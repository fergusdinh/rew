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

namespace Lof\RewardPoints\Model\ResourceModel;

class Transaction extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Lof\RewardPoints\Logger\Logger                   $logger
     * @param \Lof\RewardPoints\Model\Config                    $rewardsConfig
     * @param [type]                                            $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->rewardsLogger = $rewardsLogger;
        $this->rewardsConfig = $rewardsConfig;
         $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_rewardpoints_transaction', 'transaction_id');
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $todayDate = new \DateTime(date('Y-m-d H:i:s'));
        if ($object->getCreatedAt()!='') {
            $object->setCreatedAt($todayDate);
        }

        $object->setUpdatedAt($todayDate);
        if ($object->getTotalPoints() < $object->getAvailablePoints()) {
            $this->rewardsLogger->addError(__('Customer total points is smaller than avaiable points'));
        }

        // Skip is admin
        if (!$object->getAdminUserId()) {
            if (!$object->getExpiresAt()) {
                $expireDate = $this->rewardsConfig->getEarningExpireDate();
                $object->setExpiresAt($expireDate);
            }
        }

        return $this;
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        try {
            // Refresh customer points
            $customer = $object->getRewardsCustomer();
            if($customer) {
                $customer->refreshPoints()->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $this;
    }


}
