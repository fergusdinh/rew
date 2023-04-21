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

namespace Lof\RewardPointsRule\Model\ResourceModel\Spending;

class Collection extends \Lof\RewardPoints\Model\ResourceModel\Spending\Collection
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
    	$this->_init('Lof\RewardPointsRule\Model\Spending', 'Lof\RewardPointsRule\Model\ResourceModel\Spending');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
    }
}