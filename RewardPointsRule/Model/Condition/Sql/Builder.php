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
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Model\Condition\Sql;

use Magento\Rule\Model\Condition\Combine;

class Builder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Attach conditions filter to collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param Combine $combine
     *
     * @return void
     */
    
    public function attachConditionToCollection(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        Combine $combine
    ): void {
        
        $this->_connection = $collection->getResource()->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $whereExpression = (string)$this->_getMappedSqlCombination($combine);

        $whereExpression  = str_replace("`value`, 0) IN ('","`value`, 0) like '%", $whereExpression);
        $whereExpression  = str_replace("')) )","%') )", $whereExpression);
        $whereExpression  = str_replace("AND(IFNULL(","AND ((IFNULL(", $whereExpression);

        if (!empty($whereExpression)) {
            // Select ::where method adds braces even on empty expression
            $collection->getSelect()->where($whereExpression);
        }
    }

}
