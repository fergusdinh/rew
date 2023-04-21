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
use Magento\Review\Model\Review;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPointsBehavior\Model\Earning;


class ProductReviewSubmited implements ObserverInterface
{
	/**
	 * @var \Magento\Customer\Api\CustomerRepositoryInterface
	 */
	protected $customerRepository;

	/**
	 * @var \Lof\RewardPointsBehavior\Helper\Behavior
	 */
	protected $rewardsBehavior;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

	/**
	 * @var \Lof\RewardPoints\Logger\Logger
	 */
	protected $rewardsLogger;

	/**
	 * @var \Lof\RewardPoints\Model\Config
	 */
	protected $rewardsConfig;

	/**
	 * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository 
	 * @param \Lof\RewardPointsBehavior\Helper\Behavior         $rewardsBehavior    
	 * @param \Lof\RewardPoints\Helper\Data                     $rewardsData        
	 * @param \Lof\RewardPoints\Logger\Logger                   $rewardsLogger      
	 * @param \Lof\RewardPoints\Model\Config                    $rewardsConfig      
	 */
	public function __construct(
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	) {
		$this->customerRepository = $customerRepository;
		$this->rewardsBehavior    = $rewardsBehavior;
		$this->rewardsData        = $rewardsData;
		$this->rewardsLogger      = $rewardsLogger;
		$this->rewardsConfig      = $rewardsConfig;
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
	    	try {
				$review     = $observer->getEvent()->getObject();
				$customerId = $review->getCustomerId();
				$productId = $review->getEntityPkValue();
	    		if ($customerId) {
					$code = $customerId . '-' . $review->getId() . '-' . $productId;
	    			$customer = $this->customerRepository->getById($customerId);
	    			if($review->getStatusId() == Review::STATUS_APPROVED) {
	    				$this->rewardsBehavior->processRule(Earning::BEHAVIOR_REVIEW, $customer, $code);
	    			}
	    			if($review->getStatusId() == Review::STATUS_PENDING) {
	    				$this->rewardsBehavior->processRule(Earning::BEHAVIOR_REVIEW, $customer, $code, true, Transaction::STATE_PROCESSING);
	    			}
	    		}
	    	} catch (\Exception $e) {
	    		$this->rewardsLogger->addError($e->getMessage());
	    	}
	    }
    }
}