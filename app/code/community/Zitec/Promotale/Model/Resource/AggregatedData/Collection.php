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
 * AggregatedData collection model
 * 
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */

class Zitec_Promotale_Model_Resource_AggregatedData_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Store associated entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = array(
        'rule' => array(
            'associations_table' => 'catalogrule/rule',
            'aggregated_data_id_field' => 'entity_id',
            'entity_id_field' => 'rule_id'
        )
    );

    /**
     * Pseudo constructor.
     */
    protected function _construct()
    {
        $this->_init('zitec_promotale/aggregatedData');
    }

    /**
     * Adds total sales amount filter
     */
    public function addSalesAmountFilter()
    {
        $this->getSelect()
                ->columns(array('sales' => 'SUM(total)'))
                ->group('rule_id')
                ->order('sales', 'DESC');

        return $this;
    }

    /**
     * Init flag for adding rule website ids to collection result
     *
     * @param bool|null $flag
     *
     * @return Mage_Rule_Model_Resource_Rule_Collection_Abstract
     */
    public function addStoreFilter($storeId = NULL)
    {
        if ($storeId) {
            $this->getSelect()
                ->where('store_id = ?', $storeId);
        }

        return $this;
    }
}
