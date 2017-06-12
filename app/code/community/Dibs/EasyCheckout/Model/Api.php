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

class Dibs_EasyCheckout_Model_Api extends Mage_Core_Model_Abstract
{

    /**
     * @var Dibs_EasyPayment_Api_Client
     */
    protected $apiClient;

    /**
     * @var Dibs_EasyPayment_Api_Service_Payment
     */
    protected $paymentService;

    /**
     * @var Dibs_EasyPayment_Api_Service_Refund
     */
    protected $refundService;

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return null
     */
    public function createPayment(Mage_Sales_Model_Quote $quote)
    {
        $result = null;
        $paymentService = $this->getPaymentService();
        $createPaymentParams = $this->_getCreatePaymentParams($quote);
        $response = $paymentService->create($createPaymentParams);
        $result = $response->getResponseDataObject()->getData('paymentId');
        return $result;
    }

    /**
     * @param $paymentId
     *
     * @return Dibs_EasyCheckout_Model_Api_Payment|null
     */
    public function findPayment($paymentId)
    {
        $result = null;
        $paymentService = $this->getPaymentService();
        $response = $paymentService->find($paymentId);
        $result = new Dibs_EasyCheckout_Model_Api_Payment($response->getResponseDataObject()->getData('payment'));
        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param $amount
     *
     * @return mixed|null
     */
    public function chargePayment(Mage_Sales_Model_Order_Invoice $invoice, $amount)
    {
        $result = null;
        $paymentId = $invoice->getOrder()->getDibsEasyPaymentId();
        $paymentService = $this->getPaymentService();
        $chargeParams = $this->_getChargePaymentParams($invoice, $amount);
        $response = $paymentService->charge($paymentId, $chargeParams);
        $result = $response->getResponseDataObject()->getData('chargeId');
        return $result;
    }

    /**
     * @param $chargeId
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param $amount
     *
     * @return mixed|null
     */
    public function refundPayment($chargeId, Mage_Sales_Model_Order_Creditmemo $creditmemo, $amount)
    {
        $result = null;
        $refundService = $this->getRefundService();
        $chargeParams = $this->_getRefundPaymentParams($creditmemo, $amount);
        $response = $refundService->charge($chargeId, $chargeParams);
        $result = $response->getResponseDataObject()->getData('refundId');
        return $result;
    }

    /**
     * @return Dibs_EasyPayment_Api_Service_Payment
     */
    public function getPaymentService()
    {
        if (is_null($this->paymentService)){
            $apiClient = $this->_getApiClient();
            $this->paymentService = new Dibs_EasyPayment_Api_Service_Payment($apiClient);
        }

        return $this->paymentService;
    }

    /**
     * @return Dibs_EasyPayment_Api_Service_Payment|Dibs_EasyPayment_Api_Service_Refund
     */
    public function getRefundService()
    {
        if (is_null($this->paymentService)){
            $apiClient = $this->_getApiClient();
            $this->paymentService = new Dibs_EasyPayment_Api_Service_Refund($apiClient);
        }

        return $this->paymentService;
    }

    /**
     * @return Dibs_EasyPayment_Api_Client
     */
    protected function _getApiClient()
    {
        if (is_null($this->apiClient)){
            $secretKey = $this->_getDibsCheckoutHelper()->getSecretKey();
            $isTestEnvironment = $this->_getDibsCheckoutHelper()->isTestEnvironmentEnabled();
            $this->apiClient = new Dibs_EasyPayment_Api_Client($secretKey, $isTestEnvironment);
        }
        return $this->apiClient;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @param $amount
     *
     * @return array
     */
    protected function _getRefundPaymentParams(Mage_Sales_Model_Order_Creditmemo $creditmemo, $amount)
    {
        $refundOrderItems = $this->_getCreditMemoItems($creditmemo);
        $params = [
            'amount' => $this->_getDibsIntVal($amount),
            'orderItems' => $refundOrderItems
        ];

        return $params;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param $amount
     *
     * @return array
     */
    protected function _getChargePaymentParams(Mage_Sales_Model_Order_Invoice $invoice, $amount)
    {
        $invoiceItems = $this->_getInvoiceItems($invoice);
        $params = [
            'amount' => $this->_getDibsIntVal($amount),
            'orderItems' => $invoiceItems
        ];

        return $params;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    protected function _getCreatePaymentParams(Mage_Sales_Model_Quote $quote)
    {
        $params = [
            'order' => [
                'items'     =>  $this->_getQuoteItems($quote),
                'amount'    =>  $this->getDibsQuoteGrandTotal($quote),
                'currency'  =>  $quote->getQuoteCurrencyCode(),
                'reference' =>  $quote->getEntityId()
            ],
            'checkout' => [
                'url' => Mage::getUrl('dibseasy/checkout',array('_secure'=>true))
            ]
        ];

        return $params;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return array
     */
    protected function _getQuoteItems(Mage_Sales_Model_Quote $quote)
    {
        $result = [];
        $items = $quote->getAllItems();
        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item){
            if ($this->_isNotChargeable($item)){
                continue;
            }
            $result[] = $this->_getOrderLineItem($item);

        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Creditmemo $invoice
     *
     * @return array
     */
    protected function _getCreditMemoItems(Mage_Sales_Model_Order_Creditmemo $invoice)
    {
        $result = [];
        $items = $invoice->getAllItems();
        /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
        foreach ($items as $item){
            if ($this->_isNotChargeable($item->getOrderItem())){
                continue;
            }
            $result[] = $this->_getOrderLineItem($item);

        }

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return array
     */
    protected function _getInvoiceItems(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $result = [];
        $items = $invoice->getAllItems();
        /** @var Mage_Sales_Model_Order_Invoice_Item $item */
        foreach ($items as $item){
            if ($this->_isNotChargeable($item->getOrderItem())){
                continue;
            }
            $result[] = $this->_getOrderLineItem($item);

        }

        return $result;
    }

    /**
     * @param Mage_Core_Model_Abstract $item
     *
     * @return array
     */
    protected function _getOrderLineItem(Mage_Core_Model_Abstract $item)
    {
        $result = [
            'reference'         =>  $item->getSku(),
            'name'              =>  $item->getName(),
            'quantity'          =>  (int)$item->getQty(),
            'unit'              =>  1,
            'unitPrice'         =>  $this->_getDibsIntVal($item->getPrice()),
            'taxRate'           =>  $this->_getDibsIntVal($item->getTaxPercent()),
            'taxAmount'         =>  $this->_getItemTaxAmount($item),
            'grossTotalAmount'  =>  $this->_getItemGrossTotalAmount($item),
            'netTotalAmount'    =>  $this->_getItemNetTotalAmount($item) ,
        ];

        return $result;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return int
     */
    public function getDibsQuoteGrandTotal(Mage_Sales_Model_Quote $quote)
    {
        return $this->_getDibsIntVal($quote->getGrandTotal());
    }

    /**
     * @param Mage_Core_Model_Abstract $item
     *
     * @return bool
     */
    protected function _isNotChargeable(Mage_Core_Model_Abstract $item)
    {
        $result = false;
        if ($item->getParentItem()
            && $item->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $result = true;
        }

        if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            $result = true;
        }

        return $result;
    }

    /**
     * @param Mage_Core_Model_Abstract $item
     *
     * @return int
     */
    protected function _getItemTaxAmount(Mage_Core_Model_Abstract $item)
    {
        $itemTax = (double)$item->getTaxAmount() + (double)$item->getHiddenTaxAmount();
        $result = $this->_getDibsIntVal($itemTax);

        return $result;
    }

    /**
     * @param Mage_Core_Model_Abstract $item
     *
     * @return int
     */
    protected function _getItemGrossTotalAmount(Mage_Core_Model_Abstract $item)
    {
        $itemGrossTotal = (double)$item->getRowTotalInclTax() - (double)$item->getDiscountAmount();
        $result = $this->_getDibsIntVal($itemGrossTotal);

        return $result;
    }

    /**
     * @param Mage_Core_Model_Abstract $item
     *
     * @return int
     */
    protected function _getItemNetTotalAmount(Mage_Core_Model_Abstract $item)
    {
        $netDiscount = (double)$item->getDiscountAmount() - (double)$item->getHiddenTaxAmount();
        $itemNetTotal = (double)$item->getRowTotalInclTax() - (double)$item->getTaxAmount() - $netDiscount;
        $result = $this->_getDibsIntVal($itemNetTotal);

        return $result;
    }

    /**
     * @param $value
     *
     * @return int
     */
    protected function _getDibsIntVal($value)
    {
        $result = (double)$value * 100;
        return (int)$result;
    }

    /**
     * @return Dibs_EasyCheckout_Helper_Data
     */
    protected function _getDibsCheckoutHelper()
    {
        /** @var Dibs_EasyCheckout_Helper_Data $helper */
        $helper = Mage::helper('dibs_easycheckout');
        return $helper;
    }

}
