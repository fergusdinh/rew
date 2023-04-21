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

namespace Lof\RewardPoints\Observer;

class OrderCheckoutSuccess extends Order
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $orderId           = $this->checkoutSession->getLastOrderId();
            $order             = $this->orderFactory->create()->load($orderId);
            $purchase          = $this->rewardsPurchase->getByOrder($order, true);
            $totalEarnedPoitns = (float) $purchase->getEarnPoints();
            $totalSpentPoitns  = (float) $purchase->getSpendPoints();

            $this->rewardsBalanceOrder->proccessOrder($order, '', $purchase);

            if ($totalEarnedPoitns > 0 && $totalSpentPoitns > 0) {
                $this->messageManager->addSuccessMessage(__('You earned %1 and spent %2 for the order #%3.', $this->rewardsData->formatPoints($totalEarnedPoitns), $this->rewardsData->formatPoints($totalSpentPoitns), $order->getIncrementId()));
            } elseif ($totalEarnedPoitns > 0 && $totalSpentPoitns <= 0) {
                $this->messageManager->addSuccessMessage(__('You earned %1 for the order #%2.', $this->rewardsData->formatPoints($totalEarnedPoitns), $order->getIncrementId()));

                if (!$purchase->getCustomerId()) {
                    $transaction = $this->rewardsBalance->getByOrder($order);
                    $this->messageManager->addSuccessMessage(__('Enter your reward code <strong>"%1"</strong> to get %2 in your account dashboard.', $transaction->getCode() , $this->rewardsData->formatPoints($totalEarnedPoitns)));
                    $this->rewardsMail->setRewardCodeEmail($transaction, $order);
                }
            } elseif($totalSpentPoitns > 0) {
                $this->messageManager->addSuccessMessage(__('You spent %1 for the order #%2.', $this->rewardsData->formatPoints($totalSpentPoitns), $order->getIncrementId()));
            }

            if($totalEarnedPoitns > 0 && !in_array($order->getStatus(), $this->rewardsConfig->getGeneralEarnInStatuses())) {
                $this->messageManager->addSuccessMessage(__('Earned points will be enrolled to your account after we finish processing your order.'));
            }
        }
    }
}
