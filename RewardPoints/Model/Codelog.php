<?php


namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\Data\CodelogInterface;

class Codelog extends \Magento\Framework\Model\AbstractModel implements CodelogInterface
{

    protected $_eventPrefix = 'lof_rewardpoints_codelog';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Codelog');
    }

    public function validateCode($codeId='', $customerId = 0, $storeId = 0)
    {
        $flag = true;
        if($codeId && $customerId){
            $data = $this->getCollection()->addFieldToFilter('code_id', array('eq' => (int)$codeId))
                                            ->addFieldToFilter('user_id',array('eq' => (int)$customerId))
                                            ->addFieldToFilter('store_id',array('eq' => (int)$storeId));
            if($data->count() > 0){
                $flag = false;
            }
        }else{
            $flag = false;
        }
        return $flag;
    }

    /**
     * Get log_id
     * @return string
     */
    public function getLogId()
    {
        return $this->getData(self::LOG_ID);
    }

    /**
     * Set log_id
     * @param string $logId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setLogId($logId)
    {
        return $this->setData(self::LOG_ID, $logId);
    }

    /**
     * Get code_id
     * @return string
     */
    public function getCodeId()
    {
        return $this->getData(self::CODE_ID);
    }

    /**
     * Set code_id
     * @param string $codeId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setCodeId($codeId)
    {
        return $this->setData(self::CODE_ID, $codeId);
    }

    /**
     * Get user_id
     * @return string
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * Set user_id
     * @param string $userId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Get store_id
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set user_id
     * @param string $store_id
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setStoreId($store_id)
    {
        return $this->setData(self::STORE_ID, $store_id);
    }
}
