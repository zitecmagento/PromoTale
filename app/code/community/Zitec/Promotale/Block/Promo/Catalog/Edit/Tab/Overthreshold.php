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
 * This class is used to display a tab in catalogrule promotion 
 * It is used to display products with discount higher then threshold
 *
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Block_Promo_Catalog_Edit_Tab_Overthreshold extends Mage_Adminhtml_Block_Abstract implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * The collection of products with high discount percentage
     * 
     * @var Mage_Catalog_Model_Resource_Product_Collection 
     */
    protected $_products;

    /**
     * Return the products with a higher discount saved on rule model
     * @return type
     */
    public function getRuleOverThresholdProducts()
    {
        if (!$this->_products) {
            $model = Mage::registry('current_promo_catalog_rule');
            $productIds = unserialize($model->getAlertedProducts());

            $this->_products = Mage::getModel('catalog/product')->getCollection()
                    ->addAttributeToSelect(array("name"))
                    ->addAttributeToFilter('entity_id', array('in' => $productIds));
        }

        return $this->_products;
    }

    /**
     * Return true if saving was made even if there was products with high discount percentage
     */
    public function getRuleSavingStatus()
    {
        $model = Mage::registry('current_promo_catalog_rule');
        $force_saving = (bool) $model->getForceSaving();
        return $force_saving;
    }

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('catalogrule')->__('Products with discount higher than threshold');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('catalogrule')->__('Products with discount higher than threshold');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns the number of products with high discount percentage
     * 
     * @return int
     */
    public function countProducts()
    {
        return count($this->getRuleOverThresholdProducts());
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        if ($this->countProducts()) {
            return false;
        }
        return true;
    }

}
