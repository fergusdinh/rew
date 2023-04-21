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

namespace Lof\RewardPoints\Helper;

use Lof\RewardPoints\Model\Transaction;

class Balance extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Lof\RewardPoints\Model\PurchaseFactory
     */
    protected $purchaseFactory;

    /**
     * @var \Lof\RewardPoints\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $rewardPointCustomerFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context                               $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $dateTime
     * @param \Lof\RewardPoints\Model\PurchaseFactory                             $purchaseFactory
     * @param \Lof\RewardPoints\Model\TransactionFactory                          $transactionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Lof\RewardPoints\Helper\Customer                                   $rewardsCustomer
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger
     * @param \Lof\RewardPoints\Helper\Mail                                       $rewardsMail
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Model\PurchaseFactory $purchaseFactory,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $rewardPointCustomerFactory
    ) {
        parent::__construct($context);
        $this->dateTime                     = $dateTime;
        $this->purchaseFactory              = $purchaseFactory;
        $this->transactionFactory           = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardsCustomer              = $rewardsCustomer;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardPointCustomerFactory  = $rewardPointCustomerFactory;
    }

    public function getByOrder($order, $action = '') {
        $collection = $this->transactionCollectionFactory->create()
        ->addFieldToFilter('order_id', $order->getId());

        if ($action) {
            $collection->addFieldToFilter('action', $action);
        }

        $balance = $collection->getFirstItem();
        return $balance;
    }

    public function getTransaction($field, $value)
    {
        $collection = $this->transactionCollectionFactory->create()
        ->addFieldToFilter($field, $value);
        $transaction = $collection->getFirstItem();
        return $transaction;
    }

    public function proccessTransaction(){
        $this->proccessTransactionApplied();
        $this->proccessTransactionExpired();
    }

    public function updatePointsUsed($customer, $spentPoints)
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $transactions  = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getCustomerId())
            ->addFieldToFilter('is_expired', [
                ['eq' => 0],
                ['null' => true]
            ])
            ->addFieldToFilter('expires_at', ['gteq' => $currentTime]);
            $transactions->getSelect()->where('amount > amount_used OR amount_used IS NULL');
            $transactions->getSelect()->order('expires_at ASC');

            //get total amount of all transactions has status is spending close
            $spending_close_transactions  = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getCustomerId())
            ->addFieldToFilter('status', Transaction::SPENDING_CLOSED);
            $spending_close_transactions->getSelect()->where('amount > amount_used OR amount_used IS NULL');
            $spending_close_transactions->getSelect()->columns(['amount_total' => new \Zend_Db_Expr('SUM(amount)')])->group('customer_id');

            $spending_close_point = 0;
            if($spending_close_transactions->getSize()) {
                $spending_close_point = (float)$spending_close_transactions->getFirstItem()->getAmountTotal();
                //$this->rewardsLogger->addError(__('spending_close_point = ') . $spending_close_point);
            }

            foreach ($transactions as $transaction) {
                $amount     = $transaction->getAmount();
                $amountUsed = $transaction->getAmountUsed();
                if (($amount - $amountUsed) < $spentPoints) {
                    $amount = $amount - $spending_close_point;
                    $amount = ($amount > 0)?$amount:0;
                    $transaction->setAmountUsed($amount);
                    $spentPoints -= ($amount - $amountUsed);
                } else {
                    $amount = ($amountUsed + $spentPoints) - $spending_close_point;
                    $amount = ($amount > 0)?$amount:0;
                    $transaction->setAmountUsed($amount);
                    $spentPoints = 0;
                }
                //$this->rewardsLogger->addError(__('updated_amount = ') . $amount);
                $transaction->save();

                if (!$spentPoints) {
                    break;
                }
            }


        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS4:' . $e->getMessage()));
        }
        return $this;
    }

    public function proccessTransactionExpired()
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $collection  = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('is_expired', 0)
            ->addFieldToFilter('expires_at', ['lt' => $currentTime]);
            foreach ($collection as $transaction) {
                /**
                 * Send Email Notification
                 */
                $this->rewardsMail->sendNotificationBalanceExpiredEmail($transaction);

                /**
                 * Transaction
                 */
                $transaction->setData('is_expired', 1)
                ->setData('status', Transaction::STATE_EXPIRED)
                ->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS3:' . $e->getMessage()));
        }
        return $this;
    }

    public function proccessTransactionApplied()
    {
        try {
            $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
            $collection  = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('is_applied', 0)
            ->addFieldToFilter('apply_at', ['lt' => $currentTime]);
            foreach ($collection as $transaction) {
                /**
                 * Send Email Notification
                 */
                $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction);

                // Calculatation Avaiable Points for Customer
                $customer = $this->rewardPointCustomerFactory->create()->addFieldToFilter('customer_id', $transaction->getCustomerId())->getFirstItem();
                $totalPoints     = $customer->getTotalPoints() + $transaction->getAmount();
                $availablePoints = $customer->getAvailablePoints() + $transaction->getAmount();
                $customer->setAvailablePoints($availablePoints);
                $customer->setTotalPoints($totalPoints);
                $customer->save();

                /**
                 * Transaction
                 */
                $transaction->setData('is_applied', 1)
                ->setData('status', Transaction::STATE_COMPLETE)
                ->save();

            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS4:' . $e->getMessage()));
        }
        return $this;
    }


    public function changePointsBalance($params)
    {
        try{
            // Check exit customer & code
            if (isset($params['code']) && $params['code'] && (!$this->getTransaction('customer_id', $params['customer_id']))) {
                return false;
            }
            $collection = $this->transactionCollectionFactory->create();
            if (isset($params['transaction_id'])) {
                $collection->addFieldToFilter('transaction_id', $params['transaction_id']);
            }
            if (isset($params['customer_id'])) {
                $collection->addFieldToFilter('customer_id', $params['customer_id']);
            }
            if (isset($params['code'])) {
                $collection->addFieldToFilter('code', $params['code']);
            }
            $transaction = $collection->getFirstItem();

            if(!$transaction->getId()) {
                $transaction = $this->transactionFactory->create()->setCustomerId($params['customer_id']);
            }
            foreach ($params as $k => $v) {
                $transaction->setData($k, $v);
            }
            $transaction->save();
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }
}
