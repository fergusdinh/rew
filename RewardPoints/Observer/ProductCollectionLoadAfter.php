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
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Observer;

class ProductCollectionLoadAfter extends Order
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @param \Magento\Framework\Registry            $coreRegistry
     * @param \Lof\RewardPoints\Helper\Balance\Earn  $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Model\Config         $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->coreRegistry        = $coreRegistry;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsConfig       = $rewardsConfig;
        $this->customerCollectionFactory    = $customerCollectionFactory;
    }
	/**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $collection     = $observer->getCollection();
            $points         = $this->rewardsBalanceEarn->getProductCollectionPoints($collection);
            $spendingPoints = $this->rewardsBalanceSpend->getProductSpendingPoints($collection->getAllIds());


            foreach ($collection as $_product) {
                if(isset($points[$_product->getId()])) {
                    $_product->setEarningPoints($points[$_product->getId()]);
                }
                if(isset($spendingPoints[$_product->getId()]) && $this->rewardsBalanceSpend->getCustomer()->getData()) {
                     $customerId = $this->rewardsBalanceSpend->getCustomer()->getId();
                     $customer = $this->customerCollectionFactory->create()
                    ->addFieldToFilter('customer_id', $customerId)
                    ->getFirstItem();
                    if($spendingPoints[$_product->getId()]['points'] <= $customer->getData('available_points')) {
                    $_product->setSpendingPoints($spendingPoints[$_product->getId()]['points']);
                    $_product->setFinalPrice(0);
                    $_product->setPrice(0);
                    $_product->setEarningPoints(0);
                    }
                }
            }
        }
    }
}
