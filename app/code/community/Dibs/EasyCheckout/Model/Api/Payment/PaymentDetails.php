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

class Dibs_EasyCheckout_Model_Api_Payment_PaymentDetails
{
    /** @var  string */
    protected $paymentType;

    /** @var  string */
    protected $paymentMethod;

    /** @var Varien_Object  */
    protected $invoiceDetails;

    /** @var Varien_Object  */
    protected $cardDetails;

    /**
     * Dibs_EasyCheckout_Model_Api_Payment_Consumer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->paymentType = isset($data['paymentType']) ? $data['paymentType'] : null;
        $this->paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : null ;
        $this->invoiceDetails = isset($data['invoiceDetails']) ? new Varien_Object($data['invoiceDetails']) : null;
        $this->cardDetails = isset($data['cardDetails']) ? new Varien_Object($data['cardDetails']) : null;
    }

    /**
     * @return null|string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @return null|string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return null|Varien_Object
     */
    public function getInvoiceDetails()
    {
        return $this->invoiceDetails;
    }

    /**
     * @return null|Varien_Object
     */
    public function getCardDetails()
    {
        return $this->cardDetails;
    }

    /**
     * @return mixed|null
     */
    public function getMaskedPan()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)) {
            $result = $cardDetails->getData('maskedPan');
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcLast4()
    {
        $result = null;
        $maskedPan = $this->getMaskedPan();
        if ($maskedPan != ''){
            $result = substr($maskedPan,-4);
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcExpMonth()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)) {
            $expiryDate = $cardDetails->getData('expiryDate');
            if ($expiryDate != '') {
                $result = substr($expiryDate, 0, -2);
            }
        }

        return $result;
    }

    /**
     * @return null|string
     */
    public function getCcExpYear()
    {
        $result = null;
        $cardDetails = $this->getCardDetails();
        if (!empty($cardDetails)) {
            $expiryDate = $cardDetails->getData('expiryDate');
            if ($expiryDate != '') {
                $result = substr($expiryDate, -2);
            }
        }

        return $result;
    }




}
