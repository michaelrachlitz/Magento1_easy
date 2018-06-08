<?php

/**
 * Class Dibs_EasyCheckout_Model_Checkout
 */
class Dibs_EasyCheckout_Model_Checkout extends Mage_Core_Model_Abstract
{
    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return null
     * @throws Dibs_EasyCheckout_Model_Exception
     */
    public function createPaymentId(Mage_Sales_Model_Quote $quote)
    {
        $paymentId = null;

        if (!$quote->isVirtual()) {
            $configuredShippingRage = $this->_findShippingRate($quote->getShippingAddress());
            if (!$configuredShippingRage ||
                $quote->getShippingAddress()->getShippingMethod() != $configuredShippingRage->getCode()) {
                $this->_setShippingMethod($quote);
                $quote->save();
            }
        }

        /** @var Dibs_EasyCheckout_Model_Api $api */
        $api = Mage::getModel('dibs_easycheckout/api');

        /** @var Dibs_EasyPayment_Api_Response $paymentResponse */
        $paymentId = $api->createPayment($quote);

        if ($paymentId) {
            $quote->setDibsEasyPaymentId($paymentId);
            $quote->setDibsEasyGrandTotal($quote->getGrandTotal());
            $quote->save();
        }

        return $paymentId;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Dibs_EasyCheckout_Model_Api_Payment $payment
     *
     * @return bool
     */
    public function validatePayment(Mage_Sales_Model_Quote $quote, Dibs_EasyCheckout_Model_Api_Payment $payment)
    {
        $result = false;
        /** @var Dibs_EasyCheckout_Model_Api $api */
        $api = Mage::getModel('dibs_easycheckout/api');
        if ($payment->getOrderDetails()->getData('amount') == $api->getDibsQuoteGrandTotal($quote)
            && $payment->getOrderDetails()->getData('reference') == $quote->getId()
            && $payment->getPaymentId() == $quote->getDibsEasyPaymentId()
            && $payment->getOrderDetails()->getData('currency') == $quote->getQuoteCurrencyCode()
        ) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Dibs_EasyCheckout_Model_Api_Payment $payment
     *
     * @return Mage_Sales_Model_Order
     */
    public function createOrder(Mage_Sales_Model_Quote $quote, Dibs_EasyCheckout_Model_Api_Payment $payment)
    {
        $quote->collectTotals();
        if ($quote->getCustomerId()) {
            $customer = $this->_loadCustomer($quote->getCustomerId());
            $quote->setCustomer($customer);
            $quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER);
        } else {
            $quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        }

        $quote->setDibsEasyIsCreatingPayment(true);

        $this->_prepareQuoteBillingAddress($quote, $payment);
        $this->_prepareQuoteShippingAddress($quote, $payment);
        $this->_setPaymentMethod($quote, $payment);
        $this->_setShippingMethod($quote);

        /** @var Dibs_EasyCheckout_Model_Api $api */
        $api = Mage::getModel('dibs_easycheckout/api');
        $quoteDibsTotal = $api->getDibsIntVal($quote->getGrandTotal());
        $reservedDibsAmount = $payment->getSummary()->getData('reservedAmount');

        if ($quoteDibsTotal > $reservedDibsAmount) {
            $reservedDibsAmountRegular = $api->convertDibsValToRegular($reservedDibsAmount);
            $helper = $this->_getHelper();
            $errorText = 'Reserved payment amount is not correct. Reserved amount %s - order amount %s';
            $message = $helper->__($errorText, $reservedDibsAmountRegular, $quote->getGrandTotal());
            throw new Dibs_EasyCheckout_Model_Exception($message);
        }

        $quote->setDibsEasyGrandTotal($quote->getGrandTotal());

        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST) {
            $this->_prepareGuestCustomerQuote($quote);
            $quote->setCustomerIsGuest(1);
            $quote->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        }

        Mage::dispatchEvent('dibs_easy_checkout_quote_before_create_order', array(
            'quote' => $quote,
            'payment' => $payment,
        ));

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();

        $order = $this->_loadOrderByKey($quote->getId());

        if (!$order->getId()) {
            Mage::throwException($this->getHelper()->__('Order cannot be created, cart not valid') . ' ' . $quote->getId());
        }

        // Update Order
        /** @var $order Mage_Sales_Model_Order */
        $oderStatus = $this->_getHelper()->getNewOrderStatus();
        $order->setStatus($oderStatus)->setState('processing');

        // Set Order Dibs Payment Id
        $order->setDibsEasyPaymentId($quote->getDibsEasyPaymentId());

        $order->save();

        $order->sendNewOrderEmail();

        $quote->setIsActive(false)
            ->save();

        /** @var Dibs_EasyCheckout_Helper_Data $helper */
        $helper = Mage::helper('dibs_easycheckout');
        // Set Checkout Success Data
        $checkout = $helper->getCheckout();
        $checkout->setLastOrderId($order->getId());
        $checkout->setLastQuoteId($quote->getId());
        $checkout->setLastSuccessQuoteId($quote->getId());

        return $order;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Dibs_EasyCheckout_Model_Api_Payment $payment
     *
     * @return $this
     */
    protected function _prepareQuoteBillingAddress(
        Mage_Sales_Model_Quote $quote,
        Dibs_EasyCheckout_Model_Api_Payment $payment
    ) {
        $paymentBillingAddress = $payment->getBillingAddress();
        $paymentBillingAddress = $paymentBillingAddress->getData();
        if (empty($paymentBillingAddress)) {
            $paymentBillingAddress = $payment->getShippingAddress();
        }

        $billingAddress = $quote->getBillingAddress();
        $billingRegionCode  = $paymentBillingAddress->getData('postalCode');
        $billingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $billingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $billingAddress->setStreet($paymentBillingAddress->getStreetsArray());
        $billingAddress->setPostcode($paymentBillingAddress->getData('postalCode'));
        $billingAddress->setCity($paymentBillingAddress->getData('city'));
        $billingAddress->setCountryId($this->getCountryId($paymentBillingAddress->getData('country')));
        $billingAddress->setEmail($payment->getPrivatePerson()->getData('email'));
        $billingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $billingAddress->setCompany($payment->getCompany()->getData('name'));

        if ($billingRegionCode) {
            $billingRegionId =$this->getRegionId($billingAddress->getCountryId(), $billingRegionCode);
            $billingAddress->setRegionId($billingRegionId);
        }

        $billingAddress->setShouldIgnoreValidation(true);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Dibs_EasyCheckout_Model_Api_Payment $payment
     */
    protected function _prepareQuoteShippingAddress(
        Mage_Sales_Model_Quote $quote,
        Dibs_EasyCheckout_Model_Api_Payment $payment
    ) {

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $shippingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $shippingAddress->setStreet($payment->getShippingAddress()->getStreetsArray());
        $shippingAddress->setPostcode($payment->getShippingAddress()->getData('postalCode'));
        $shippingAddress->setCity($payment->getShippingAddress()->getData('city'));
        $shippingAddress->setCountryId($this->getCountryId($payment->getShippingAddress()->getData('country')));
        $shippingAddress->setEmail($payment->getPrivatePerson()->getData('email'));
        $shippingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $shippingAddress->setCompany($payment->getCompany()->getData('name'));
        $shippingRegionCode = $payment->getShippingAddress()->getData('postalCode');

        if ($shippingRegionCode) {
            $shippingRegionId =$this->getRegionId($shippingAddress->getCountryId(), $shippingRegionCode);
            $shippingAddress->setRegionId($shippingRegionId);
        }

        $shippingAddress->setShouldIgnoreValidation(true);
    }

    /**
     * @param string $countryCode
     *
     * @return null
     */
    protected function getCountryId($countryCode = '')
    {
        $result = null;
        if (!empty($countryCode)) {
            $result = Mage::getModel('directory/country')->loadByCode($countryCode)->getId();
        }

        return $result;
    }

    /**
     * @param string $countryId
     * @param string $regionCode
     *
     * @return null
     */
    protected function getRegionId($countryId = '', $regionCode = '')
    {
        $result = null;
        if (!empty($countryId) && !empty($regionCode)){
            $result = Mage::getModel('directory/region')->loadByCode($regionCode, $countryId)->getId();
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _setPaymentMethod(Mage_Sales_Model_Quote $quote, Dibs_EasyCheckout_Model_Api_Payment $dibsPayment)
    {
        $quotePayment = $quote->getPayment();
        $quotePayment->importData(array('method' => Dibs_EasyCheckout_Model_Config::PAYMENT_CHECKOUT_METHOD));

        $quotePayment->setData('dibs_easy_payment_type', $dibsPayment->getPaymentDetails()->getPaymentType());
        $quotePayment->setData('dibs_easy_cc_masked_pan', $dibsPayment->getPaymentDetails()->getMaskedPan());
        $quotePayment->setData('cc_last_4', $dibsPayment->getPaymentDetails()->getCcLast4());
        $quotePayment->setData('cc_exp_month', $dibsPayment->getPaymentDetails()->getCcExpMonth());
        $quotePayment->setData('cc_exp_year', $dibsPayment->getPaymentDetails()->getCcExpYear());
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _setShippingMethod(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->getIsVirtual() && $shippingAddress = $quote->getShippingAddress()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingCountryId = $shippingAddress->getCountryId();
            if (empty($shippingCountryId)) {
                $defaultCountryCode = Mage::getStoreConfig('general/country/default');
                /** @var Mage_Directory_Model_Country $country */
                $country = Mage::getModel('directory/country')->loadByCode($defaultCountryCode);

                $shippingAddress->setCountryId($country->getId());
            }

            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates();

            $shippingRate = $this->_findShippingRate($shippingAddress);

            if (!$shippingRate) {
                $helper = $this->_getHelper();
                $message = 'There is error. Please contact store administrator for details';
                throw new Dibs_EasyCheckout_Model_Exception($helper->__($message));
            }

            $shippingAddress->setShippingMethod($shippingRate->getCode());
        }
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $shippingAddress
     *
     * @return Mage_Sales_Model_Quote_Address_Rate|null
     */
    protected function _findShippingRate(Mage_Sales_Model_Quote_Address $shippingAddress)
    {
        $result = null;
        $configuredShippingCarrier = $this->_getHelper()->getShippingCarrierId();
        /** @var Mage_Sales_Model_Quote_Address_Rate $rate */
        foreach ($shippingAddress->getShippingRatesCollection() as $rate) {
            if ($rate->getCarrier() == $configuredShippingCarrier) {
                $result = $rate;
            }
        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _prepareGuestCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $quote->getCustomer();
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);

        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        $customer->setFirstname($customerBilling->getFirstname());
        $customer->setLastname($customerBilling->getLastname());
        $customer->setEmail($customerBilling->getEmail());

        $quote->setCustomer($customer);
    }

    /**
     * @return Dibs_EasyCheckout_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('dibs_easycheckout');
    }

    /**
     * @param $id
     *
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomer($id)
    {
        return Mage::getModel('customer/customer')->load($id);
    }

    /**
     * @param $id
     * @param string $key
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _loadOrderByKey($id, $key = 'quote_id')
    {
        return Mage::getModel('sales/order')->load($id, $key);
    }
}
