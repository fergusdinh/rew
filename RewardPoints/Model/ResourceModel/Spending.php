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

namespace Lof\RewardPoints\Model\ResourceModel;

class Spending extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
        ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
    	$this->_init('lof_rewardpoints_spending_rule', 'rule_id');
    }


    /**
     * Process block data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Cms\Model\ResourceModel\Page
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = [
            'object_id = ?' => (int)$object->getId(),
            'store_id !=  0',
        ];
        $this->getConnection()->delete($this->getTable('lof_rewardpoints_spending_rule_relationships'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $data = $object->getData();

        // CUSTOMER GROUP
        $oldCustomerGroups = $this->lookupCustomerGroupIds($object->getId());
        $newCustomerGroups = (array)$object->getCustomerGroupIds();

        $table = $this->getTable('lof_rewardpoints_spending_rule_customer_group');
        $insert = array_diff($newCustomerGroups, $oldCustomerGroups);
        $delete = array_diff($oldCustomerGroups, $newCustomerGroups);
        if ($delete) {
            $where = ['rule_id = ?' => (int)$object->getId(), 'customer_group_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $insertData = [];
            foreach ($insert as $customerGroupId) {
                $insertData[] = [
                'rule_id'           => (int)$object->getId(),
                'customer_group_id' => (int)$customerGroupId
                ];
            }
            try{
                $this->getConnection()->insertMultiple($table, $insertData);
            } catch(\Exception $e) {
                $this->rewardsLogger->addError($e->getMessage());
            }
        }

        // Rule Relationships
        $table = $this->getTable('lof_rewardpoints_spending_rule_relationships');
        $oldRelationshipRules = $this->lookupRelationshipRuleIds($object->getId());
        //$newRelationshipRules = (array)$this->_storeManager->getStores();
        $where = ['rule_id = ?' => (int)$object->getId()];

        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('lof_rewardpoints_spending_rule_relationships'))->where(
            'rule_id = :rule_id'
            );
        $binds = [':rule_id' => (int)$object->getId()];
        $isExit = $connection->fetchRow($select, $binds);
        $objectId = $object->getObjectId()?$object->getObjectId():$object->getId();
        if(!$isExit){
            $this->getConnection()->delete($table, $where);
            $param = '';
            if(isset($data['use_default'])) $param = $data['use_default'];
            if($param && is_array($param)){
                $param = serialize($param);
            }
            $this->getConnection()->insert($table, [
                'object_id'   => $objectId,
                'rule_id'     => $object->getId(),
                'store_id'    => $object->getStoreId(),
                'use_default' => $param
                ]);
        } else if (isset($data['use_default'])){
            if (is_array($data['use_default'])) $data['use_default'] = serialize($data['use_default']);
            $this->getConnection()->update($table, ['use_default' => $data['use_default']], [
                    'object_id = ?' => $objectId,
                    'rule_id   = ?' =>  $object->getId()
                ]);
        }
        return parent::_afterSave($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable('lof_rewardpoints_spending_rule_store'),
            'store_id'
            )->where(
            'rule_id = :rule_id'
            );

            $binds = [':rule_id' => (int)$id];

            return $connection->fetchCol($select, $binds);
        }


    /**
     * Get customer group ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function getRuleParam($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('lof_rewardpoints_spending_rule_relationships'))->where(
            'rule_id = :rule_id'
            );
        $binds = [':rule_id' => (int)$id];
        return $connection->fetchRow($select, $binds);
    }

    /**
     * Get customer group ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupCustomerGroupIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('lof_rewardpoints_spending_rule_customer_group'),'customer_group_id')->where(
            'rule_id = :rule_id'
            );
        $binds = [':rule_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }


    /**
     * Get customer group ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupRelationshipRuleIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('lof_rewardpoints_spending_rule_relationships'),'rule_id')->where(
            'object_id = :object_id'
            );
        $binds = [':object_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $customerGroupIds = $this->lookupCustomerGroupIds($object->getId());
            $object->setData('customer_group_ids', $customerGroupIds);

            $relationshipParam = $this->getRuleParam($object->getId());
            if(isset($relationshipParam['use_default'])){
                $object->setData('use_default', $relationshipParam['use_default']);
            }
            if(isset($relationshipParam['object_id'])){
                $object->setData('object_id', $relationshipParam['object_id']);
            }
            if(isset($relationshipParam['store_id'])){
                $object->setData('store_id', $relationshipParam['store_id']);
            }
        }
        return parent::_afterLoad($object);
    }

    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrive store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }

}
