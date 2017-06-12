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

class Dibs_EasyCheckout_Model_System_Config_Source_Environment
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Dibs_EasyCheckout_Helper_Data::API_ENVIRONMENT_LIVE, 'label' => $this->getModuleHelper()->__('Live')),
            array('value' => Dibs_EasyCheckout_Helper_Data::API_ENVIRONMENT_TEST,  'label' => $this->getModuleHelper()->__('Test'))
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            Dibs_EasyCheckout_Helper_Data::API_ENVIRONMENT_LIVE => $this->getModuleHelper()->__('Live'),
            Dibs_EasyCheckout_Helper_Data::API_ENVIRONMENT_TEST  => $this->getModuleHelper()->__('Test'),
        );
    }

    /**
     * @return Mage_Core_Helper_Abstract
     */
    public function getModuleHelper()
    {
        $helper = Mage::helper('dibs_easycheckout');
        return $helper;
    }

}
