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

use Magento\Framework\Event\ObserverInterface;
use \Lof\RewardPoints\Model\Transaction;

class SalesOrderReorder extends Order implements ObserverInterface
{
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if ($this->rewardsConfig->isEnable()) {
			$order = $this->coreRegistry->registry('current_order');
			$transaction = $this->rewardsBalanceOrder->cancelEarnedPoints($order, Transaction::STATE_CANCELED, Transaction::EARNING_CLOSED);
	        $transaction = $this->rewardsBalanceOrder->cancelSpentPoints($order, Transaction::STATE_CANCELED, Transaction::SPENDING_CLOSED);
	    }
	}
}
