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

class Dibs_EasyCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{

    const PAYMENT_CHECKOUT_METHOD = 'dibs_easy_checkout';

    const API_ENVIRONMENT_TEST = 'test';

    const API_ENVIRONMENT_LIVE = 'live';

    const DIBS_CHECKOUT_JS_TEST = 'https://test.checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DIBS_CHECKOUT_JS_LIVE = 'https://checkout.dibspayment.eu/v1/checkout.js?v=1';

    const DEFAULT_CHECKOUT_LANGUAGE = 'en-GB';

    const DIBS_EASY_SHIPPING_METHOD = 'dibs_easy_freeshipping_dibs_easy_freeshipping';

    // For now we support only SEK
    protected $_supportedCurrencies = ['SEK'];

    protected $_supportedLanguages = ['en-GB','sv-SE'];

    /**
     * @return bool
     */
    public function isEasyCheckoutAvailable()
    {
        $result = false;

        $checkoutEnabled = (bool)(int) Mage::getStoreConfig('payment/dibs_easy_checkout/enabled');
        $quoteCurrency = $this->getQuote()->getQuoteCurrencyCode();
        $quoteCurrencySupported = in_array($quoteCurrency,$this->_supportedCurrencies);

        if ($checkoutEnabled && $quoteCurrencySupported){
            $result = true;
        }


        return $result;
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/environment');
    }

    /**
     * @return mixed
     */
    public function getLiveSecret()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/live_secret_key');
    }

    /**
     * @return mixed
     */
    public function getTestSecret()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/test_secret_key');
    }

    /**
     * @return mixed
     */
    public function getLiveCheckoutKey()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/live_checkout_key');
    }

    /**
     * @return mixed
     */
    public function getTestCheckoutKey()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/test_checkout_key');
    }

    /**
     * @return mixed
     */
    public function getNewOrderStatus()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/new_order_status');
    }

    /**
     * @return mixed
     */
    public function getShippingCarrierId()
    {
        return Mage::getStoreConfig('payment/dibs_easy_checkout/carrier');
    }

    /**
     * @return mixed|string
     */
    public function getCheckoutLanguage()
    {
        $language = self::DEFAULT_CHECKOUT_LANGUAGE;
        $currentStoreLang = str_replace('_','-',Mage::app()->getLocale()->getLocaleCode());
        if (in_array($currentStoreLang, $this->_supportedLanguages)){
            $language = $currentStoreLang;
        }

        return $language;

    }
    /**
     * @return string
     */
    public function getEasyCheckoutJsUrl()
    {

        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = self::DIBS_CHECKOUT_JS_TEST;
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = self::DIBS_CHECKOUT_JS_LIVE;
                break;
            default:
                $result = '';
        }


        return $result;

    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = $this->getTestSecret();
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = $this->getLiveSecret();
                break;
            default:
                $result = '';
        }


        return $result;

    }

    /**
     * @return mixed|string
     */
    public function getCheckoutKey()
    {
        $environment = $this->getEnvironment();
        switch ($environment){
            case self::API_ENVIRONMENT_TEST:
                $result = $this->getTestCheckoutKey();
                break;
            case self::API_ENVIRONMENT_LIVE:
                $result = $this->getLiveCheckoutKey();
                break;
            default:
                $result = '';
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isTestEnvironmentEnabled()
    {
        $result = false;
        $currentEnv = $this->getEnvironment();
        if ($currentEnv == self::API_ENVIRONMENT_TEST){
            $result = true;
        }

        return $result;
    }

    /**
     * @return mixed
     */
    public function getQuoteDibsEasyPaymentId()
    {
        return $this->getQuote()->getDibsEasyPaymentId();
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return Mage_Sales_Model_Quote|null
     * @throws Exception
     */
    public function getQuote()
    {
        $result = null;
        if ($this->getCheckout()){
            $result = $this->getCheckout()->getQuote();
        }
        if (empty($result)){
            $message = $this->__('Cart is empty');
            throw new Exception($message);
        }
        return $result;
    }




}
