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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Observer\Purchase;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPoints\Model\Config;
use Lof\RewardPointsRule\Model\Config as RewardsRuleConfig;

class RefreshPoints implements ObserverInterface
{
    /**
     * @var \Lof\RewardPointsRule\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Lof\RewardPointsRule\Helper\Data  $rewardsData       
     * @param \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig 
     */
	public function __construct(
		\Lof\RewardPointsRule\Helper\Data $rewardsData,
        \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
	) {
        $this->rewardsData       = $rewardsData;
        $this->rewardsRuleConfig = $rewardsRuleConfig;
	}

	/**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        if ($this->rewardsRuleConfig->isEnable()) {
    		$purchase = $observer->getPurchase();
    		$this->refreshEarningRulePoints($purchase);
            $this->refreshEarningCartRulePoints($purchase);
            $this->refreshSpendingCatalogRules($purchase);
            $this->refreshSpendingCartRules($purchase);
            $this->refreshSpendingRates($purchase);
        }
	}

    public function refreshSpendingRates($purchase)
    {
        $params = $purchase->getParams();
        $points     = 0;
        $discount   = 0;
        if (isset($params[RewardsRuleConfig::SPENDING_RATE]['rules'])) {
            $rules = $params[RewardsRuleConfig::SPENDING_RATE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                if (isset($rule['status']) && $rule['status']) {
                    $points += (float) $rule['points'];
                    $discount += $rule['discount'];
                }
            }
            $params[RewardsRuleConfig::SPENDING_RATE]['rules']    = $rules;
            $params[RewardsRuleConfig::SPENDING_RATE]['discount'] = $discount;
            $purchase->setParams($params);
            $purchase->setSpendRatePoints($points);
            $purchase->refreshSpendPoints();

        }
        return $this;
    }

	public function refreshSpendingCartRules($purchase)
    {
        $params = $purchase->getParams();
        if (isset($params[RewardsRuleConfig::SPENDING_CART_RULE]['rules'])) {
            $rules      = $params[RewardsRuleConfig::SPENDING_CART_RULE]['rules'];
            $points     = 0;
            $discount   = 0;
            foreach ($rules as $ruleId => $rule) {
                if (isset($rule['status']) && $rule['status']) {
                    $points += (int) $rule['points'];
                    $discount += $rule['discount'];
                }
            }
            $params[RewardsRuleConfig::SPENDING_CART_RULE]['rules']    = $rules;
            $params[RewardsRuleConfig::SPENDING_CART_RULE]['discount'] = $discount; 
            $purchase->setParams($params);
            $purchase->setSpendCartPoints($points);
            $purchase->refreshSpendPoints();
        }
        return $this;
    }

	public function refreshSpendingCatalogRules($purchase)
    {
        $params = $purchase->getParams();
        if(!($quote = $purchase->getQuote())) {
            $quote = $this->rewardsData->getQuote();
        }
        $collection    = $quote->getAllVisibleItems();
        $totalDiscount = 0;
        if(isset($params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules'])) {
            // Compare with cart items, verify discount qty
            $tmp = [];
            foreach ($collection as $item) {
                $tmp[strtolower($item->getSku())] = $item->getQty();
            }
            $rules = [];

            foreach ($params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules'] as $ruleId => $rule) {
                foreach ($rule['items'] as $sku => $_item) {
                    if (isset($tmp[$sku])) {
                        if($_item['qty'] < $tmp[$sku]) {
                            $tmp[$sku] = $tmp[$sku] - $_item['qty'];
                        } else {
                            $_item['qty'] = $tmp[$sku];
                            unset($tmp[$sku]);
                        }
                        $rules[$ruleId]['items'][$sku] = $_item;
                    }
                }
            }
            $totalPoints   = 0;
            foreach ($rules as $ruleId => $rule) {
                $ruleSpendPoints = 0;
                $ruleDiscount    = 0;
                if (empty($rule['items'])) {
                    unset($rules[$ruleId]);
                    continue;
                }
                foreach ($rule['items'] as $sku => $product) {
                    $ruleSpendPoints += ($product['points'] * $product['steps']);
                    $ruleDiscount += ($product['discount'] * $product['steps']);
                }
                $rules[$ruleId]['points'] = $ruleSpendPoints;
                $rules[$ruleId]['discount'] = $ruleDiscount;
                $totalPoints += $ruleSpendPoints;
                $totalDiscount += $ruleDiscount;
            }
            $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules']    = $rules;
            $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['points']   = $totalPoints;
            $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['discount'] = $totalDiscount;
            $purchase->setData('spend_catalog_points', $totalPoints);
        }
        $purchase->setParams($params);
        $purchase->refreshSpendPoints();
        return $purchase;
    }

	public function refreshEarningCartRulePoints($purchase)
    {
        $params  = $purchase->getParams();
        $totalPoints = 0;
        if (isset($params[RewardsRuleConfig::EARNING_CART_RULE]['rules'])) {
            $rules = $params[RewardsRuleConfig::EARNING_CART_RULE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $productId => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['items'][$productId])) {
                        unset($rules[$ruleId]['items'][$productId]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
            }
            $params[RewardsRuleConfig::EARNING_CART_RULE]['rules']  = $rules;
            $params[RewardsRuleConfig::EARNING_CART_RULE]['points'] = $totalPoints;
        }

        $purchase->setData('earn_cart_points', $totalPoints);
        $purchase->setParams($params);
        $purchase->refreshEarnPoints();
        return $purchase;
    }

	public function refreshEarningRulePoints($purchase)
    {
        $params  = $purchase->getParams();
        $totalPoints = 0;
        if (isset($params[RewardsRuleConfig::EARNING_CATALOG_RULE]['rules'])) {
            $rules = $params[RewardsRuleConfig::EARNING_CATALOG_RULE]['rules'];
            $totalPoints = 0;
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $sku => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['items'][$sku])) {
                        unset($rules[$ruleId]['items'][$sku]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
            }
            $params[RewardsRuleConfig::EARNING_CATALOG_RULE]['rules'] = $rules;
            $params[RewardsRuleConfig::EARNING_CATALOG_RULE]['points'] = $totalPoints;
            if (isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && is_array($params[Config::EARNING_PRODUCT_POINTS]['items'])) {
                $points = 0;
                foreach ($params[Config::EARNING_PRODUCT_POINTS]['items'] as $k => $product) {
                    $totalPoints += ($product['qty'] * $product['points']);
                    $points += ($product['qty'] * $product['points']);
                }
                $params[Config::EARNING_PRODUCT_POINTS]['points'] = $points;
            }
        }

        // Duplicate earning rates, change  $params[Config::EARNING_CART_RULE] => $params[Config::EARNING_RATE]
        if (isset($params[Config::EARNING_RATE]['rules'])) {
            $rateTotalPoints = 0;
            $rules = $params[Config::EARNING_RATE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $productId => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['items'][$productId])) {
                        unset($rules[$ruleId]['items'][$productId]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
                $rateTotalPoints += $rules[$ruleId]['points'];
            }
            $params[Config::EARNING_RATE]['rules'] = $rules;
            $params[Config::EARNING_RATE]['points'] = $rateTotalPoints;
        }

        $purchase->setData('earn_catalog_points', $totalPoints);
        $purchase->refreshEarnPoints();
        $purchase->setParams($params);
        return $this;
    }
}