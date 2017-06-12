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
