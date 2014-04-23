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

$installer->endSetup();
