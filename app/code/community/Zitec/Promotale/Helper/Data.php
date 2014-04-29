<?php

/**
 * 
 */
class Zitec_Promotale_Helper_Data extends Mage_CatalogRule_Helper_Data
{

    const XML_PATH_CATALOG_RULE_THRESHOLD = 'catalog/promotale_alert/alert_threshold';
    const XML_PATH_CATALOGRULE_RECEIVER_EMAIL = 'trans_email/promotale_alert/email';
    const XML_PATH_CATALOGRULE_RECEIVER_NAME = 'trans_email/promotale_alert/name';
    const XML_PATH_CATALOGRULE_TEMPLATE = 'catalog/promotale_alert/template';
    const XML_PATH_CATALOGRULE_IDENTITY = 'catalog/promotale_alert/identity';

    /**
     * create query to request product price
     * @return string/query
     */
    public function getProductPriceQuery()
    {
        $dbReaderAdapt = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableCPED = Mage::getSingleton('core/resource')->getTableName('catalog_product_entity_decimal');
        $tableEAVA = Mage::getSingleton('core/resource')->getTableName('eav_attribute');
        $query = $dbReaderAdapt->select()
                ->from(array('cp' => $tableCPED), array('price' => 'MIN(cp.value)'))
                ->joinInner(array('eav' => $tableEAVA), 'eav.attribute_id = cp.attribute_id AND eav.entity_type_id = cp.entity_type_id', array())
                ->where('cp.entity_id = :id ')
                ->where('cp.store_id IN (:store_id) OR cp.store_id = 0')
                ->where('attribute_code = ?', 'price')
                ->limit(1);

        return $query;
    }

    /**
     *  get the threshold config from admin 
     * @param type $storeIds
     * @return int
     */
    public function getDiscountThresholdForWebsite($storeIds)
    {
        if (!empty($storeIds)) {
            $discountThreshold = Mage::getStoreConfig(self::XML_PATH_CATALOG_RULE_THRESHOLD, reset($storeIds));
        } else {
            $discountThreshold = Mage::getStoreConfig(self::XML_PATH_CATALOG_RULE_THRESHOLD);
        }
        $discountThreshold = intval($discountThreshold);
        if ($discountThreshold > 100) {
            $discountThreshold = 100;
        }
        return $discountThreshold;
    }

    /**
     * save the ids for products with high discount percentage on the rule (serialized)
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param type $productIds
     * @return boolean
     */
    public function saveHighDiscountedProductsList(Mage_CatalogRule_Model_Rule $rule, $productIds = array())
    {
        $resource = Mage::getSingleton('core/resource');
        $ruleTable = $resource->getTableName('catalogrule');

        $writeAdapter = $resource->getConnection('core_write');

        if (empty($productIds) && $rule->getIsActive()) {
            $writeAdapter->update(
                    $ruleTable, array(
                'alerted_products' => NULL,
                    ), "rule_id = '{$rule->getId()}'"
            );
            return false;
        }

        $isActive = ($rule->getForceSaving() == 0) ? 0 : 1;

        $writeAdapter->update(
                $ruleTable, array(
            'alerted_products' => serialize($productIds),
            'is_active' => $isActive,
                ), "rule_id = '{$rule->getId()}'"
        );
        return true;
    }

    /**
     * send the email in case of existing products with hight discount percentage to the emails selected in admin
     * @param Mage_CatalogRule_Model_Rule $rule
     * @param type $productIds
     * @param type $discountPercentageForProds
     * @return boolean
     */
    public function sendCatalogRuleEmailAlert(Mage_CatalogRule_Model_Rule $rule, $productIds = array(), $discountPercentageForProds = array())
    {
        try
        {
            if (empty($productIds)) {
                return false;
            }
            $products = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('name')->addAttributeToFilter('entity_id', array(
                'in' => $productIds));

            $receiverPath = Mage::getStoreConfig(self::XML_PATH_CATALOGRULE_IDENTITY);

            $receiverName = Mage::getStoreConfig('trans_email/' . $receiverPath . '/name');
            $receiverEmail = Mage::getStoreConfig('trans_email/' . $receiverPath . '/email');
            if (!isset($receiverName, $receiverEmail)) {
                $receiverName = Mage::getStoreConfig(self::XML_PATH_CATALOGRULE_RECEIVER_EMAIL);
                $receiverEmail = Mage::getStoreConfig(self::XML_PATH_CATALOGRULE_RECEIVER_NAME);
            }

            $emailTemplate = Mage::getStoreConfig(self::XML_PATH_CATALOGRULE_TEMPLATE);

            $translate = Mage::getSingleton('core/translate');
            $translate->setTranslateInline(false);

            $sender = array(
                'name' => $this->__('Promotion Alert - threshold exceeded'),
                'email' => $this->__('no-reply@zitec.com'),
            );

            $emailAdresses = explode(',', $receiverEmail);
            if (count($emailAdresses) == 1) {
                $receiverEmail = $emailAdresses[0];
                $emailAdresses = explode(';', $receiverEmail);
            }
            $receiverEmail = null;
            foreach ($emailAdresses as $receiverEmail) {
                $receiverEmail = trim($receiverEmail);
                Mage::getModel('core/email_template')
                        ->setDesignConfig(array('area' => 'frontend'))
                        ->sendTransactional(
                                $emailTemplate, $sender, $receiverEmail, $receiverName, array(
                            'rule' => $rule,
                            'ruleAdminUrl' => Mage::getUrl('*/admin/promo_catalog/edit/', array('id' => $rule->getRuleId())),
                            'receiverEmail' => $receiverEmail,
                            'receiverName' => $receiverName,
                            'savingStatus' => $rule->getForceSaving() == 1 ?
                                    $this->__('Promotion was saved even if there are some products with discount higher than threshold') :
                                    $this->__('Promotion was deactivated because there are some products with discount higher than threshold'),
                            'countProducts' => count($products),
                            'products' => $products,
                            'discountPercentageForProds' => $discountPercentageForProds
                                )
                );
            }

            $translate->setTranslateInline(true);
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            return false;
        }

        return true;
    }

}
