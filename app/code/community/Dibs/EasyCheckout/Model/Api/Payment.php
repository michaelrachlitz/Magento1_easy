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

class Dibs_EasyCheckout_Model_Api_Payment
{
    /** @var  string */
    protected $paymentId;

    /** @var Varien_Object  */
    protected $summary;

    /** @var Dibs_EasyCheckout_Model_Api_Payment_Consumer  */
    protected $consumer;

    /** @var Varien_Object  */
    protected $paymentDetails;

    /** @var Varien_Object  */
    protected $orderDetails;

    /** @var Varien_Object  */
    protected $checkout;

    /** @var null|Varien_Object  */
    protected $refunds;

    /** @var DateTime  */
    protected $created;


    public function __construct($data)
    {
        $this->paymentId = $data['paymentId'];
        $this->created = new DateTime($data['created']);
        $this->summary = new Varien_Object($data['summary']);
        $this->consumer = new Dibs_EasyCheckout_Model_Api_Payment_Consumer($data['consumer']);
        $this->paymentDetails = new Varien_Object($data['paymentDetails']);
        $this->orderDetails = new Varien_Object($data['orderDetails']);
        $this->checkout = new Varien_Object($data['checkout']);
        $this->refunds = isset($data['refunds']) ? new Varien_Object($data['refunds']) : null;

    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address
     */
    public function getBillingAddress()
    {
       return $this->consumer->getBillingAddress();
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Address
     */
    public function getShippingAddress()
    {
       return $this->consumer->getShippingAddress();
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_Company
     */
    public function getCompany()
    {
        return $this->consumer->getCompany();
    }

    /**
     * @return Dibs_EasyCheckout_Model_Api_Payment_Consumer_PrivatePerson
     */
    public function getPrivatePerson()
    {
        return $this->consumer->getPrivatePerson();
    }

    /**
     * @return Varien_Object
     */
    public function getPaymentDetails()
    {
        return $this->paymentDetails;
    }

    /**
     * @return Varien_Object
     */
    public function getOrderDetails()
    {
        return $this->orderDetails;
    }

    /**
     * @return Varien_Object
     */
    public function getCheckout()
    {
        return $this->checkout;
    }

    /**
     * @return null|Varien_Object
     */
    public function getRefunds()
    {
       return $this->refunds;
    }

    /**
     * @return Varien_Object
     */
    public function getSummary()
    {
        return $this->summary;
    }

}
