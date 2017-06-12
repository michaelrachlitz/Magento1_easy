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

class Dibs_EasyCheckout_Block_Adminhtml_Sales_Order_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals {

    /**
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _beforeToHtml()
    {
        $isDibsEasyCheckoutPayment = $this->isDibsEasyCheckoutPayment();
        if ($isDibsEasyCheckoutPayment){
            $this->removeAdjustmentsBlock();
        }
        return parent::_beforeToHtml();
    }

    /**
     * Remove Adjustments Block
     */
    protected function removeAdjustmentsBlock()
    {
        $this->unsetChild('adjustments');
    }

    /**
     * @return bool
     */
    protected function isDibsEasyCheckoutPayment()
    {
        $result = false;
        $paymentMethod = $this->getCreditmemo()->getOrder()->getPayment()->getMethod();
        if ($paymentMethod == Dibs_EasyCheckout_Helper_Data::PAYMENT_CHECKOUT_METHOD) {
            $result = true;
        }

        return $result;
    }

}