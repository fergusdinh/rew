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

namespace Lof\RewardPoints\Helper\Balance;

use \Lof\RewardPoints\Model\Transaction;
use \Lof\RewardPoints\Model\Email;
use \Lof\RewardPoints\Model\Config as RewardsConfig;
use \Lof\RewardPoints\Model\Purchase as RewardsPurchase;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Sales\Model\OrderFactory          $orderFactory
     * @param \Lof\RewardPoints\Helper\Purchase          $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Balance           $rewardsBalance
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData
     * @param \Lof\RewardPoints\Helper\Customer          $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Mail              $rewardsMail
     * @param \Lof\RewardPoints\Logger\Logger            $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config             $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        parent::__construct($context);
        $this->orderFactory    = $orderFactory;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsBalance  = $rewardsBalance;
        $this->rewardsData     = $rewardsData;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsMail     = $rewardsMail;
        $this->rewardsLogger   = $rewardsLogger;
        $this->rewardsConfig   = $rewardsConfig;
    }

    public function proccessOrder($order, $adminId = '', $purchase = null)
    {
        try {
            if (is_numeric($order)) {
                $order = $this->orderFactory->create()->load($order);
            }
            $orderId           = $order->getId();
            if(!$purchase) {
                $purchase          = $this->rewardsPurchase->getByOrder($order, true);
            }
            $totalEarnedPoitns = (float) $purchase->getEarnPoints();

            $totalSpentPoitns  = (float) $purchase->getSpendPoints();
            if ($totalEarnedPoitns > 0) {
                $code = strtoupper($this->rewardsData->generateCouponCode('REWARD-', 2, 3, 3));

                $status = Transaction::STATE_PROCESSING;
                if (in_array($order->getStatus(), $this->rewardsConfig->getGeneralEarnInStatuses())) {
                    $status = Transaction::STATE_COMPLETE;
                }

                $params = [
                    'customer_id'   => $order->getCustomerId(),
                    'amount'        => $totalEarnedPoitns,
                    'amount_used'   => 0,
                    'title'         => __('Earned %1 for the order #%2', $this->rewardsData->formatPoints($totalEarnedPoitns), $order->getIncrementId()),
                    'code'          => $code,
                    'action'        => Transaction::EARNING_ORDER,
                    'status'        => $status,
                    'params'        => serialize($purchase->getParams()),
                    'expires_at'    => '',
                    'store_id'      => (float) $order->getStore()->getId(),
                    'order_id'      => $orderId,
                    'admin_user_id' => $adminId
                ];

                $this->rewardsBalance->changePointsBalance($params);
            }

            $rewardsCustomer = $this->rewardsCustomer->getCustomer($order->getCustomerId());
            if ($totalSpentPoitns > 0) {

                if ($totalSpentPoitns>$rewardsCustomer->getTotalPoints()) {
                     $totalSpentPoitns = $rewardsCustomer->getTotalPoints();
                }
                $status = Transaction::STATE_PROCESSING;
                if (in_array($order->getStatus(), $this->rewardsConfig->getGeneralSpendInStatuses())) {
                    $status = Transaction::STATE_COMPLETE;
                }
                $code = Transaction::SPENDING_ORDER;
                $code = strtoupper($this->rewardsData->generateCouponCode('REWARD-' . $code . '-', 2, 3, 3));
                $params = [
                    'customer_id'   => $order->getCustomerId(),
                    'amount'        => -$totalSpentPoitns,
                    'amount_used'   => 0,
                    'title'         => __('Spent %1 for the order #%2', $this->rewardsData->formatPoints($totalSpentPoitns),$order->getIncrementId()),
                    'code'          => $code,
                    'action'        => Transaction::SPENDING_ORDER,
                    'status'        => $status,
                    'params'        => serialize($purchase->getParams()),
                    'store_id'      => (int) $order->getStore()->getId(),
                    'order_id'      => $orderId,
                    'admin_user_id' => $adminId
                ];
                $this->rewardsBalance->changePointsBalance($params);

                // Update Customer Points
                $availablePoints = $rewardsCustomer->getAvailablePoints();
                $params[RewardsPurchase::DISCOUNT][RewardsConfig::SPENDING_RATE] = 0;
                $params[RewardsPurchase::DISCOUNT][RewardsConfig::SPENDING_PRODUCT_POINTS] = 0;
                $rewardsCustomer->setTotalPoints($availablePoints);
                $rewardsCustomer->save();

                /**
                 * Refresh points used
                 */
                $this->rewardsBalance->updatePointsUsed($rewardsCustomer, $totalSpentPoitns);

                /**
                 * Send Email
                 */
                $transaction = $this->rewardsBalance->getByOrder($order);
                $title = __('You spent %1 for the order #%2', $this->rewardsData->formatPoints($totalSpentPoitns), $order->getIncrementId());
                $transaction->setTitle($title);
                $this->rewardsMail->setOrder($order)->sendNotificationBalanceUpdateEmail($transaction, '');
            }


        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $this;
    }

    public function earnOrderPoints($order, $withAdmin = true)
    {
        try {
            $transaction = $this->rewardsBalance->getByOrder($order);
            if ($transaction->getId() && $transaction->getStatus() != Transaction::STATE_COMPLETE) {

                /**
                 * Change transaction status to Complete
                 */
                $transaction->setAction(Transaction::EARNING_ORDER)->setStatus(Transaction::STATE_COMPLETE)->save();

                /**
                 * Send a Notifications Email
                 */
                if ($amount = $transaction->getAmount()) {
                    $amount = $this->rewardsData->formatPoints($amount);
                    $params['title'] = __('You earned %1 for the order #%2', $amount, $order->getIncrementId());
                    $this->rewardsMail->setTrigger(Email::ACTION_EARN_POINTS)
                    ->setParams($params)
                    ->sendNotificationBalanceUpdateEmail($transaction,'');
                }

                /**
                 * Save Customer
                 */
                if (!$order->getCustomerIsGuest()) {
                    $customer = $this->rewardsCustomer->getCustomer($order->getCustomerId())->save();
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }

    public function cancelEarnedPoints($order, $status, $action, $points = '', $adminId = '')
    {
        try {
            $purchase    = $this->rewardsPurchase->getByOrder($order);
            $transaction = $this->rewardsBalance->getByOrder($order, Transaction::EARNING_ORDER);
            if (!$transaction->getId()) {
                return;
            }

            $earnPoints  = ($transaction->getAmount() - $transaction->getAmountUsed());
            if ($points && $earnPoints < $points) {
                $earnPoints = (float) $points;
            }
            $customerId = 0;

            if (!$order->getCustomerIsGuest()) {
                $customer = $transaction->getRewardsCustomer();
                $customerId  = $customer->getCustomerId();
            }

            if ($transaction->getId() && $earnPoints>0) {
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE) {
                        if ($customerId) {
                        $totalPoints = $customer->getTotalPoints();
                        if ($totalPoints < $earnPoints) {
                            $earnPoints = $totalPoints;
                        }
                    }
                    $earnPoints = -$earnPoints;
                }

                /**
                 * Save Transaction Points
                 */
                $orderId = $order->getId();
                $params = [
                    'customer_id'   => $customerId,
                    'amount'        => $earnPoints,
                    'amount_used'   => 0,
                    'title'         => __('Cancel %1 for the order #%2', $this->rewardsData->formatPoints($earnPoints), $order->getIncrementId()),
                    'code'          => $action . $orderId,
                    'action'        => $action,
                    'status'        => $status,
                    'params'        => serialize($purchase->getParams()),
                    'expires_at'    => '',
                    'store_id'      => $order->getStore()->getId(),
                    'order_id'      => $orderId,
                    'admin_user_id' => $adminId
                ];

                if ($transaction->getStatus() != Transaction::STATE_COMPLETE) {
                    $params['transaction_id'] = $transaction->getId();
                }

                $this->rewardsBalance->changePointsBalance($params);

                /**
                 * Send a Notifications Email
                 */
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    $params['title'] = __('Cancel %1 for the order #%2.', $this->rewardsData->formatPoints($earnPoints), $order->getIncrementId());
                    $params['amount'] = -$earnPoints;
                    $this->rewardsMail->setTrigger(Email::ACTION_CANCEL_EARNED_POINTS)
                    ->setParams($params)
                    ->sendNotificationBalanceUpdateEmail($transaction,'');
                }

                /**
                 * Save Customer Points
                 */
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    $customer->refreshPoints()->save();
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }

    public function spendOrderPoints($order, $withAdmin = true)
    {
        try {
            $transaction = $this->rewardsBalance->getByOrder($order, Transaction::SPENDING_ORDER);
            if ($transaction->getId() && $transaction->getStatus() == Transaction::STATE_PROCESSING) {
                /**
                 * Change transaction status to Complete
                 */
                $transaction->setAction(Transaction::SPENDING_ORDER)->setStatus(Transaction::STATE_COMPLETE)->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }

    public function cancelSpentPoints($order, $action, $status, $points = '', $adminId = '')
    {
        try {
            $customerId  = $order->getCustomerId();
            $customer    = $this->rewardsCustomer->getCustomer($customerId);
            $purchase    = $this->rewardsPurchase->getByOrder($order);
            $transaction = $this->rewardsBalance->getByOrder($order, Transaction::SPENDING_ORDER);
            $spentPoints = $purchase->getSpendPoints();
            if ($points && $points < $spentPoints) {
                $spentPoints = (float) $points;
            }
            if ($transaction->getId() && $spentPoints>0) {
                $orderId = $order->getId();

                /**
                 * Save Balance
                 */
                $params = [
                    'customer_id'   => $customerId,
                    'amount'        => $spentPoints,
                    'amount_used'   => 0,
                    'title'         => __('Cancel spent %1 for the order #%2', $this->rewardsData->formatPoints($spentPoints), $order->getIncrementId()),
                    'code'          => $action . $orderId,
                    'action'        => Transaction::SPENDING_CREDITMEMO,
                    'status'        => Transaction::STATE_COMPLETE,
                    'params'        => serialize($purchase->getParams()),
                    'expires_at'    => '',
                    'store_id'      => $order->getStore()->getId(),
                    'order_id'      => $orderId,
                    'admin_user_id' => $adminId
                ];

                $this->rewardsBalance->changePointsBalance($params);

                /**
                 * Send a Notifications Email
                 */
                $params['title'] = __('Cancel spent %1 for the order #%2.', $this->rewardsData->formatPoints($spentPoints), $order->getIncrementId());
                $params['amount'] = $spentPoints;
                $this->rewardsMail->setTrigger(Email::ACTION_CANCEL_SPENT_POINTS)
                    ->setParams($params)
                    ->sendNotificationBalanceUpdateEmail($transaction,'');

                /**
                 * Save Customer Points
                 */
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    $customer->refreshPoints()->save();
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $transaction;
    }
}
