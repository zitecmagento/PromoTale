<?php

class Zitec_Promotale_Model_Observer
{

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
