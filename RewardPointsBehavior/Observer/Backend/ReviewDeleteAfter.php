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
use Lof\RewardPointsBehavior\Model\Earning;


class ReviewDeleteAfter implements ObserverInterface
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
     * @var \Lof\RewardPoints\Model\Config
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
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
        	try {
    			$review          = $observer->getDataObject();
    			$customerId      = $review->getCustomerId();
    			$productId       = $review->getEntityPkValue();
    			$code            = Earning::BEHAVIOR_REVIEW . '-' . $customerId . '-' . $review->getId() . '-' . $productId;
    			$transaction     = $this->transactionCollectionFactory->create()->addFieldToFilter('code', $code)->getFirstItem();
    			$rewardsCustomer = $transaction->getRewardsCustomer();
                if($rewardsCustomer) {
        			$statusId        = $review->getStatusId();
        			$availablePoints = (int) $rewardsCustomer->getAvailablePoints();
        			$amount          = (int) $transaction->getAmount();

        	    	if ($transaction->getId() && ($statusId == Review::STATUS_APPROVED) && $rewardsCustomer) {
        	    		if ($rewardsCustomer->getId() && ($amount<=$availablePoints)) {
        	    			$transaction->delete();
        	    		} else {
        	    			$this->rewardsLogger->addError('Reiew Delete: No enough points to cancel the transaction: #' . $transaction->getId());
        	    		}
        	    	} elseif ($transaction->getId()) {
        	    		$transaction->delete();
        	    	}
                }
    	    } catch (\Exception $e) {
    	    	$this->rewardsLogger->addError($e->getMessage());
    	    }
        }
    }
}