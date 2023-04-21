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

class Creditmemo extends \Magento\Sales\Model\Order\Total\AbstractTotal
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
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
	public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
	{
		$order = $creditmemo->getOrder();
		$purchase = $this->rewardsPurchase->getByOrder($order);
		if (($discount = $purchase->getDiscount()) && ($creditmemo->getGrandTotal() > $discount)) {
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $discount);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $discount);
		}
		return $this;
	}
}
