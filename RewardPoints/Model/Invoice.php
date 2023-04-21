<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
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

namespace Lof\RewardPoints\Model;

class Invoice extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

	/**
	 * @param \Lof\RewardPoints\Helper\Purchase $rewardsPurchase
	 */
	public function __construct(
		\Lof\RewardPoints\Helper\Purchase $rewardsPurchase
		){
		$this->rewardsPurchase = $rewardsPurchase;
	}

	/**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
	public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
	{
		$order = $invoice->getOrder();
		$purchase = $this->rewardsPurchase->getByOrder($order);
		if (($discount = $purchase->getDiscount()) && ($invoice->getGrandTotal() > $discount)) {
			$invoice->setGrandTotal($invoice->getGrandTotal() - $discount);
			$invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $discount);
		}
		return $this;
	}
}
