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

namespace Lof\RewardPointsBehavior\Model\ResourceModel\Earning\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;

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
        if(!$type){
            $type = \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR;
        }
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        $this->getSelect()->where('main_table.type = (?)', $type);
        if($filterStoreId){
            $this->addStoreFilter((int) $filterStoreId, false);
        }else {
            //$this->addStoreFilter((int) $storeId);
        }
        $this->getSelect()->group(
            'main_table.rule_id'
            );
        return $this;
    }
}
