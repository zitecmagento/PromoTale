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
 * Extends Rule_Condition_Product model defined in core CatalogRule module
 * 
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{

    /**
     * Load attribute options
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {
        /**
         * @method Mage_Catalog_Model_Resource_Eav_Attribute isAllowedForRuleCondition()
         */
        $allowedInputs = array(
            'text',
            'multiselect',
            'textarea',
            'date',
            'datetime',
            'select',
            'boolean',
            'price'
        );

        $productAttributes = Mage::getResourceSingleton('catalog/product_attribute_collection')
                ->addVisibleFilter()
                ->addFieldToFilter('additional_table.' . $this->_isUsedForRuleProperty, 1)
                ->addFieldToFilter('main_table.frontend_input', $allowedInputs)
                ->addFieldToSelect('attribute_code')
                ->addFieldToSelect('frontend_label');

        $attributes = array();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }
}
