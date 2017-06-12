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

class Dibs_EasyCheckout_Model_Api_Payment_Consumer
{
    /** @var Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address  */
    protected $billingAddress;

    /** @var Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address  */
    protected $shippingAddress;

    /** @var Dibs_EasyCheckout_Model_Api_Payment_Consumer_Company  */
    protected $company;

    /** @var Dibs_EasyCheckout_Model_Api_Payment_Consumer_PrivatePerson  */
    protected $privatePerson;

    /**
     * Dibs_EasyCheckout_Model_Api_Payment_Consumer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->billingAddress = new Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address($data['billingAddress']);
        $this->shippingAddress = new Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address($data['shippingAddress']);
        $this->company = new Dibs_EasyCheckout_Model_Api_Payment_Consumer_Company($data['company']);
        $this->privatePerson = new Dibs_EasyCheckout_Model_Api_Payment_Consumer_PrivatePerson($data['privatePerson']);
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_PrivatePerson
     */
    public function getPrivatePerson()
    {
        return $this->privatePerson;
    }



}
