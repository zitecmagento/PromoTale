<?php

/**
 * Zitec_Promotale extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Zitec
 * @package    Zitec_Promotale
 * @copyright  Copyright (c) 2014 Zitec COM
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PromoTale Dashboard Top Promotions Sales block
 *
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Block_Dashboard_Top extends Mage_Adminhtml_Block_Abstract
{

    /**
     * The collection of products with high discount percentage
     * 
     * @var Mage_Catalog_Model_Resource_Product_Collection 
     */
    protected $_rules;

    /**
     * Data aggregated per rule id
     * 
     * @var array
     */
    protected $_aggregatedData;

    /**
     * Return the products with a higher discount saved on rule model
     * 
     * @return Mage_CatalogRule_Model_Resource_Rule_Collection
     */
    public function getCatalogPriceRules()
    {
        if (!$this->_rules) {
            $allStores = Mage::app()->getStores();

            $aggregated_data = Mage::getModel('zitec_promotale/aggregatedData')->getCollection()
                    ->addStoreFilter($this->getRequest()->getParam('store'))
                    ->addSalesAmountFilter();

            foreach ($aggregated_data as $data) {
                $this->_aggregatedData[$data['rule_id']] =  $data->getData('sales');
            }

            if ($this->_aggregatedData) {
                $this->_rules = Mage::getModel('catalogrule/rule')->getCollection()
                        ->addFieldToFilter('rule_id', array('in' => array_keys($this->_aggregatedData)));
            }
        }

        return $this->_rules;
    }

    /**
     * Returns the catalog price rule total sales
     * 
     * @param int $rule_id
     *   The rule id
     * 
     * @return float
     */
    public function getCatalogPriceRuleSales($rule_id)
    {
        return isset($this->_aggregatedData[$rule_id]) ? $this->_aggregatedData[$rule_id] : NULL;
    }

    
    /**
     * Returns the catalog price rule total sales formatted
     * 
     * @param int $rule_id
     *   The rule id
     * 
     * @return string
     */
    public function getCatalogPriceRuleSalesFormatted($rule_id)
    {
        $currencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
        return $currencySymbol . number_format($this->getCatalogPriceRuleSales($rule_id), 2);
    }
}
