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

$installer = $this;
$installer->startSetup();

/** @var Mage_Sales_Model_Resource_Quote_Collection $quoteCollection */
$quoteCollection = Mage::getModel('sales/quote')->getCollection();
$quoteCollection->join(array('order'=> 'sales/order'),'order.quote_id=main_table.entity_id', array('order_id' =>'order.entity_id'));
$quoteCollection->addFieldToFilter('main_table.dibs_easy_payment_id',array('notnull' => true));

$items = $quoteCollection->getItems();

if (!empty($items)){
    $orderPaymentIds = array();
    foreach ($items as $item){
        $orderPaymentIds[$item->getOrderId()] = $item->getDibsEasyPaymentId();
    }

    $orderCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('entity_id',array('in'=>array_keys($orderPaymentIds)));
    $orders = $orderCollection->getItems();
    foreach ($orders as $order){
        if (isset($orderPaymentIds[$order->getId()])){
            $order->setDibsEasyPaymentId($orderPaymentIds[$order->getId()]);
            $order->save();
        }
    }
}

$installer->endSetup();

