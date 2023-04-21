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

use \Lof\RewardPointsBehavior\Model\Earning;

class Unlike extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Lof\RewardPointsBehavior\Helper\Behavior
     */
    protected $rewardsBehavior;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @param \Magento\Framework\App\Action\Context     $context        
     * @param \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param \Lof\RewardPointsBehavior\Helper\Balance  $rewardsBalance 
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPointsBehavior\Helper\Balance $rewardsBalance
    ) {
        parent::__construct($context);
        $this->rewardsBehavior = $rewardsBehavior;
        $this->customerSession = $customerSession;
        $this->rewardsBalance  = $rewardsBalance;
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
            $customer = $this->getCustomer();
            if($customer){
                $url = urldecode($post['url']);
                $behavior = Earning::BEHAVIOR_FACEBOOK_LIKE;
                $code = $behavior . '-' . $url;
                $this->rewardsBalance->cancelTransaction($customer->getId(), $code);
                $this->rewardsBehavior->getMessage(0, $behavior);
            } else {
                $data['status'] = false;
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
                );
        }
    }
}