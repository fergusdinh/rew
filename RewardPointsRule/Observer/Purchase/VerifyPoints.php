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

class VerifyPoints implements ObserverInterface
{
    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Lof\RewardPointsRule\Model\Config     $rewardsRuleConfig  
     */
	public function __construct(
		\Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
	) {
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsRuleConfig   = $rewardsRuleConfig;
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
    		$obj    = $observer->getObj();
    		$params = $obj->getParams();
    		$quote  = $observer->getQuote();
    		$limitedSubtotal = $this->rewardsBalanceSpend->getLimitedSubtotal($quote);

    		// Spending Cart Rules
            if (isset($params[RewardsRuleConfig::SPENDING_CART_RULE]['rules'])) {
                $spendingProductPoints = $params[RewardsRuleConfig::SPENDING_CART_RULE]['rules'];
                foreach ($spendingProductPoints as $ruleId => $rule) {
                    if (isset($rule['discount']) && $rule['discount'] > $limitedSubtotal) {
                        $spendingProductPoints[$ruleId]['discount'] = ($limitedSubtotal / $rule['stepdiscount']) * $rule['stepdiscount'];
                        $spendingProductPoints[$ruleId]['points'] = $spendingProductPoints[$ruleId]['discount'] * $spendingProductPoints[$ruleId]['steps'];
                    }
                }
                $params[RewardsRuleConfig::SPENDING_CART_RULE]['rules'] = $spendingProductPoints;
            }

            // Spending Catalog Rules
            if (isset($params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules'])) {
                if ($params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['discount'] > $limitedSubtotal) {
                    $priceAvaialable = $limitedSubtotal;
                    $rules = [];
                    $spendingCatalogRules = $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules'];
                    $newRules = [];
                    foreach ($spendingCatalogRules as $ruleId => $rule) {
                        foreach ($rule['items'] as $sku => $item) {
                            if (($priceAvaialable / $item['discount']) > 1) {
                                $item['steps'] = (int) ($priceAvaialable / $item['discount']);
                                $newRules[$ruleId]['items'][$sku] = $item;
                                $priceAvaialable -= ($item['steps'] * $item['discount']);
                            }
                        }
                    }
                    $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['rules'] = $newRules;
                }
            }
            $obj->setParams($params);
        }
	}
}