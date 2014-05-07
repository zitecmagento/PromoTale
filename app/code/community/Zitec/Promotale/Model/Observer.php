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
 * Promotale Observer.
 * 
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */

class Zitec_Promotale_Model_Observer
{

    /**
     * Add the rule ids applied to the product to quote item
     * 
     * @param Varien_Event_Observer $argv
     */
    public function checkoutCartProductAddAfter($argv)
    {
        $_product = $argv->getProduct();
        $quote_item = $argv->getQuoteItem();

        $pId = $_product->getId();
        $storeId = $_product->getStoreId();

        $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
        $wId = Mage::app()->getStore($storeId)->getWebsiteId();

        if ($_product->hasCustomerGroupId()) {
            $gId = $_product->getCustomerGroupId();
        } else {
            $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }

        $rules = Mage::getResourceModel('catalogrule/rule')
                ->getRulesFromProduct($date, $wId, $gId, $pId);

        $ruleIds = array();

        foreach ($rules as $rule) {
            $ruleIds[] = $rule['rule_id'];
        }

        if (!empty($ruleIds)) {
            $quote_item->setPromotaleRuleIds(serialize($ruleIds));
        }
    }
}
