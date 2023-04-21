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

namespace Lof\RewardPoints\Block\Adminhtml\Sales\Order;

use \Magento\Sales\Model\Order;
use Lof\RewardPoints\Model\Transaction;

class Totals extends \Magento\Sales\Block\Adminhtml\Totals
{
	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

	/**
	 * @var \Magento\Framework\Pricing\Helper\Data
	 */
	protected $pricingHelper;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Framework\Registry                      $registry
	 * @param \Magento\Sales\Helper\Admin                      $adminHelper
	 * @param \Lof\RewardPoints\Helper\Purchase                $rewardsPurchase
	 * @param \Lof\RewardPoints\Helper\Data                    $rewardsData
	 * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
	 * @param array                                            $data
	 */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper);
		$this->rewardsPurchase = $rewardsPurchase;
		$this->rewardsData     = $rewardsData;
		$this->rewardsBalance  = $rewardsBalance;
		$this->pricingHelper   = $pricingHelper;
    }

    /**
     * Initialize totals object.
     *
     * @return $this
     */
    protected function initTotals()
    {
        parent::_initTotals();
		$order       = $this->getOrder();
		$purchase    = $this->rewardsPurchase->getByOrder($order);
		$parent      = $this->getParentBlock();

		if ($purchase && $purchase->getId()) {
			$action = Transaction::EARNING_ORDER;
			if ($order->getStatus() == Order::STATE_CLOSED) {
				$action = Transaction::EARNING_CLOSED;
			}
			if ($order->getStatus() == Order::STATE_CANCELED) {
				$action = Transaction::EARNING_CANCELED;
			}
			$transaction = $this->rewardsBalance->getByOrder($order, $action);


			if ($earnPoints = $transaction->getAmount()){
				$value = $earnPoints;
				if($this->getData('is_creditmemo')) {
		    		$value = '<input name="creditmemo[earnedpoints]" class="admin__control-text validate-length maximum-length-' . $earnPoints . ' validate-digits" type="text" value="' . $earnPoints . '" />';
		    	} else {
		    		$value = $this->rewardsData->formatPoints($value);
		    	}



		        $earnRow = new \Magento\Framework\DataObject(
		            [
						'code'        => 'earn',
						'field'       => 'earn',
						'strong'      => false,
						'is_formated' => true,
						'value'       => $value,
						'base_value'  => $value,
						'label'       => __('Earned %1', $this->rewardsData->getUnit($earnPoints)),
		            ]
		        );
		        $parent->addTotal($earnRow, ['discount']);
		    }

		    $action = Transaction::SPENDING_ORDER;
			if ($order->getStatus() == Order::STATE_CLOSED) {
				$action = Transaction::SPENDING_CLOSED;
			}
			if ($order->getStatus() == Order::STATE_CANCELED) {
				$action = Transaction::SPENDING_CANCELED;
			}
			$transaction = $this->rewardsBalance->getByOrder($order, $action);
		    if ($spendPoints = $transaction->getAmount()){

		    	$value = $spendPoints;
		    	if($this->getData('is_creditmemo')) {
		    		$value = '<input name="creditmemo[spentpoints]" class="admin__control-text maximum-length-' . $spendPoints . ' validate-digits" type="text" value="' . $spendPoints . '" />';
		    	} else {
		    		$value = $this->rewardsData->formatPoints($value);
		    	}
		        $spendRow = new \Magento\Framework\DataObject(
		            [
						'code'        => 'spend',
						'field'       => 'spend',
						'strong'      => false,
						'is_formated' => true,
						'value'       => $value,
						'base_value'  => $value,
						'label'       => __('Spent %1', $this->rewardsData->getUnit($spendPoints)),
						'base_value'  => 100
		            ]
		        );

		        $value = $spendPoints;
		        $parent->addTotal($spendRow, ['discount']);
				$discount = $purchase->getDiscount();
		        $usepointsRow = new \Magento\Framework\DataObject(
			        [
						'code'        => 'using_points',
						'field'       => 'using_points',
						'strong'      => false,
						'is_formated' => true,
						'value'       => '-' . $this->pricingHelper->currency($purchase->getDiscount()),
						'base_value'  => $purchase->getDiscount(),
						'label'       => __('Use %1', $this->rewardsData->formatPoints($spendPoints))
			        ]
				);
				$parent->addTotal($usepointsRow, ['discount']);
		    }
	    }
        return $this;
    }

}
