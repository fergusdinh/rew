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

namespace Lof\RewardPoints\Block\Account\Dashboard;

class Summary extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    protected $customerSession;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context             
     * @param \Lof\RewardPoints\Helper\Customer      $rewardsCustomer     
     * @param \Lof\RewardPoints\Model\Config         $rewardsConfig       
     * @param \Lof\RewardPoints\Helper\Balance\Earn  $rewardsBalanceEarn  
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array                                  $data                
     */
    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
    	parent::__construct($context);
        $this->rewardsCustomer     = $rewardsCustomer;
        $this->rewardsConfig       = $rewardsConfig;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->customerSession           = $customerSession;
    }

    /**
     * Get current reward customer
     * @return \Lof\RewardPoints\Model\Customer
     */
    public function getCustomer()
    {
        $rewardsCustomer = $this->rewardsCustomer->getCustomer();
        return $rewardsCustomer;
    }

    public function getExpireAfterDays()
    {
        return $this->rewardsConfig->getEarningExpire();
    }

    public function getEarningRule()
    {
        $customer = $this->customerSession->getCustomer();
        $customerGroupId = null;
        if($customer){
            $customerGroupId = $customer->getGroupId();
        }
        $rules = $this->rewardsBalanceEarn->getRules('',null,$customerGroupId);
        return $rules;
    }

    public function getSpendingRule()
    {
        return $this->rewardsBalanceSpend->getRules();
    }

}