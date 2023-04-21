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

namespace Lof\RewardPoints\Model\ResourceModel\Transaction;

use Lof\RewardPoints\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\Transaction', 'Lof\RewardPoints\Model\ResourceModel\Transaction');
        $this->_map['fields']['transaction_id'] = 'main_table.transaction_id';
        $this->_map['fields']['customer']       = 'customer_table.entity_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues("transaction_id");
        if (count($items)) {
            $connection = $this->getConnection();
            foreach ($this as $item) {
                $customerId = $item->getData('customer_id');
                $select = $connection->select()->from(['customer_table' => $this->getTable('customer_grid_flat')])
                                                    ->where('customer_table.entity_id = (?)', $customerId);
                $result = $connection->fetchRow($select);
                $item->setData('name', $result['name']);
                $item->setData('email', $result['email']);
            }
        }
        return parent::_afterLoad();
    }
    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
                ['customer_table' => $this->getTable('customer_grid_flat')],
                'main_table.customer_id = customer_table.entity_id',
                ['name','email']
            );
    }
}
