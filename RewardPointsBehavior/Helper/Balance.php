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

namespace Lof\RewardPointsBehavior\Helper;

class Balance extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Behavior
     */
    protected $rewardsBehavior;

    /**
     * @param \Magento\Framework\App\Helper\Context                               $context                      
     * @param \Magento\Framework\Message\ManagerInterface                         $messageManager               
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory 
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger                
     * @param \Lof\RewardPointsBehavior\Helper\Behavior                           $rewardsBehavior              
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
		\Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
		\Lof\RewardPoints\Logger\Logger $rewardsLogger,
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior
	) {
		parent::__construct($context);
		$this->messageManager               = $messageManager;
		$this->transactionCollectionFactory = $transactionCollectionFactory;
		$this->rewardsLogger                = $rewardsLogger;
		$this->rewardsBehavior              = $rewardsBehavior;
	}

	public function cancelTransaction($customerId, $code)
	{
		$collection = $this->transactionCollectionFactory->create()
		->addFieldToFilter('customer_id', $customerId)
		->addFieldToFilter('code', $code);
		$transaction = $collection->getFirstItem();
		if($transaction && $transaction->getId()){
			$rewardsCustomer = $transaction->getRewardsCustomer()->refreshPoints();
			if ((int) $transaction->getAmount() <=  (int) $rewardsCustomer->getAvailablePoints()) {
				try {
					$transaction->delete();
					$rewardsCustomer->refreshPoints()->save();
					$this->messageManager->addSuccess	(__('Facebook Like Points has been canceled.'));
				} catch (\Exception $e) {
					$this->rewardsLogger->addError($e->getMessage());
				}
				return true;
			}
		}
		return false;
	}
}