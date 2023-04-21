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

namespace Lof\RewardPointsBehavior\Model;

use Magento\Framework\Stdlib\DateTime;

class Cron
{
	/**
	 * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
	 */
	protected $customerCollectionFactory;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $date;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
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
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory    $customerCollectionFactory    
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $date                         
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory 
     * @param \Lof\RewardPointsBehavior\Helper\Behavior                           $rewardsBehavior              
     * @param \Lof\RewardPoints\Helper\Data                                       $rewardsData                  
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger                
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
    	\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
		$this->customerCollectionFactory    = $customerCollectionFactory;
		$this->date                         = $date;
		$this->transactionCollectionFactory = $transactionCollectionFactory;
		$this->rewardsBehavior              = $rewardsBehavior;
		$this->rewardsData                  = $rewardsData;
		$this->rewardsLogger                = $rewardsLogger;
    }

    /**
     * @return void
     */
    public function execute()
    {
    	$this->earnBirthdayPoints();
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function earnBirthdayPoints()
    {
    	try {
	    	$customers = $this->customerCollectionFactory->create()
	    	->joinAttribute('dob', 'customer/dob', 'entity_id');

	    	$currentDate  = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $this->date->gmtDate());
	        $currentMonth = (int) $currentDate->format("m");
	        $currentDay   = (int) $currentDate->format("d");

	        $customers->getSelect()->where('extract(month from `at_dob`.`dob`) = ?', $currentMonth)
	        ->where('extract(day from `at_dob`.`dob`) = ?', $currentDay);

	        foreach ($customers as $customer) {
				$code = $this->rewardsData->generateRandomString(5);
	    		$this->rewardsBehavior->processRule(Earning::BEHAVIOR_BIRTHDAY, $customer, $code);
	    	}
	    } catch (\Exception $e) {
	    	$this->rewardsLogger->addError($e->getMessage());
	    }
    }
}