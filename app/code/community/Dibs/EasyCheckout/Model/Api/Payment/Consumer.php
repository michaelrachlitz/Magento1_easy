<?php

/**
 * Class Dibs_EasyCheckout_Model_Api_Payment_Consumer
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
