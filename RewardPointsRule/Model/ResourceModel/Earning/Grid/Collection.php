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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Model\ResourceModel\Earning\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use \Lof\RewardPointsRule\Model\ResourceModel\Earning\Collection as EarningCollection;
use Lof\RewardPoints\Model\Earning;

class Collection extends \Lof\RewardPoints\Model\ResourceModel\Earning\Grid\Collection implements SearchResultInterface
{
    protected function _initSelect()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $storeId = $storeManager->getStore()->getStoreId(); 
        $request = $objectManager->get('Magento\Framework\App\Request\Http');  
        $type = $request->getParam('type');
        $filterStoreId = $request->getParam('store');
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        if($type){
            $this->getSelect()->where('main_table.type = (?)', $type);
        }
        if($filterStoreId){
            $this->addStoreFilter((int) $filterStoreId, false);
        }else{
            $this->addStoreFilter(0);
        }
        $this->getSelect()->group(
            'main_table.rule_id'
            );
        return $this;
    }
}
