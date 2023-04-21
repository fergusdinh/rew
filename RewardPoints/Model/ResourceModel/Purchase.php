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

class Purchase extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Lof\RewardPoints\Helper\Customer                 $rewardsCustomer
     * @param \Lof\RewardPoints\Logger\Logger                   $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                    $rewardsConfig
     * @param [type]                                            $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger   = $rewardsLogger;
        $this->rewardsConfig   = $rewardsConfig;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_rewardpoints_purchase', 'purchase_id');
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
        $object->refreshPoints();

        if($object->getSpendPoints() < 0) {
            $this->rewardsLogger->addError('Purchase BUG1:' . $object->getSpendPoints());
            $object->setSpendPoints(0);
        }

        if($object->getSpendCartPoints() < 0) {
            $this->rewardsLogger->addError('Purchase BUG2:' . $object->getSpendCartPoints());
            $object->setSpendCartPoints(0);
        }

        if($object->getSpendCatalogPoints() < 0) {
            $this->rewardsLogger->addError('Purchase BUG3:' . $object->getSpendCatalogPoints());
            $object->setSpendCatalogPoints(0);
        }

        if($object->getDiscount() < 0) {
            $this->rewardsLogger->addError('Purchase BUG4:' . $object->getDiscount());
            $object->setDiscount(0);
        }

        $earnPoitns = $object->getData('earn_points');
        $maximumEarningPoints = $this->rewardsConfig->getMaximumEarningPointsPerOrder();
        if ($earnPoitns && $maximumEarningPoints && ($earnPoitns>$maximumEarningPoints)) {
            $object->setData('earn_points', $maximumEarningPoints);
        }

        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        // Refresh Customer Points
        $params[$object->getQuoteId()] = $object->getSpendPoints();
        $customer = $this->rewardsCustomer->getCustomer($object->getCustomerId(), $params);//will refresh customer points with $params
        if ($customer && $customer->getId()) {
            $customer->save();
        }
        return $this;
    }
}
