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

namespace Lof\RewardPointsRule\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPointsRule\Model\Config;

class Rule implements ObserverInterface
{
    /**
     * @var \Lof\RewardPointsRule\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPointsRule\Model\Validator
     */
    protected $calculator;

    /**
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn 
     * @param \Lof\RewardPointsRule\Model\Validator     $validator          
     * @param \Lof\RewardPointsRule\Model\Config        $rewardsRuleConfig  
     */
    public function __construct(
      \Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn,
      \Lof\RewardPointsRule\Model\Validator $validator,
      \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
      ) {
      $this->rewardsBalanceEarn = $rewardsBalanceEarn;
      $this->calculator         = $validator;
      $this->rewardsRuleConfig  = $rewardsRuleConfig;
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
      $items                = $observer->getItems();
      $quote                = $observer->getQuote();
      $earningCartRules     = $spendingCartRules = [];
      $obj                  = $observer->getObj();
      $params               = $obj->getParams();
      $applySpendingRuleIds = [];
      foreach ($items as $item) {
        if ($item->getParentItem()) {
          continue;
        }
            // Earning Cart Rules
        $applyEarningRuleIds = $this->calculator->setRuleId('earning_rule')->processEarningCartRules($item);
        foreach ($applyEarningRuleIds as $k => $v) {
          $earningCartRules[$v][] = $item->getId();
        }
            // Spending Cart Rules
        $applySpendingRuleIds = $this->calculator->setRuleId('spending_rule')->processSpendingCartRules($item);
        foreach ($applySpendingRuleIds as $k => $v) {
          $params[Config::SPENDING_CART_RULE]['rules'][$v]['items'][strtolower($item->getSku())]['qty'] = $item->getQty();
        }

        if (!empty($applySpendingRuleIds) && isset($params[Config::SPENDING_CART_RULE]['rules'])) {
          foreach ($params[Config::SPENDING_CART_RULE]['rules'] as $ruleId => $rule) {
            if (!in_array($ruleId, $applySpendingRuleIds)) {
              unset($params[Config::SPENDING_CART_RULE]['rules'][$ruleId]);
            }
          }
        } else {
          if (isset($params[Config::SPENDING_CART_RULE]) && isset($params[Config::SPENDING_CART_RULE]['rules']) && is_array($params[Config::SPENDING_CART_RULE]['rules'])) {
            foreach ($params[Config::SPENDING_CART_RULE]['rules'] as $ruleId => $rule) {
              if (isset($rule['items']) && is_array($rule['items'])) {
                if (isset($rule['items'][strtolower($item->getSku())])) {
                  unset($params[Config::SPENDING_CART_RULE]['rules'][$ruleId]['items'][strtolower($item->getSku())]);
                }
                if (empty($params[Config::SPENDING_CART_RULE]['rules'][$ruleId]['items'])) {
                  unset($params[Config::SPENDING_CART_RULE]['rules'][$ruleId]);
                }
              }
            }
          }
        }
      }

      if (isset($params[Config::SPENDING_CART_RULE]['rules'])) {
        $spendingCartRules = (array)$params[Config::SPENDING_CART_RULE]['rules'];
        foreach ($spendingCartRules as $ruleId => $rule) {
          if(empty($rule['items'])) {
            $params[Config::SPENDING_CART_RULE]['rules'][$ruleId]['points'] = 0;
            $params[Config::SPENDING_CART_RULE]['rules'][$ruleId]['discount'] = 0;
          }
        }
      }
      $params[Config::EARNING_CATALOG_RULE] = $this->rewardsBalanceEarn->getCatalogRulePoints($quote);
      $params[Config::EARNING_CART_RULE]    = $this->rewardsBalanceEarn->getCartRulePoints($quote, $earningCartRules);
      $obj->setParams($params);
    }
  }
}