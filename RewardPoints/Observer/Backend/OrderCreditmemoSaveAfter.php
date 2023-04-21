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

namespace Lof\RewardPoints\Observer\Backend;

use Lof\RewardPoints\Model\Transaction;

class OrderCreditmemoSaveAfter extends \Lof\RewardPoints\Observer\Order
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
        $params = $observer->getEvent()->getRequest()->getParams();
        if(!empty($params) && $this->rewardsConfig->isEnable()) {
            $orderId = $params['order_id'];
            $creditmemo = $this->creditmemo->getCollection()->addFieldToFilter('order_id', $orderId)->getFirstItem();
            if ($creditmemo) {
                $order  = $this->orderFactory->create()->load($orderId);
                if (isset($params['creditmemo']['earnedpoints'])) {
                    $transaction = $this->rewardsBalanceOrder->cancelEarnedPoints($order, Transaction::STATE_COMPLETE, Transaction::EARNING_CREDITMEMO, (float) $params['creditmemo']['earnedpoints']);
                }
                if (isset($params['creditmemo']['spentpoints'])) {
                    $transaction = $this->rewardsBalanceOrder->cancelSpentPoints($order, Transaction::SPENDING_CREDITMEMO, Transaction::STATE_COMPLETE, (float) $params['creditmemo']['spentpoints']);
                }
                $customerId = $order->getCustomerId();
                $customer   = $this->rewardsCustomer->getCustomer($customerId);
            }
        }
    }
}
