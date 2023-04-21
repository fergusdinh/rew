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

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPoints\Model\Config;

class CheckoutCartIndex implements ObserverInterface
{
	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $coreRegistry = null;

	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

	/**
	 * @var \Lof\RewardPoints\Model\Config
	 */
	protected $rewardsConfig;

	/**
	 * @param \Magento\Framework\Registry       $registry
	 * @param \Lof\RewardPoints\Helper\Purchase $rewardsPurchase
	 * @param \Lof\RewardPoints\Model\Config    $rewardsConfig
	 */
	public function __construct(
		\Magento\Framework\Registry $registry,
		\Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
		\Lof\RewardPoints\Model\Config $rewardsConfig
	) {
		$this->coreRegistry    = $registry;
		$this->rewardsPurchase = $rewardsPurchase;
		$this->rewardsConfig   = $rewardsConfig;
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
		if ($this->rewardsConfig->isEnable() && !$this->coreRegistry->registry('rewards_purchase')) {
			$purchase = $this->rewardsPurchase->getPurchase()->verifyPointsWithCartItems()->save();
			$this->coreRegistry->register('rewards_purchase', $purchase);
		}
	}
}
