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

class Dibs_EasyCheckout_CheckoutController extends Mage_Core_Controller_Front_Action
{

    const DIBS_PAYMENT_ID_PARAM = 'paymentId';
    /**
     * Page where payment is initiated
     */
    public function indexAction()
    {
        /** @var Dibs_EasyCheckout_Helper_Data $helper */
        $helper = Mage::helper('dibs_easycheckout');

        if (!$helper->isEasyCheckoutAvailable()){
            $this->_redirect('checkout/cart');
        }

        /** @var Dibs_EasyCheckout_Model_Checkout $dibsCheckout */
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');

        $dibsPaymentId = $this->getRequest()->getParam(self::DIBS_PAYMENT_ID_PARAM);

        $paymentId = $helper->getQuote()->getDibsEasyPaymentId();

        if (!empty($dibsPaymentId) && !empty($paymentId) && $dibsPaymentId == $paymentId){
            return $this->_redirect('dibseasy/checkout/validate');
        }

        if (!empty($dibsPaymentId) && !empty($paymentId) && $dibsPaymentId != $paymentId){

            $quote = $helper->getQuote();
            $quote->setDibsEasyPaymentId(null)
                ->setDibsEasyGrandTotal(null)
                ->save();
            $message = $helper->__('There is error. Please contact store administrator for details');
            $helper->getCheckout()->addError($message);
            return $this->_redirect('checkout/cart');
        }

        try {

            if (empty($paymentId)){
                $paymentId = $dibsCheckout->createPaymentId($helper->getQuote());
            }

            Mage::register('dibs_easy_payment_id', $paymentId);

        } catch (Exception $e) {

            Mage::logException($e);

            $message = $helper->__('There is error. Please contact store administrator for details');
            $helper->getCheckout()->addError($message);
            return $this->_redirect('checkout/cart');
        }

        $this->_initLayoutMessages('customer/session');
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($helper->__('DIBS Easy Checkout'));
        $this->renderLayout();
    }

    /**
     * Validates result coming back from DIBS
     */
    public function validateAction()
    {
        /** @var Dibs_EasyCheckout_Helper_Data $helper */
        $helper = Mage::helper('dibs_easycheckout');

        $quote = $helper->getQuote();
        $paymentId = $quote->getDibsEasyPaymentId();

        if (empty($paymentId)){
            Mage::log('No payment ID was saved on the customers quote, please make sure this column exists in the database and fully clear the cache if it does', null, 'dibseasy.log');
            $messsage = $helper->__('There is error. Please contact store administrator for details');
            $helper->getCheckout()->addError($messsage);
            $this->_redirect('checkout/cart');
        }

        try {

            /** @var Dibs_EasyCheckout_Model_Api $api */
            $api = Mage::getModel('dibs_easycheckout/api');

            /** @var Dibs_EasyCheckout_Model_Checkout $dibsCheckout */
            $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');

            /** @var Dibs_EasyCheckout_Model_Api_Payment $payment */
            $payment = $api->findPayment($paymentId);

            $isValidPayment = $dibsCheckout->validatePayment($quote, $payment);
            if ($isValidPayment) {
                $dibsCheckout->createOrder($quote, $payment);
            } else {

                $helper->getCheckout()->addError("The payment data and order data doesn't appear to match, please try again");
                $quote->setDibsEasyPaymentId(null)
                    ->setDibsEasyGrandTotal(null)
                    ->save();
                return $this->_redirect('checkout/cart');
            }

        } catch (Exception $e) {
            $messsage = $helper->__('There is error. Please contact store administrator for details');
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($quote, $messsage);
            $helper->getCheckout()->addException($e, $messsage);
            $quote->setDibsEasyPaymentId(null)
                ->setDibsEasyGrandTotal(null)
                ->save();
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_redirect('checkout/onepage/success', array('_secure'=>true));

    }

}
