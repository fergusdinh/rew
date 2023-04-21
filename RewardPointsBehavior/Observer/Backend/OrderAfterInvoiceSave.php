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

namespace Lof\RewardPointsBehavior\Observer\Backend;

use Lof\RewardPoints\Helper\Balance\Spend;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Observer\Order;
use Lof\RewardPointsBehavior\Helper\Behavior;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPointsBehavior\Model\CustomerReferFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;

class OrderAfterInvoiceSave extends Order
{
    protected $rewardsBehavior;
    protected $customerReferFactory;
    protected $_customerRepositoryInterface;
    public function __construct(Registry $coreRegistry,
                                CustomerReferFactory $customerReferFactory,
                                Behavior $rewardsBehavior,
                                CustomerRepositoryInterface $customerRepositoryInterface,
                                \Magento\Sales\Model\OrderFactory $orderFactory, \Magento\Sales\Model\Order\Creditmemo $creditmemo, \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Checkout\Model\Session $checkoutSession, \Lof\RewardPoints\Helper\Data $rewardsData, \Lof\RewardPoints\Helper\Balance\Order $rewardsBalanceOrder, \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn, Spend $rewardsBalanceSpend, \Lof\RewardPoints\Helper\Balance $rewardsBalance, \Lof\RewardPoints\Helper\Purchase $rewardsPurchase, \Lof\RewardPoints\Helper\Customer $rewardsCustomer, \Lof\RewardPoints\Helper\Mail $rewardsMail, \Lof\RewardPoints\Logger\Logger $rewardsLogger, \Lof\RewardPoints\Model\Config $rewardsConfig, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, \Lof\RewardPoints\Model\PurchaseFactory $purchaseFactory, \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory)
    {
        parent::__construct($coreRegistry, $orderFactory, $creditmemo, $quoteCollectionFactory, $quoteRepository, $objectManager, $messageManager, $storeManager, $checkoutSession, $rewardsData, $rewardsBalanceOrder, $rewardsBalanceEarn, $rewardsBalanceSpend, $rewardsBalance, $rewardsPurchase, $rewardsCustomer, $rewardsMail, $rewardsLogger, $rewardsConfig, $cacheTypeList, $purchaseFactory, $transactionCollectionFactory);

        $this->rewardsBehavior   = $rewardsBehavior;
        $this->customerReferFactory    = $customerReferFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            $code = $this->rewardsData->generateRandomString(10);
            $purchase = $this->rewardsPurchase->getByOrder($order);
            $customerEmail = $order->getCustomerEmail();
            $customerId = $order->getCustomerId();
            $modelRefer = $this->customerReferFactory->create()->getCollection()
                                                ->addFieldToFilter('refered_email', $customerEmail)
                                                ->addFieldToFilter('first_order', array("neq" => 1));
                                                
            if(count($modelRefer)>0){
                $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                $refermodelID = $modelRefer->getData()[0]['id'];
                $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                $customer_referred = $this->_customerRepositoryInterface->getById($customerId);
                if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_CANCELED) {
                    return;
                }
                if ($order &&  $customer_refer && $this->getConfig()->isEarnAfterInvoice()) {
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
            }else {
                $modelRefer = $this->customerReferFactory->create()->getCollection()
                                                ->addFieldToFilter('refered_email', $customerEmail)
                                                ->addFieldToFilter('first_order', 1);
                if($modelRefer->count()){
                    $customerReferId = $modelRefer->getData()[0]['customer_refer_id'];
                    $refermodelID = $modelRefer->getData()[0]['id'];

                    $customer_refer = $this->_customerRepositoryInterface->getById($customerReferId);
                    $customer_referred = $this->_customerRepositoryInterface->getById($customerId);
                    if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_CANCELED) {
                        return;
                    }
                    
                    if ($order && $customer_refer && $this->getConfig()->isEarnAfterInvoice()) {
                        $model = $this->customerReferFactory->create()->load($refermodelID);
                        $number_orders = $model->getNumberOrders();
                        $number_orders += 1;
                        $total_orders = $model->getTotalOrders();
                        $total_orders += (float)$order->getGrandTotal();
                        //Advanced process earning rule by refer friend
                        $this->rewardsBehavior->setOrderId($order->getId())->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_refer, $code, true, Transaction::STATE_COMPLETE,  false, $number_orders, false);
                        $this->rewardsBehavior->setOrderId($order->getId())->processAdvacedRule(Earning::BEHAVIOR_REFER_FRIEND, $customer_referred, $code, true, Transaction::STATE_COMPLETE,  true, $number_orders, false);
                        
                        $model->setNumberOrders($number_orders);
                        $model->setTotalOrders($total_orders);
                        $model->save();
                    }
                }
            }
        }

    }
}