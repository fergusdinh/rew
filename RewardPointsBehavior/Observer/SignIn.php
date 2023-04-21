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
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Observer;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPointsBehavior\Model\Earning;

class SignIn implements ObserverInterface
{

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Behavior
     */
    protected $rewardsBehavior;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

	/**
	 * @var \Lof\RewardPoints\Model\Config
	 */
	protected $rewardsConfig;

	/**
	 * @param \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior 
	 * @param \Lof\RewardPoints\Helper\Data             $rewardsData     
	 * @param \Lof\RewardPointsBehavior\Model\Config    $rewardsConfig   
	 */
	public function __construct(
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
		\Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	) {
		$this->rewardsBehavior = $rewardsBehavior;
		$this->rewardsData     = $rewardsData;
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
		if ($this->rewardsConfig->isEnable()) {
			if (($customer = $observer->getCustomer())){
				$code = $this->rewardsData->generateRandomString(5);
				$this->rewardsBehavior->processRule(Earning::BEHAVIOR_SIGNIN, $customer, $code);
			}
		}
	}
}