<?php

/**
 * Class Dibs_EasyCheckout_CheckoutController
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

        if (!$helper->isEasyCheckoutAvailable()) {
            $this->_redirect('checkout/cart');
        }

        /** @var Dibs_EasyCheckout_Model_Checkout $dibsCheckout */
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');

        $dibsPaymentId = $this->getRequest()->getParam(self::DIBS_PAYMENT_ID_PARAM);

        $paymentId = $helper->getQuote()->getDibsEasyPaymentId();

        if (!empty($dibsPaymentId) && !empty($paymentId) && $dibsPaymentId == $paymentId) {
            return $this->_redirect('dibseasy/checkout/validate');
        }

        if (!empty($dibsPaymentId) && !empty($paymentId) && $dibsPaymentId != $paymentId) {
            $quote = $helper->getQuote();
            $quote->setDibsEasyPaymentId(null)
                ->setDibsEasyGrandTotal(null)
                ->save();
            $message = $helper->__('There is error. Please contact store administrator for details');
            $helper->getCheckout()->addError($message);
            return $this->_redirect('checkout/cart');
        }

        try {

            if (empty($paymentId)) {
                $paymentId = $dibsCheckout->createPaymentId($helper->getQuote());
            }

            Mage::register('dibs_easy_payment_id', $paymentId);
        } catch (Exception $e) {
            Mage::logException($e);
            $message = $helper->__('There is error. Please contact store administrator for details');
            $helper->getCheckout()->addError($message);

            return $this->_redirect('checkout/cart');
        }

        
        $smessages = Mage::getSingleton('core/session')->getMessages(true);
        
        
        
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

        if (empty($paymentId)) {
            Mage::log('No payment ID was saved on the customers quote, please make sure this column exists in the database and fully clear the cache if it does', null, 'dibseasy.log');
            $messsage = $helper->__('There is error. Please contact store administrator for details3');
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
                $helper->getCheckout()->addError(
                    "The payment data and order data doesn't appear to match, please try again"
                );
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
            return $this->_redirect('checkout/cart');
        }

        return $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
    
    /**
     * Remove item from cart on Easy checkout page
     */
    public function removeItemAction() {
       $id = $this->getRequest()->getParam('id');
       $cart = Mage::getSingleton('checkout/cart');
       $cart->removeItem($id)->save();
       $this->_redirect('dibseasy/checkout');
    }

    public function updateItemAction() {
       $id = (int) $this->getRequest()->getParam('id');
       $qty = (int) $this->getRequest()->getParam('qty');
       try {
           $cart = Mage::getSingleton('checkout/cart');
           $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
            $cart->save();
            $this->_redirect('dibseasy/checkout');
       }catch(Exception $e) {
            $helper = Mage::helper('dibs_easycheckout');
            $message = $helper->__($e->getMessage());
            $helper->getCheckout()->addError($message);
            $this->_redirect('checkout/cart');
       }
    }

    
    public function UpdateViewAction() {
        
        $action = $this->getRequest()->getParam('action');
        $state = $this->getRequest()->getParam('customerisloggedin');
        $helper = Mage::helper('dibs_easycheckout');
        $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');
        
        try {
            switch($action) {
              case 'address-changed':
                $dibsCheckout->changeShippingAddress();
              break;

              case 'set-shipping-method':
                $shippingRateCode = $this->getRequest()->getParam('shipping_rate_code');
                $dibsCheckout->setShippingMethod($shippingRateCode);
              break;

              case 'start':
                $dibsCheckout->start($ship);
              break;
        }
             $exception = 0; 
             $this->loadLayout();
             $output = $this->getLayout()->getOutput();
             if(Mage::getSingleton('core/session')->getShippingNotAvailable()) {
               $exception = 1;
               if($state) {
                 $helper->getCheckout()->addError(Mage::getSingleton('core/session')->getShippingMethodsError());
               }
               Mage::getSingleton('core/session')->setShippingNotAvailable(0);
             }
             $this->getResponse()->setHeader('Content-type', 'application/json' );
             $this->getResponse()->setBody(json_encode(['outputHtml' => $output, 'exception' => $exception]));
        
       
        } catch(Exception $e) {
            $this->getResponse()->setHeader( 'Content-type', 'application/json' );
            $this->getResponse()->setBody(json_encode(['exception' => 1, 'message' => $e->getMessage()]));
            $helper->getCheckout()->addError($e->getMessage());
        }
        
        /*
        $gridValues = $dibsCheckout->getGridValues();
        if(isset($gridValues['exception'])) {
            $helper->getCheckout()->addError($gridValues['exception']);
        }
        */
    }
    
    public function setAction() {
         //$shippingRateCode = 'flatrate_flatrate';
         $shippingRateCode = 'freeshipping_freeshipping';
         $dibsCheckout = Mage::getModel('dibs_easycheckout/checkout');
         $dibsCheckout->setShippingMethod($shippingRateCode);
        
    }
    
    public function showAction() {
        $shippingAddress = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        $shippingAddress->collectShippingRates();
    }
    
    public function shippingAction() {
      var_dump($this->getLayout()->getUpdate()->getHandles());
      //$this->loadLayout();
      //$this->renderLayout();
    }


}
