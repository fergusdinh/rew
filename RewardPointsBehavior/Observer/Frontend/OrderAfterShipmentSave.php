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
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Observer\Frontend;

use Lof\RewardPoints\Helper\Balance;
use Lof\RewardPoints\Helper\Balance\Earn;
use Lof\RewardPoints\Helper\Balance\Order;
use Lof\RewardPoints\Helper\Balance\Spend;
use Lof\RewardPoints\Helper\Data;
use Lof\RewardPoints\Helper\Mail;
use Lof\RewardPoints\Helper\Purchase;
use Lof\RewardPoints\Logger\Logger;
use Lof\RewardPoints\Model\PurchaseFactory;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPointsBehavior\Helper\Behavior;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPointsBehavior\Model\CustomerReferFactory;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderAfterShipmentSave extends \Lof\RewardPoints\Observer\Order
{
    protected $rewardsBehavior;
    protected $collectionFactory;
    protected $_customerRepositoryInterface;
    protected $customerReferFactory;
    public function __construct(\Magento\Framework\Registry $coreRegistry,
                                OrderFactory $orderFactory,
                                \Magento\Sales\Model\Order\Creditmemo $creditmemo,
                                CollectionFactory $quoteCollectionFactory,
                                CartRepositoryInterface $quoteRepository,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\Message\ManagerInterface $messageManager,
                                StoreManagerInterface $storeManager,
                                Session $checkoutSession,
                                Data $rewardsData,
                                Order $rewardsBalanceOrder,
                                Earn $rewardsBalanceEarn,
                                Spend $rewardsBalanceSpend,
                                Balance $rewardsBalance,
                                Purchase $rewardsPurchase,
                                \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
                                Mail $rewardsMail,
                                Logger $rewardsLogger,
                                \Lof\RewardPoints\Model\Config $rewardsConfig,
                                TypeListInterface $cacheTypeList,
                                PurchaseFactory $purchaseFactory,
                                CustomerReferFactory $customerReferFactory,
                                CustomerRepositoryInterface $customerRepositoryInterface,
                                Behavior $rewardsBehavior,
                                \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
                                )
    {
        parent::__construct($coreRegistry, $orderFactory, $creditmemo, $quoteCollectionFactory, $quoteRepository, $objectManager, $messageManager, $storeManager, $checkoutSession, $rewardsData, $rewardsBalanceOrder, $rewardsBalanceEarn, $rewardsBalanceSpend, $rewardsBalance, $rewardsPurchase, $rewardsCustomer, $rewardsMail, $rewardsLogger, $rewardsConfig, $cacheTypeList, $purchaseFactory, $transactionCollectionFactory);
        $this->rewardsBehavior   = $rewardsBehavior;
        $this->customerReferFactory    = $customerReferFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();
            $code            = $this->rewardsData->generateRandomString(10);
            $purchase = $this->rewardsPurchase->getByOrder($order);
            $customerEmail = $order->getCustomerEmail();
            $customerId = $order->getCustomerId();
            $modelRefer = $this->customerReferFactory->create()->getCollection()
                                                ->addFieldToFilter('refered_email', $customerEmail)
                                                ->addFieldToFilter('first_order', array("neq" => 1));
            if(count($modelRefer)>0){
                $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                $refermodelID = $modelRefer->getData()[0]['id'];
                if ($customerReferId) {
                    $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                    $customer_referred = $this->_customerRepositoryInterface->getById($customerId);

                    if ($order && $customer_refer && $this->getConfig()->isEarnAfterShipment()) {
                        $this->rewardsBehavior->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $code, true, Transaction::STATE_COMPLETE, '', false, false, false, true);
                        $this->rewardsBehavior->processRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_referred, $code, true, Transaction::STATE_COMPLETE, '', true, false, false, true);
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
            }else{
                $modelRefer = $this->customerReferFactory->create()->getCollection()
                                                ->addFieldToFilter('refered_email', $customerEmail)
                                                ->addFieldToFilter('first_order', 1);
                if(count($modelRefer)>0){
                    $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                    $refermodelID = $modelRefer->getData()[0]['id'];
                    if ($customerReferId) {
                        $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                        $customer_referred = $this->_customerRepositoryInterface->getById($customerId);

                        if ($order && $customer_refer && $this->getConfig()->isEarnAfterShipment()) {
                            $model = $this->customerReferFactory->create()->load($refermodelID);
                            $number_orders = $model->getNumberOrders();
                            $number_orders += 1;
                            $total_orders = $model->getTotalOrders();
                            $total_orders += (float)$order->getGrandTotal();
                            //advanced process earning rule for refer friend
                            $this->rewardsBehavior->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $code, true, Transaction::STATE_COMPLETE, false, $number_orders);
                            $this->rewardsBehavior->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_referred, $code, true, Transaction::STATE_COMPLETE, true, $number_orders);
                            
                            $model->setNumberOrders($number_orders);
                            $model->setTotalOrders($total_orders);
                            $model->save();
                        }
                    }
                }

            }
        }
    }
}