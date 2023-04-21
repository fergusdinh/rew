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

class OrderAfterShipmentSave extends \Lof\RewardPoints\Observer\Order
{
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if ($this->rewardsConfig->isEnable()) {
			$shipment = $observer->getEvent()->getShipment();
			$order    = $shipment->getOrder();
			$purchase = $this->rewardsPurchase->getByOrder($order);

			if ($order && $this->getConfig()->isEarnAfterShipment()) {
	            $this->rewardsBalanceOrder->earnOrderPoints($order);
			}
			$this->rewardsBalanceOrder->spendOrderPoints($order);
	    }
	}
}
