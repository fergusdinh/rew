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

class Notification extends \Magento\Framework\View\Element\Template
{   
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

	/**
	 * @var \Lof\RewardPoints\Helper\Customer
	 */
	protected $rewardsCustomer;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context    
     * @param \Lof\RewardPoints\Helper\Customer                $rewardsCustomer 
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        array $data = []
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->rewardsCustomer = $rewardsCustomer;
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer;
    }

    public function getApplyCodeUrl()
    {
        return $this->getUrl('*/*/applysettings');
    }
}