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
 * Extends the Catalog Rule model.
 * 
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Model_Rule extends Mage_CatalogRule_Model_Rule
{
    
    /**
     * Prepare the force_saving param from the form to save to the rule.
     */
    public function loadPost(array $rule){
        if (isset($rule['force_saving'])) {
            $rule['force_saving'] = 1;            
        } else {
            $rule['force_saving'] = 0;              
        }        
        return parent::loadPost($rule);
    }
    
    
    /**
     * after save we have to proceed other stuff then general flow
     * save the products with high discount - if exists
     * send email notification if exists products with high discount
     */
    public function _afterSave(){
        $updatingResults = $this->_getResource()->updateRuleProductData($this);
var_dump($updatingResults);exit;
//        $highDiscountedProducts = $updatingResults->getHighDiscountedProducts();
//        $discountPercentageForProds = $updatingResults->getDiscountPercentageForHighDiscountedProds();
//        Mage::helper('catalogrule/data')->sendCatalogRuleEmailAlert($this,$highDiscountedProducts, $discountPercentageForProds);
//        Mage::helper('catalogrule/data')->saveHighDiscountedProductsList($this, $highDiscountedProducts);
//
//        $this->cleanModelCache();
//        Mage::dispatchEvent('model_save_after', array('object'=>$this));
//        Mage::dispatchEvent($this->_eventPrefix.'_save_after', $this->_getEventData());

        return $this;
        
    }

    /**
     * 
     * @return if was forced saving
     */
    public function isForceSavingEnabled(){
        return $this->getForceSaving();
    }
    
    
}
