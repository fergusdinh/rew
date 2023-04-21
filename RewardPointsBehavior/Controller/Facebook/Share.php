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

namespace Lof\RewardPointsBehavior\Controller\Facebook;

use \Lof\RewardPoints\Model\Transaction;
use \Lof\RewardPointsBehavior\Model\Earning;

class Share extends \Magento\Framework\App\Action\Action
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
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Framework\App\Action\Context     $context        
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior
     * @param \Lof\RewardPointsBehavior\Helper\Data     $rewardsData    
     */
    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData
    ) {
    	parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->rewardsBehavior = $rewardsBehavior;
        $this->rewardsData     = $rewardsData;
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
            $url = urldecode($post['url']) . '-' . $this->rewardsData->generateRandomString(5);
            $customer = $this->getCustomer();

            $productId = '';
            if(isset($post['product'])) {
                $productId = $post['product'];
            }

            if($customer){
                $behavior = Earning::BEHAVIOR_FACEBOOK_SHARE;
                $totalPoints = $this->rewardsBehavior->processRule($behavior, $customer, $url, true, Transaction::STATE_COMPLETE, $productId);
            } else {
                $data['status'] = false;
            }

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
                );
        }
    }
}