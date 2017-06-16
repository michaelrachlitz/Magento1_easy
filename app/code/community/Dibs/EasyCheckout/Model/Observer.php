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

class Dibs_EasyCheckout_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @param $observer
     */
    public function validateDibsEasyPaymentId($observer)
    {
        $quote = $observer->getQuote();
        $grandTotal = (double)$quote->getGrandTotal();
        $dibsEasyGrandTotal = (double)$quote->getDibsEasyGrandTotal();
        if ($grandTotal != $dibsEasyGrandTotal){
            $quote->setDibsEasyPaymentId('');
        }

    }

    /**
     * @param $observer
     *
     * @return $this
     */
    public function saveOrderDibsEasyPaymentId($observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();
        $order->setDibsEasyPaymentId($quote->getDibsEasyPaymentId());
        return $this;
    }

    /**
     * @param $observer
     *
     * @return $this
     */
    public function setInvoiceToPaymentObject($observer)
    {
        $invoice = $observer->getInvoice();
        $payment = $observer->getPayment();
        $payment->setInvoice($invoice);
        return $this;
    }

    public function setPaymentMethodAvailable(Varien_Event_Observer $observer)
    {
        $methodInstance = $observer->getEvent()->getMethodInstance();
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        if ($methodInstance instanceof \Dibs_EasyCheckout_Model_Payment_Checkout && $quote->getDibsEasyIsCreatingPayment()) {
            /** @var \StdClass $result */
            $result = $observer->getEvent()->getResult();
            /** @var Dibs_EasyCheckout_Helper_Data $helper */
            $helper = Mage::helper('dibs_easycheckout');
            $result->isAvailable = $helper->isEasyCheckoutAvailable();
            $result->isDeniedInConfig = !$result->isAvailable;
        }
    }

}
