<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\RewardPoints\Block\Checkout\Cart;

use Lof\RewardPoints\Model\Config;

class Item extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

    /**
     * @param \Magento\Catalog\Block\Product\Context          $context
     * @param \Lof\RewardPoints\Helper\Purchase               $rewardsPurchase
     * @param array                                           $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->rewardsPurchase = $rewardsPurchase;

    }


    public function getSpendPoint() {
        $points   = 0;
        $item     = $this->getItem();
        $purchase = $this->rewardsPurchase->getCurrentPurchase();
        $params   = $purchase->getParams();
        if (isset($params[Config::SPENDING_PRODUCT_POINTS]['items'])) {
            $rules = $params[Config::SPENDING_PRODUCT_POINTS]['items'];
            foreach ($rules as $sku => $_item) {
                $rulePoints = 0;
                if ($sku == strtolower($item->getSku())) {
                    $points = $_item['points'];
                    break;
                }
            }
        }
        return $points;
    }

    public function getPoint()
    {
        $item     = $this->getItem();
        $purchase = $this->rewardsPurchase->getCurrentPurchase();
        $params   = $purchase->getParams();
        $points   = 0;

        $object = new \Magento\Framework\DataObject([
                'points'   => $points
        ]);
        $this->_eventManager->dispatch(
            'rewardpoints_item_earning_points',
            [
                'obj'      => $object,
                'purchase' => $purchase,
                'item'     => $item
            ]
        );
        $points = $object->getPoints();

        if (isset($params[Config::EARNING_RATE]['rules'])) {
            $rules  = $params[Config::EARNING_RATE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $sku => $_item) {
                    if ($sku == strtolower($item->getSku())) {
                        $points += $_item['points'];
                    }
                }
            }
        }

        if (isset($params[Config::EARNING_PRODUCT_POINTS]['rules']['items'])) {
            $earningProductPoints  = $params[Config::EARNING_PRODUCT_POINTS]['rules']['items'];
            foreach ($earningProductPoints as $sku => $_item) {
                if ($sku == strtolower($item->getSku())) {
                    $points = $_item['points'];
                }
            }
        }
        return $points;
    }

    public function getPointsSpending()
    {
        $data     = [];
        $purchase = $this->rewardsPurchase->getCurrentPurchase();
        $params   = $purchase->getParams();
        $item     = $this->getItem();
        if($params && isset($params[Config::SPENDING_PRODUCT_POINTS]['items'])) {
            $rules = $params[Config::SPENDING_PRODUCT_POINTS]['items'];
            foreach ($rules as $sku => $product) {
                if ($sku == strtolower($item->getSku())) {
                    $product['steps'] = $product['qty'];
                    $data[] = $product;
                }
            }
        }
        return $data;
    }

    public function getCancelPointsUrl($ruleId)
    {
        return $this->getUrl('rewardpoints/checkout/cancelpoints', [
                'item_id' => $this->getItem()->getItemId(),
                'sku'   => strtolower($this->getItem()->getSku()),
                'rule'  => $ruleId,
                'type'  => Config::SPENDING_PRODUCT_POINTS
            ]);
    }

}
