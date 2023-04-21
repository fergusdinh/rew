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
use Magento\Newsletter\Model\Subscriber;
use Magento\Framework\Stdlib\DateTime;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPoints\Model\Transaction;

class NewsletterSubscription implements ObserverInterface
{
	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $date;

	/**
	 * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
	 */
	protected $transactionCollectionFactory;

	/**
	 * @var \Lof\RewardPointsBehavior\Helper\Behavior
	 */
	protected $rewardsBehavior;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

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
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $date                         
	 * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory 
	 * @param \Lof\RewardPointsBehavior\Helper\Behavior                           $rewardsBehavior              
	 * @param \Lof\RewardPoints\Helper\Data                                       $rewardsData                  
	 * @param \Lof\RewardPoints\Helper\Customer                                   $rewardsCustomer              
	 * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger                
	 * @param \Lof\RewardPoints\Model\Config                                      $rewardsConfig                
	 */
	public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
		\Lof\RewardPoints\Helper\Customer $rewardsCustomer,
		\Lof\RewardPoints\Logger\Logger $rewardsLogger,
		\Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	) {
		$this->date                         = $date;
		$this->transactionCollectionFactory = $transactionCollectionFactory;
		$this->rewardsBehavior              = $rewardsBehavior;
		$this->rewardsData                  = $rewardsData;
		$this->rewardsCustomer              = $rewardsCustomer;
		$this->rewardsLogger                = $rewardsLogger;
		$this->rewardsConfig                = $rewardsConfig;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if ($this->rewardsConfig->isEnable()) {
			try {
				
				$subscriber = $observer->getEvent()->getSubscriber();
				$customerId = $subscriber->getCustomerId();
				$status     = $subscriber->getStatus();
				$customer   = $this->rewardsData->getCustomer($customerId);
				if ($customerId && $status && ($status == Subscriber::STATUS_SUBSCRIBED)) {
					$code     = $this->rewardsData->generateRandomString(5);
					$this->rewardsBehavior->processRule(Earning::BEHAVIOR_NEWSLETTER_SIGNUP, $customer, $code);
				} else if ($customerId && $status && ($status == Subscriber::STATUS_UNSUBSCRIBED)) {
					$rules = $this->rewardsBehavior->getRules(Earning::BEHAVIOR_NEWSLETTER_SIGNUP, $customer->getGroupId());
					$transaction = $this->transactionCollectionFactory->create()
					->addFieldToFilter('customer_id', $customerId)
					->addFieldToFilter('status', Transaction::STATE_COMPLETE)
					->addFieldToFilter('action', Earning::BEHAVIOR_NEWSLETTER_SIGNUP)
					->setOrder('transaction_id', 'DESC')
					->getFirstItem();

					if ($transaction && $transaction->getId()) {
						$datetime1 = strtotime($this->date->gmtDate());
						$datetime2 = strtotime($transaction->getCreatedAt());
						$interval  = abs($datetime2 - $datetime1);
						$minutes   = round($interval / 60);
						$rewardsCustomer = $this->rewardsCustomer->getCustomer($customerId);
						if ($rewardsCustomer->getAvailablePoints() >= $transaction->getAmount()) {
							$transaction->setStatus(Transaction::STATE_CANCELED)->save();
						} else {
							$this->rewardsLogger->addError('Prevent SPAM earn points. Available points no enough to cancel the transaction');
						}
					} 
				}
			} catch (\Exception $e) {
				$this->rewardsLogger->addError($e->getMessage());
			}
		}
	}
}