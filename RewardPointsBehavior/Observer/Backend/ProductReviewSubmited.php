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

namespace Lof\RewardPointsBehavior\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Review\Model\Review;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPointsBehavior\Model\Earning;


class ProductReviewSubmited implements ObserverInterface
{
	/**
	 * @var \Lof\RewardPoints\Logger\Logger
	 */
	protected $rewardsLogger;

	/**
	 * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
	 */
	protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPointsBehavior\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger                
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory 
     * @param \Lof\RewardPoints\Helper\Mail                                       $rewardsMail                  
     * @param \Lof\RewardPointsBehavior\Model\Config                                      $rewardsConfig                
     */
	public function __construct(
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	) {
        $this->rewardsLogger                = $rewardsLogger;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardsConfig                = $rewardsConfig;
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
                $review          = $observer->getEvent()->getObject();
                $customerId      = $review->getCustomerId();
                $productId       = $review->getEntityPkValue();
                $code            = Earning::BEHAVIOR_REVIEW . '-' . $customerId . '-' . $review->getId() . '-' . $productId;
                $transaction     = $this->transactionCollectionFactory->create()
                ->addFieldToFilter('code', $code)
                ->getFirstItem();
                $rewardsCustomer = $transaction->getRewardsCustomer();
                if($rewardsCustomer){
                    $statusId        = $review->getStatusId();

            		if ($transaction->getId() && ($statusId == Review::STATUS_APPROVED)) {
            			$transaction->setStatus(Transaction::STATE_COMPLETE)->save();
                        $emailData['email_message'] = $transaction->getEmailMessage();
                        $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $emailData);
            		}

                    if ($transaction->getId() && ($rewardsCustomer->getAvailablePoints()<=$transaction->getAmount()) && ($statusId == Review::STATUS_PENDING)) {
                        $transaction->setStatus(Transaction::STATE_PROCESSING)->save();
                    }
                }
        	} catch (\Exception $e) {
        		$this->rewardsLogger->addError($e->getMessage());
        	}
        }
    }
}