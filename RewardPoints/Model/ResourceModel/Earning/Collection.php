<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Model\ResourceModel\Earning;

use Lof\RewardPoints\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * @return void
     */
    protected function _construct()
    {
    	$this->_init('Lof\RewardPoints\Model\Earning', 'Lof\RewardPoints\Model\ResourceModel\Earning');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = false)
    {
        $this->getSelect()->joinLeft([ 'rs' => $this->getTable('lof_rewardpoints_earning_rule_relationships')],
            'rs.rule_id = main_table.rule_id');
        if($withAdmin){
            $stores = [0];
        }else {
            $stores = [];
        }
        $stores[] = (int)$store;
        $this->addFieldToFilter('rs.store_id', array("in" => $stores));
    	return $this;
    }

    /**
     * @param bool|false $emptyOption
     * @return array
     */
    public function toOptionArray($emptyOption = false)
    {
        $arr = [];
        if ($emptyOption) {
            $arr[0] = [
                'value' => 0,
                'label' => __('-- Please Select --')
            ];
        }
        foreach ($this as $item) {
            $arr[] = [
                'value' => $item->getId(),
                'label' => $item->getName()
            ];
        }
        return $arr;
    }

    /**
     * @return $this
     */
    public function addStatusFilter()
    {
        $this->addFieldToFilter('is_active', \Lof\RewardPoints\Model\Earning::STATUS_ENABLED);
        return $this;
    }

    /**
     * @return $this
     */
    public function addDateFilter()
    {
        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $this->getSelect()
        ->where("(main_table.active_from <= '{$now}' OR ISNULL(main_table.active_from)) AND
            ('{$now}' <= main_table.active_to OR ISNULL(main_table.active_to))");
        return $this;
    }

    /**
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $table = $this->getTable('lof_rewardpoints_earning_rule_customer_group');
        $this->getSelect()
        ->joinLeft(
            [
                'rsg' => $table
            ],
                'rsg.rule_id = main_table.rule_id'
            )
        ->where('rsg.customer_group_id = (?)', $customerGroupId);
        return $this;
    }

}
