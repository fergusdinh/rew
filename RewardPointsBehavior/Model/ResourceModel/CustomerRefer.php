<?php
namespace Lof\RewardPointsBehavior\Model\ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CustomerRefer extends AbstractDb
{
    protected  function _construct()
    {
        $this->_init('lof_rewardpoints_customer_referred','id');
    }
}