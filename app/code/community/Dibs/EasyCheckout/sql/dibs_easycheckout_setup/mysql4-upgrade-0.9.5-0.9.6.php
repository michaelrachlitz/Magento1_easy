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
$connection = $installer->getConnection();
$orderTable = $installer->getTable('sales_flat_order_payment');
$connection->addColumn($orderTable, 'dibs_easy_payment_type',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => '200',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Dibs Easy Payment Type'
    )
);
$quoteTable = $installer->getTable('sales_flat_quote_payment');
$connection->addColumn($quoteTable, 'dibs_easy_payment_type',
    array(
        'type'=>Varien_Db_Ddl_Table::TYPE_TEXT,
        'length' => '200',
        'unsigned' => true,
        'nullable' => true,
        'default' => NULL,
        'comment' => 'Dibs Easy Payment Type'
    )
);
$installer->endSetup();