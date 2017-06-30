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

class Dibs_EasyCheckout_Model_Carrier_Freeshipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'dibs_easy_free_shipping';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|false|Mage_Core_Model_Abstract
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigData('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier('dibs_easy_free_shipping');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('dibs_easy_free_shipping');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice('0.00');
        $method->setCost('0.00');

        $result->append($method);

        return $result;
    }


    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array('dibs_easy_freeshipiing' => $this->getConfigData('name'));
    }


}
