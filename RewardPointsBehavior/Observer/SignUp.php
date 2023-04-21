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

namespace Lof\RewardPointsBehavior\Observer;

use Lof\RewardPointsBehavior\Model\CustomerReferFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPoints\Model\Transaction;

class SignUp implements ObserverInterface
{
    protected $accountManagement;

	/**
	 * @var \Lof\RewardPointsBehavior\Helper\Behavior
	 */
	protected $rewardsBehavior;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

	/**
	 * @var \Lof\RewardPointsBehavior\Model\Config $rewardsConfig
	 */
	protected $rewardsConfig;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;


    protected $_customerSession;

    protected $_customerRepositoryInterface;
    protected $_coreSession;
	protected $request;
	protected $customerReferFactory;

    /**
     * @param AccountManagementInterface                $accountManagement
     * @param \Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,  
     * @param \Lof\RewardPoints\Helper\Data $rewardsData,
     * @param \Lof\RewardPointsBehavior\Model\Config $rewardsConfig,   
     * @param \Magento\Customer\Model\Session $customerSession,
	 * @param   \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
	 * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger,
	 * @param \Magento\Framework\Session\SessionManagerInterface $coreSession,
	 * @param \Magento\Framework\App\RequestInterface $request
     */
	public function __construct(
		AccountManagementInterface $accountManagement,
		\Lof\RewardPointsBehavior\Helper\Behavior $rewardsBehavior,
		\Lof\RewardPoints\Helper\Data $rewardsData,
		\Lof\RewardPointsBehavior\Model\Config $rewardsConfig,
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		\Magento\Framework\App\RequestInterface $request,
          CustomerReferFactory $customerReferFactory

    ) {
		$this->accountManagement = $accountManagement;
		$this->rewardsBehavior   = $rewardsBehavior;
		$this->rewardsData       = $rewardsData;
		$this->rewardsConfig     = $rewardsConfig;
		$this->rewardsLogger     = $rewardsLogger;
		$this->_customerSession = $customerSession;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
		$this->_coreSession = $coreSession;
		$this->request = $request;
		$this->customerReferFactory = $customerReferFactory;

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
		if ($observer->getCustomer()->getId() && $this->rewardsConfig->isEnable()){
			$customer = $observer->getEvent()->getCustomer();
			$referred_customer_id = $customer->getId();
			$code     = $this->rewardsData->generateRandomString(10);
			$code .= time();
			$dataRequest = $this->request->getPostValue();
			$this->_coreSession->start();
			$customerId = $this->_coreSession->getRefer();
			$referCode = $this->_coreSession->getReferCode();
			if(!$customerId && isset($dataRequest['rw_refer_id']) && is_numeric($dataRequest['rw_refer_id'])){
				$customerId = (int)$dataRequest['rw_refer_id'];
			}
			if(!$referCode && isset($dataRequest['rw_refer_code']) && $dataRequest['rw_refer_code']){
				$referCode = $dataRequest['rw_refer_code'];
			}
			if(!$referCode){
				$referCode = $code;
			}
			$confirmationStatus = $this->accountManagement->getConfirmationStatus($referred_customer_id);
			$customerRefer = $this->_customerSession->getRefer();
			if(!$customerRefer && $customerId) {
				$customer_refer = $this->_customerRepositoryInterface->getById($customerId);
				$modelRefer = $this->customerReferFactory->create();
				$dataRefer = [
				    'refered_email'=>$customer->getEmail(),
				    'referred_name'=>$customer->getFirstName().' '. $customer->getLastName(),
				    'customer_refer_id'=>$customerId,
				    'first_order'=> 0,
                ];
                $modelRefer->setData($dataRefer);
                $modelRefer->save();
				$this->rewardsBehavior->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $referCode, true, Transaction::STATE_COMPLETE, '', false, false, true);
				//Apply Points for referred customer when he register completely
				$this->rewardsBehavior->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer, $referCode, true, Transaction::STATE_COMPLETE, '', true, false, true);
			}
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
            	$this->rewardsBehavior->processRule(Earning::BEHAVIOR_SIGNUP, $customer, $code, false);
            } else {
            	$this->rewardsBehavior->processRule(Earning::BEHAVIOR_SIGNUP, $customer, $code, true);
            }
			
		}
	}
}