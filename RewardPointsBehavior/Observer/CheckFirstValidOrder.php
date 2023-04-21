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

use Lof\RewardPoints\Model\Config as RewardPointsConfig;
use Lof\RewardPoints\Observer\Order;
use Lof\RewardPointsBehavior\Helper\Behavior;
use Lof\RewardPointsBehavior\Model\CustomerReferFactory;
use Lof\RewardPointsBehavior\Model\ResourceModel\CustomerRefer\CollectionFactory as ReferCollectionFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPoints\Model\Transaction;
class CheckFirstValidOrder extends Order implements ObserverInterface
{
    protected $accountManagement;

    /**
     * @var Behavior
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
    protected $orderFactory;
    protected $_coreSession;
    protected $request;
    protected $customerReferFactory;
    protected $collectionFactory;
    protected $rewardPointsConfig;
    protected $messageManager;


    /**
     * @param AccountManagementInterface $accountManagement
     * @param Behavior $rewardsBehavior
     * @param \Lof\RewardPoints\Helper\Data $rewardsData
     * @param \Lof\RewardPointsBehavior\Model\Config $rewardsConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CustomerReferFactory $customerReferFactory
     * @param ReferCollectionFactory $collectionFactory
     * @param RewardPointsConfig $rewardPointsConfig
     */
    public function __construct(
        AccountManagementInterface $accountManagement,
        Behavior $rewardsBehavior,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPointsBehavior\Model\Config $rewardsConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\RequestInterface $request,
        CustomerReferFactory $customerReferFactory,
        ReferCollectionFactory $collectionFactory,
        RewardPointsConfig $rewardPointsConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager

    ) {
        $this->accountManagement = $accountManagement;
        $this->rewardsBehavior   = $rewardsBehavior;
        $this->rewardsData       = $rewardsData;
        $this->rewardsConfig     = $rewardsConfig;
        $this->rewardsLogger     = $rewardsLogger;
        $this->_customerSession  = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->orderFactory      = $orderFactory;
        $this->_coreSession      = $coreSession;
        $this->request           = $request;
        $this->customerReferFactory = $customerReferFactory;
        $this->collectionFactory    = $collectionFactory;
        $this->rewardPointsConfig   = $rewardPointsConfig;
        $this->messageManager   = $messageManager;

    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->getOrder()->getId() && $this->rewardsConfig->isEnable()){
            $order = $observer->getEvent()->getOrder();
            $customerEmail = $order->getCustomerEmail();
            $customerId = $order->getCustomerId();
            $orderStatus = $order->getStatus();
            $storeId = $this->rewardsBehavior->getStore()->getId();
            $firstOrder = $this->orderFactory->create()->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('customer_email',array('eq'=>$customerEmail))->getFirstItem();
            $code            = $this->rewardsData->generateRandomString(10);
            $modelRefer      = $this->collectionFactory->create()->addFieldToFilter('refered_email',$customerEmail)
                                                                  ->addFieldToFilter('first_order', array("neq" => 1));
            if(count($modelRefer)>0){
                if($order && $this->rewardsData->getConfig()->isEarnAfterCheckout()){
                    $earningInStatuses = $this->rewardsData->getConfig()->getGeneralEarnInStatuses();
                    $transaction_status = Transaction::STATE_PROCESSING;
                    if(in_array($orderStatus, $earningInStatuses)){
                        $transaction_status = Transaction::STATE_COMPLETE;
                    }
                    $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                    $refermodelID    = $modelRefer->getData()[0]['id'];
                    if($customerReferId){
                        $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                        $customer_referred = $this->_customerRepositoryInterface->getById($customerId);
                        $this->rewardsBehavior->setOrderId($order->getId())->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $code, true, $transaction_status, '', false, false, false, true);
                        $this->rewardsBehavior->setOrderId($order->getId())->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_referred, $code, true, $transaction_status, '', true, false, false, true);
                        $model = $this->customerReferFactory->create()->load($refermodelID);
                        $number_orders = 0;
                        $number_orders += 1;
                        $total_orders = 0;
                        $total_orders += (float)$order->getGrandTotal();
                        $model->setNumberOrders($number_orders);
                        $model->setTotalOrders($total_orders);
                        $model->setFirstOrder(1)->save();
                    }
                }
            }else {
                if($order && $this->rewardsData->getConfig()->isEarnAfterCheckout()){
                    $earningInStatuses = $this->rewardsData->getConfig()->getGeneralEarnInStatuses();
                    $transaction_status = Transaction::STATE_PROCESSING;
                    if(in_array($orderStatus, $earningInStatuses)){
                        $transaction_status = Transaction::STATE_COMPLETE;
                    }
                    $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                    $refermodelID    = $modelRefer->getData()[0]['id'];
                    if($customerReferId){
                        $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                        $customer_referred = $this->_customerRepositoryInterface->getById($customerId);
                        $model = $this->customerReferFactory->create()->load($refermodelID);
                        $number_orders = $model->getNumberOrders();
                        $number_orders += 1;
                        $total_orders = $model->getTotalOrders();
                        $total_orders += (float)$order->getGrandTotal();
                        //Process advanced earning points of refer to friend
                        $this->rewardsBehavior->setOrderId($order->getId())->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $code, true, $transaction_status, false, $number_orders);
                        $this->rewardsBehavior->setOrderId($order->getId())->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_referred, $code, true, $transaction_status, true, $number_orders);
                        
                        $model->setNumberOrders($number_orders);
                        $model->setTotalOrders($total_orders);
                        $model->save();
                    }
                }
            }

        }
     }
}