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
 * Promotale AggregatedData model.
 * 
 * @category   Zitec
 * @package    Zitec_Promotale
 * @author     Zitec COM <magento@zitec.ro>
 */
class Zitec_Promotale_Model_Resource_AggregatedData extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Pseudo constructor.
     */
    public function _construct()
    {
        $this->_init('zitec_promotale/aggregatedData', 'entity_id');
    }

    /**
     * Updates the catalog price rules sales aggregated data
     */
    public function updateAllAggregatedData()
    {
        /** @var $write Varien_Db_Adapter_Interface */
        $write = $this->_getWriteAdapter();
        $read = $this->_getReadAdapter();

        // Get time promotale_aggredated_data run previously
        $select = $read->select()
                ->from($this->getTable('cron/schedule'), 'finished_at')
                ->where('job_code = :job_code')
                ->where('status = :status')
                ->order('finished_at', 'DESC')
                ->limit(1);
        $bind = array(
            ':job_code' => 'promotale_aggregate_data',
            ':status' => Mage_Cron_Model_Schedule::STATUS_SUCCESS,
        );

        $cronLastRun = $read->fetchOne($select, $bind);
        $date = $cronLastRun ? $cronLastRun : date('Y-m-d h:m:i', strtotime('-1year'));

        // Get all order items having Catalog Price Rules applied sold since last cron run
        $select = $read->select()
                ->from($this->getTable('sales/order_item'))
                ->where('promotale_rule_ids IS NOT NULL')
                ->joinInner($this->getTable('sales/order'), 'entity_id = order_id')
                ->where('sales_flat_order.updated_at >= :date')
                ->where('state = :state');
        $bind = array(
            ':date' => $date,
            ':state' => Mage_Sales_Model_Order::STATE_COMPLETE,
        );

        $results = $read->fetchAll($select, $bind);

        $aggregated_data = array();
        foreach ($results as $result) {
            $rule_ids = unserialize($result['promotale_rule_ids']);
            foreach ($rule_ids as $rule_id) {
                if (!isset($aggregated_data[$rule_id][$result['store_id']])) {
                    $aggregated_data[$rule_id][$result['store_id']] = 0;
                }
                $aggregated_data[$rule_id][$result['store_id']] += $result['row_total_incl_tax'];
            }
        }

        try
        {
            // Generate and save aggregated data row
            foreach ($aggregated_data as $rule_id => $stores_data) {
                foreach ($stores_data as $store_id => $total) {
                    $insertData = array(
                        'rule_id' => $rule_id,
                        'date' => date('Y-m-d'),
                        'store_id' => $store_id,
                        'total' => $total,
                    );

                    $write->insertOnDuplicate(
                            $this->getTable('zitec_promotale/aggregatedData'), $insertData, array(
                        'total' => new Zend_Db_Expr('`total`+' . $total),
                            )
                    );
                }
            } $write->commit();
        }
        catch (Exception $e)
        {
            $write->rollback();
            throw $e;
        }
    }

}
