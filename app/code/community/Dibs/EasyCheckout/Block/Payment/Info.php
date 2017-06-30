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
        if ($order && !empty($order->getDibsEasyPaymentId())){
            $transport->addData([$this->__('Payment ID') => $order->getDibsEasyPaymentId()]);
        }
        return $transport;
    }
}
