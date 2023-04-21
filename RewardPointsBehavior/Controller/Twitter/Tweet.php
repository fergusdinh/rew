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

namespace Lof\RewardPointsBehavior\Controller\Twitter;

class Tweet extends \Magento\Framework\App\Action\Action
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
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @param \Magento\Framework\App\Action\Context                               $context                     
     * @param \Magento\Customer\Model\Session                                     $customerSession             
     * @param \Lof\RewardPointsBehavior\Helper\Behavior                           $rewardsBehavior             
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     */
    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
    ) {
    	parent::__construct($context);
        $this->customerSession              = $customerSession;
        $this->rewardsBehavior              = $rewardsBehavior;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
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
            $code = \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR_TWITTER_TWEET . '-' . $url;
            $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('code', $code);
            if($collection->count()){
                $data['status'] = false;
            } else {
                if($customer){
                    $behavior = \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR_TWITTER_TWEET;
                    $totalPoints = $this->rewardsBehavior->processRule($behavior, $customer, $url);
                } else {
                    $data['status'] = false;
                }
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
                );
        }
    }
}