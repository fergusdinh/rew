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

class RefreshDiscount implements ObserverInterface
{
    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Lof\RewardPoints\Logger\Logger    $rewardsLogger     
     * @param \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig 
     */
    public function __construct(
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
    ) {
        $this->rewardsLogger     = $rewardsLogger;
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
    		$discount = 0;
    		$obj      = $observer->getObj();
    		$purchase = $observer->getPurchase();
    		$params   = $purchase->getParams();
    		if (isset($params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['discount'])) {
                $discount += $params[RewardsRuleConfig::SPENDING_CATALOG_RULE]['discount'];
            }
            if (isset($params[RewardsRuleConfig::SPENDING_CART_RULE]['discount'])) {
                $discount += $params[RewardsRuleConfig::SPENDING_CART_RULE]['discount'];
            }
            if (isset($params[Config::SPENDING_RATE]['discount']) && !isset($params[RewardsRuleConfig::SPENDING_CART_RULE]['discount'])) {
                $discount += $params[Config::SPENDING_RATE]['discount'];
            }

            if ($discount == 0) {
                $this->rewardsLogger->addError($purchase->getData('params'));
                $this->rewardsLogger->addError('EVENT');
            }

            $obj->setDiscount($discount);
        }
	}
}