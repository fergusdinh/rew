<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Helper;

use Lof\RewardPointsRule\Model\Earning;

class Data extends \Lof\RewardPoints\Helper\Data
{
    /**
     * Retrive rule global settings
     * @param  int $objectId
     * @return Lof\RewardPointsRule\Model\Earning
     */
    public function getGlobalRule($ruleId){
        if ($this->getType() == Earning::TYPE) {
            $ruleCollection = $this->earningRuleCollectionFactory->create();
            $tableName = 'lof_rewardpoints_earning_rule_relationships';
        } else {
            $ruleCollection = $this->spendingRuleCollectionFactory->create();
            $tableName = 'lof_rewardpoints_spending_rule_relationships';
        }
        
        $ruleCollection->getSelect()->joinLeft(
            [
            'rs' => $tableName
            ],
            'rs.object_id = main_table.rule_id'
            )
        ->where('rs.rule_id = (?)', $ruleId);
        $rule = $ruleCollection->getFirstItem();
        if ($this->getType() == 'earning') {
            $model = $this->objectManager->create('Lof\RewardPointsBehavior\Model\Earning');
        } else {
            $model = $this->objectManager->create('Lof\RewardPointsBehavior\Model\Spending');
        }
        $model->load($rule->getObjectId());
        return $model;
    }

    /**
     * Update Rule Relationship in all store view
     * @param  Lof\RewardPointsRule\Model\Earning
     * @return Lof\RewardPointsRule\Model\ResourceModel\Earning\Collection
     */
    public function updateRuleRelationShip($rule, $useDefault='')
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $_store) {
            try{
                $relationRule = '';
                $relationRule = $this->getRuleInAdmin($rule->getId(), $_store->getId(), false);
                if(!$relationRule->getId()){
                    if ($this->getType() == 'earning') {
                        $model = $this->objectManager->create('Lof\RewardPointsBehavior\Model\Earning');
                    } else {
                        $model = $this->objectManager->create('Lof\RewardPointsBehavior\Model\Spending');
                    }
                    $ruleData = $rule->getData();
                    unset($ruleData['rule_id']);
                    unset($ruleData['form_key']);
                    $ruleData['store_id'] = $_store->getId();
                    $ruleData['object_id'] = $rule->getId(); 
                    $use_default = [];
                    foreach ($ruleData as $k => $v) {
                        $use_default[] = $k;
                    }
                    $ruleData['use_default'] = $use_default;
                    $model->setData($ruleData);
                    $model->save();
                } else{
                    $params = unserialize($relationRule->getUseDefault());
                    if(is_array($params)){
                        foreach ($params as $k => $v) {
                            $relationRule->setData($v, $rule->getData($v));
                        }
                        $relationRule->setData('store_id', $_store->getId());
                        $relationRule->setData('object_id', $rule->getId());
                        $relationRule->save();
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this;
    }
}