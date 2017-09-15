<?php

class Dibs_EasyCheckout_Block_Payment_Info extends Mage_Payment_Block_Info
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('dibs/easycheckout/info.phtml');
    }
    /**
     * @param null $transport
     *
     * @return Varien_Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $order = $this->getInfo()->getOrder();
        if ($order){
            $paymentIdLabel = $this->__('Payment ID');
            $maskedPanLabel = $this->__('Masked Pan');
            $paymentData = [
                $paymentIdLabel => $this->getInfo()->getOrder()->getData('dibs_easy_payment_id'),
                $maskedPanLabel => $this->getInfo()->getData('dibs_easy_cc_masked_pan'),
            ];
            $transport->addData($paymentData);

        }
        return $transport;
    }
}
