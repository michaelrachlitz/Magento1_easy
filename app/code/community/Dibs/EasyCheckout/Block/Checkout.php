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

    /**
     * @return array
     */
    public function getCartProducts()
    {
        $quote = Mage::helper('checkout/cart')->getCart()->getQuote();
        $value = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            /** @var Mage_Sales_Model_Quote_Item $item */

            $price = $item->getParentItemId()?  $item->getParentItem()->getPrice() : $item->getPrice();
            $productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

            $attributesInfo = null;
            if ($productOptions && isset($productOptions['attributes_info'])) {
                $attributesInfo = $productOptions['attributes_info'];
            }

            $value[]= array (
                'id' => $item->getId(),
                'product_url' => $item->getProduct()->getProductUrl(),
                'name' => $item->getName(),
                'attributes_info' => $attributesInfo,
                'quantity' => $item->getQty(),
                'price' => Mage::helper('core')->currency($price, true, false),
                'subtotal' => Mage::helper('core')->currency($item->getBaseRowTotalInclTax(), true, false),
                'thumb_url' =>  $this->getProductThumbnailUrl($item->getProduct())->__toString()
            );
        }
        return $value;
    }

    public function getUpdateCartUrl()
    {
        return Mage::getUrl('dibseasy/checkout/updateItem');
    }

    /**
     *
     * @param type $product
     * @return string            )
     */
    protected function getProductThumbnailUrl($product)
    {
        return Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(82, 82);
    }

    public function getUpdateViewUrl()
    {
        return Mage::getUrl('dibseasy/checkout/UpdateView');
    }

    public function getCartUrl()
    {
        return Mage::getUrl('checkout/cart');
    }

    public function getSessionError()
    {
        $messages = Mage::getSingleton('checkout/session')->getMessages()->getItems();
        $output = NULL;
        foreach ($messages as $message) {
            $output .= $message->getText();
        }
        return $output;
    }
}
