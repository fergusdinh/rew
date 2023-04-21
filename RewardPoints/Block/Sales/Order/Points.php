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

namespace Lof\RewardPoints\Block\Sales\Order;

class Points extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Lof\RewardPoints\Helper\Data                    $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase                $rewardsPurchase
     * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rewardsData     = $rewardsData;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->pricingHelper   = $pricingHelper;
    }


    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        /** @var $parent \Magento\Sales\Block\Adminhtml\Order\Invoice\Totals */
		$parent   = $this->getParentBlock();
		$order    = $parent->getOrder();
		$purchase = $this->rewardsPurchase->getByOrder($order);

		$eanringPoints  = $purchase->getEarnPoints();
		$spendingPoints = $purchase->getSpendPoints();
		$discount       = $purchase->getDiscount();

		if ($eanringPoints) {
	        $earnRow = new \Magento\Framework\DataObject(
		        [
					'code'        => 'earning_points',
					'field'       => 'earning_points',
					'strong'      => false,
					'is_formated' => true,
					'value'       => $this->rewardsData->formatPoints($eanringPoints),
					'label'       => __('You Earned')
		        ]
			);
			$this->getParentBlock()->addTotalBefore($earnRow, ['tax']);
		}

		if ($spendingPoints) {
			$earnRow = new \Magento\Framework\DataObject(
		        [
					'code'        => 'spending_points',
					'field'       => 'spending_points',
					'strong'      => false,
					'is_formated' => true,
					'value'       => $this->rewardsData->formatPoints($spendingPoints),
					'label'       => __('You Spent')
		        ]
			);
			$this->getParentBlock()->addTotalBefore($earnRow, ['tax']);

			$usepointsRow = new \Magento\Framework\DataObject(
		        [
					'code'        => 'using_points',
					'field'       => 'using_points',
					'strong'      => false,
					'is_formated' => true,
					'value'       => '-' . $this->pricingHelper->currency($discount),
					'label'       => __('Use %1', $this->rewardsData->formatPoints($spendingPoints))
		        ]
			);
			$this->getParentBlock()->addTotalBefore($usepointsRow, ['tax']);
		}

        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
