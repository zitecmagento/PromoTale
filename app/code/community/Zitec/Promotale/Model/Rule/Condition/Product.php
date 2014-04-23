<?php
class Zitec_Promotale_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    /**
     * Load attribute options
     *
     * @return Mage_CatalogRule_Model_Rule_Condition_Product
     */
    public function loadAttributeOptions()
    {
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