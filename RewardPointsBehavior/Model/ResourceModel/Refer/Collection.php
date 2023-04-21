<?php

namespace Lof\RewardPointsBehavior\Model\ResourceModel\Refer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Lof\RewardPointsBehavior\Model\CustomerRefer::class,
            \Lof\RewardPointsBehavior\Model\ResourceModel\CustomerRefer::class
        );
    }
}
