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
 * Extend Catalog Rules resource model
 * - modify flow for saving data to catalogrule_product
 *
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Model_Resource_Rule extends Mage_CatalogRule_Model_Resource_Rule
{

    /**
     * 
     */
    protected $highDiscountedProductIds = array();

    /**
     * 
     */
    protected $discountPercentageForHighDiscountedProds = array();

    /**
     * use custom habbit for inserting in catalogrule_product
     * if there are products with discount percentage heigher then the threshold 
     * and the saving is not forced the table all entry for the current rule is removed from catalogrule_product 
     * -> trigger a notification
     * 
     * overwite parent methods
     * @param Mage_CatalogRule_Model_Rule $rule
     */
    public function updateRuleProductData(Mage_CatalogRule_Model_Rule $rule)
    {
        set_time_limit(0);

        $ruleId = $rule->getId();
        $forceSaving = intval($rule->getForceSaving());

        $write = $this->_getWriteAdapter();
        $write->beginTransaction();
        if ($rule->getProductsFilter()) {
            $this->cleanProductData($ruleId, $rule->getProductsFilter());
        } else {
            $this->cleanProductData($ruleId);
        }

        if (!$rule->getIsActive()) {
            $write->commit();
            return $this;
        }

        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        if (empty($websiteIds)) {
            return $this;
        }

        // Get matching product ids
        Varien_Profiler::start('__MATCH_PRODUCTS__');
        $productIds = $rule->getMatchingProductIds();
        Varien_Profiler::stop('__MATCH_PRODUCTS__');
        $customerGroupIds = $rule->getCustomerGroupIds();

        $fromTime = strtotime($rule->getFromDate());
        $toTime = strtotime($rule->getToDate());
        $toTime = $toTime ? ($toTime + self::SECONDS_IN_DAY - 1) : 0;

        $sortOrder = (int) $rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = array();

        $dbReaderAdapt = Mage::getSingleton('core/resource')->getConnection('core_read');
        $productPriceQuery = Mage::helper('catalogrule/data')->getProductPriceQuery();

        try
        {
            $removeAllCurrentChanges = false;

            foreach ($productIds as $productId => $validationByWebsite) {
                foreach ($websiteIds as $websiteId) {
                    $storeIds = Mage::getModel('core/website')->load($websiteId)
                            ->getStoreIds();
                    $bind = array(
                        ':id' => $productId,
                        ':store_id' => implode(',', $storeIds),
                    );
                    $priceStmt = $dbReaderAdapt->query($productPriceQuery, $bind);
                    $productPrice = $priceStmt->fetch();
                    $productPrice = $productPrice['price'];

                    foreach ($customerGroupIds as $customerGroupId) {
                        if (empty($validationByWebsite[$websiteId])) {
                            continue;
                        }
                        $catalogRuleData = array(
                            'rule_id' => $ruleId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'product_id' => $productId,
                            'action_operator' => $actionOperator,
                            'action_amount' => $actionAmount,
                            'action_stop' => $actionStop,
                            'sort_order' => $sortOrder,
                            'default_price' => $productPrice,
                        );
                        $catalogRuleData['website_' . $websiteId . '_price'] = $productPrice;

                        $rulePrice = $this->_calcRuleProductPrice($catalogRuleData);
                        // Compute the percentage of discount from the regular price
                        $discountPercent = round((($productPrice - $rulePrice) / $productPrice) * 100, 1);
                        // Get discount treshold from config table
                        $discountThreshold = Mage::helper('catalogrule/data')->getDiscountThresholdForWebsite($storeIds);

                        if ($discountPercent > $discountThreshold) {
                            $this->highDiscountedProductIds[] = $productId;
                            if (!isset($this->discountPercentageForHighDiscountedProds[$productId]) || $this->discountPercentageForHighDiscountedProds[$productId] < $discountPercent) {
                                $this->discountPercentageForHighDiscountedProds[$productId] = $discountPercent;
                            }
                            $removeAllCurrentChanges = true;
                        }
                        // Unset data not necessary for saving.
                        unset($catalogRuleData['default_price']);
                        unset($catalogRuleData['website_' . $websiteId . '_price']);

                        $rows[] = $catalogRuleData;
                        if (count($rows) == 1000) {
                            if ($forceSaving || $removeAllCurrentChanges === false) {
                                $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                            }
                            $rows = array();
                        }
                    }
                }
            }
            if (!empty($rows)) {
                if ($forceSaving || $removeAllCurrentChanges === false) {
                    $write->insertMultiple($this->getTable('catalogrule/rule_product'), $rows);
                }
            }

            if ($removeAllCurrentChanges === true && !intval($forceSaving)) {
                $write->delete($this->getTable('catalogrule/rule_product'), $write->quoteInto('rule_id=?', $ruleId));
            }

            $write->commit();
        }
        catch (Exception $e)
        {
            $write->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Return all product ids for products with higher discount percentage
     * 
     * @return array
     */
    public function getHighDiscountedProducts()
    {
        return array_unique($this->highDiscountedProductIds);
    }

    /**
     * Return an indexed array keeping the percentage for each product 
     * with higher discount percentage
     * 
     * @return array
     */
    public function getDiscountPercentageForHighDiscountedProds()
    {
        return $this->discountPercentageForHighDiscountedProds;
    }

}
