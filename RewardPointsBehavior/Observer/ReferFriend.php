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
use Magento\Customer\Api\AccountManagementInterface;
use Lof\RewardPointsBehavior\Model\Earning;

class ReferFriend implements ObserverInterface
{
    protected $accountManagement;

	/**
	 * @var \Lof\RewardPointsBehavior\Helper\Behavior
	 */
	protected $rewardsBehavior;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

	/**
	 * @var \Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	 */
	protected $rewardsConfig;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param AccountManagementInterface                $accountManagement
     * @param \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior  
     * @param \Lof\RewardPoints\Helper\Data             $rewardsData      
     * @param \Lof\RewardPoints\Model\Config            $rewardsConfig    
     * @param \Lof\RewardPoints\Logger\Logger           $rewardsLogger    
     */
	public function __construct(
		AccountManagementInterface $accountManagement,
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
		\Lof\RewardPointsBehavior\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
	) {
		$this->accountManagement = $accountManagement;
		$this->rewardsBehavior   = $rewardsBehavior;
		$this->rewardsData       = $rewardsData;
		$this->rewardsConfig     = $rewardsConfig;
		$this->rewardsLogger     = $rewardsLogger;
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
		
		if ($observer->getCustomer()->getId() && $this->rewardsConfig->isEnable()){
			$customer = $observer->getEvent()->getCustomer();
			$code     = $this->rewardsData->generateRandomString(5);

			$confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
            	$this->rewardsBehavior->processRule(Earning::BEHAVIOR_SIGNUP, $customer, $code, false);
            } else {
            	$this->rewardsBehavior->processRule(Earning::BEHAVIOR_SIGNUP, $customer, $code, true);
            }

			
		}
	}	
}