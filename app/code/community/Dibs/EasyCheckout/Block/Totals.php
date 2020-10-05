<?php

/**
 * Class Dibs_EasyCheckout_Block_Totals
 */
class Dibs_EasyCheckout_Block_Totals extends Mage_Checkout_Block_Onepage_Abstract
{
    public function getShippingMethods() {
        /** @var Dibs_EasyCheckout_Model_Checkout $dibsCheckout */
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');
        $gridValues = $dibsCheckout->getGridValues();
        return $gridValues['shipping_methods'];
    }

    public function getCartTotals() {
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');
        $gridValues = $dibsCheckout->getGridValues();
        return $gridValues['totals'];
    }

    public function getCouponeUrl() {
        return Mage::getUrl('checkout/cart/couponPost/');
    }

    public function getCouponeCode() {
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');
        return $dibsCheckout->getQuote()->getCouponCode();
    }

    public function getCheckoutUrl() {
        return Mage::getUrl('dibs_easycheckout/checkout');
    }

    public function getAddress()
    {
        Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
    }
}
