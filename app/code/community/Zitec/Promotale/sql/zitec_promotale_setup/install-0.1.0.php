<?php

/**
 * Zitec_Promotale extension
 *
 * @category   Zitec
 * @package    Zitec_Promotale
 * @copyright  Zitec COM <magento@zitec.ro>
 */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('catalogrule'), 'alerted_products', array(
    'nullable' => TRUE,
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'comment' => 'Set here the serialized - products that have the discount bigger then the choosen threshold',
));
$installer->getConnection()->addColumn($this->getTable('catalogrule'), 'force_saving', array(
    'type' => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
    'default' => '0',
    'comment' => 'keep here the preferance on saving: - ignore or not discount threshold',
));

// Add 'promotale_rule_ids' attribute for QUOTE ITEMS
$installer->getConnection()
        ->addColumn($this->getTable('sales/quote_item'), 'promotale_rule_ids', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'required' => FALSE,
            'visible' => FALSE,
            'comment' => 'Catalog Price Rule Ids applied'
        ));

// Add 'promotale_rule_ids' attribute  for ORDER ITEMS
$installer->getConnection()
        ->addColumn($this->getTable('sales/order_item'), 'promotale_rule_ids', array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'required' => FALSE,
            'visible' => FALSE,
            'comment' => 'Catalog Price Rule Ids applied'
        ));

$installer->endSetup();
