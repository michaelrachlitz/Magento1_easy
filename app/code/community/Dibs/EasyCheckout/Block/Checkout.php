<?php

/**
 * Class Dibs_EasyCheckout_Block_Checkout
 */
class Dibs_EasyCheckout_Block_Checkout extends Mage_Core_Block_Template
{
    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        $paymentId = Mage::registry('dibs_easy_payment_id');
        return $paymentId;
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutKey()
    {
        return $this->getDibsCheckoutHelper()->getCheckoutKey();
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutLanguage()
    {
        return $this->getDibsCheckoutHelper()->getCheckoutLanguage();
    }

    /**
     * @return string
     */
    public function getDibsCheckoutJsUrl()
    {
        return $this->getDibsCheckoutHelper()->getEasyCheckoutJsUrl();
    }

    /**
     * @return Dibs_EasyCheckout_Helper_Data
     */
    protected function getDibsCheckoutHelper()
    {
        /** @var Dibs_EasyCheckout_Helper_Data $helper */
        $helper = Mage::helper('dibs_easycheckout');
        return $helper;
    }

    /**
     * @return string
     */
    public function getDibsCheckoutValidateUrl()
    {
        return Mage::getUrl('dibseasy/checkout/validate');
    }
}
