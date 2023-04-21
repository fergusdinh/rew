<?php


namespace Lof\RewardPoints\Model\ResourceModel\Codelog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Lof\RewardPoints\Model\Codelog',
            'Lof\RewardPoints\Model\ResourceModel\Codelog'
        );
    }
}
