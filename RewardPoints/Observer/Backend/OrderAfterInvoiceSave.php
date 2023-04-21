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

class OrderAfterInvoiceSave extends \Lof\RewardPoints\Observer\Order
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
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();

            if ($invoice->getState() == \Magento\Sales\Model\Order\Invoice::STATE_CANCELED) {
                return;
            }

            if ($order && $this->getConfig()->isEarnAfterInvoice()) {
                $this->rewardsBalanceOrder->earnOrderPoints($order);
            }
            $this->rewardsBalanceOrder->spendOrderPoints($order);
        }
    }
}
