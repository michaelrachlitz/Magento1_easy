<?php
/**
 * Copyright (c) 2009-2017 Vaimo Group
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Dibs
 * @package     Dibs_EasyCheckout
 * @copyright   Copyright (c) 2009-2017 Vaimo Group
 */

class Dibs_EasyCheckout_Model_Checkout extends Mage_Core_Model_Abstract
{

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return null
     */
    public function createPaymentId(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->isVirtual()) {
            if ($quote->getShippingAddress()->getShippingMethod() != \Dibs_EasyCheckout_Helper_Data::DIBS_EASY_SHIPPING_METHOD) {
                $this->_setShippingMethod($quote);
                $quote->save();
                $quote->collectTotals();
            }
        }

        $result = null;
        /** @var Dibs_EasyCheckout_Model_Api $api */
        $api = Mage::getModel('dibs_easycheckout/api');
        /** @var Dibs_EasyPayment_Api_Response $paymentResponse */
        $paymentId = $api->createPayment($quote);

        if ($paymentId){
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

        $this->_prepareQuoteBillingAddress($quote,$payment);
        $this->_prepareQuoteShippingAddress($quote, $payment);
        $this->_setPaymentMethod($quote);
        $this->_setShippingMethod($quote);


        if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_GUEST){
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
    protected function _prepareQuoteBillingAddress(Mage_Sales_Model_Quote $quote,Dibs_EasyCheckout_Model_Api_Payment $payment)
    {
        $paymentBillingAddress = $payment->getBillingAddress();
        if (empty($paymentBillingAddress->getData())){
            $paymentBillingAddress = $payment->getShippingAddress();
        }
        $billingAddress = $quote->getBillingAddress();
        $billingRegionCode  = $paymentBillingAddress->getData('postalCode');
        $billingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $billingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $billingAddress->setStreet($paymentBillingAddress->getStreetsArray());
        $billingAddress->setPostcode($paymentBillingAddress->getData('postalCode'));
        $billingAddress->setCity($paymentBillingAddress->getData('city'));
        $billingAddress->setCountryId($paymentBillingAddress->getData('country'));
        $billingAddress->setEmail($payment->getPrivatePerson()->getData('email'));
        $billingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $billingAddress->setCompany($payment->getCompany()->getData('name'));

        if ($billingRegionCode) {
            $billingRegionId = Mage::getModel('directory/region')->loadByCode($billingRegionCode, $billingAddress->getCountryId());
            $billingAddress->setRegionId($billingRegionId->getId());
        }

        $billingAddress->setShouldIgnoreValidation(true);

        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @param Dibs_EasyCheckout_Model_Api_Payment $payment
     */
    protected function _prepareQuoteShippingAddress(Mage_Sales_Model_Quote $quote,Dibs_EasyCheckout_Model_Api_Payment $payment)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setFirstname($payment->getPrivatePerson()->getData('firstName'));
        $shippingAddress->setLastname($payment->getPrivatePerson()->getData('lastName'));
        $shippingAddress->setStreet($payment->getShippingAddress()->getStreetsArray());
        $shippingAddress->setPostcode($payment->getShippingAddress()->getData('postalCode'));
        $shippingAddress->setCity($payment->getShippingAddress()->getData('city'));
        $shippingAddress->setCountryId($payment->getShippingAddress()->getData('country'));
        $shippingAddress->setEmail($payment->getPrivatePerson()->getData('email'));
        $shippingAddress->setTelephone($payment->getPrivatePerson()->getTelephone());
        $shippingAddress->setCompany($payment->getCompany()->getData('name'));
        $shippingRegionCode = $payment->getShippingAddress()->getData('postalCode');

        if ($shippingRegionCode) {
            $shippingRegionId = Mage::getModel('directory/region')->loadByCode($shippingRegionCode, $shippingAddress->getCountryId());
            $shippingAddress->setRegionId($shippingRegionId->getId());
        }

        $shippingAddress->setShouldIgnoreValidation(true);
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _setPaymentMethod(Mage_Sales_Model_Quote $quote)
    {
        $quotePayment = $quote->getPayment();
        $quotePayment->importData(array('method' => Dibs_EasyCheckout_Helper_Data::PAYMENT_CHECKOUT_METHOD));
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _setShippingMethod(Mage_Sales_Model_Quote $quote)
    {
        if (!$quote->getIsVirtual() && $shippingAddress = $quote->getShippingAddress()) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setFreeShipping(true);

            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod(\Dibs_EasyCheckout_Helper_Data::DIBS_EASY_SHIPPING_METHOD);
        }

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
     * @return Mage_Core_Helper_Abstract
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
