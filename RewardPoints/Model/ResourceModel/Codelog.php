<?php


namespace Lof\RewardPoints\Model\ResourceModel;

class Codelog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_rewardpoints_codelog', 'log_id');
    }
}
