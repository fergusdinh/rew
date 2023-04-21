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

namespace Lof\RewardPointsBehavior\Controller\Pinterest;

class Pin extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $customerSession;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Behavior
     */
    protected $rewardsBehavior;

    /**
     * @param \Magento\Framework\App\Action\Context $context        
     * @param \Magento\Customer\Model\Session       $rewardsBehavior
     */
    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior
    ) {
    	parent::__construct($context);
        $this->customerSession   = $customerSession;
        $this->rewardsBehavior  = $rewardsBehavior;
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

    public function execute()
    {
        if($post = $this->getRequest()->getPostValue()){
            $data['status'] = true;
            $url = urldecode($post['url']);

            $customer = $this->getCustomer();
            if($customer){
                $behavior = \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR_PRINTEREST_PIN;
                $totalPoints = $this->rewardsBehavior->processRule($behavior, $customer, $url);
            } else {
                $data['status'] = false;
            }

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
                );
        }
    }
}