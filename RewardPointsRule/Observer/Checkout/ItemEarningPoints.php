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

namespace Lof\RewardPointsRule\Observer\Checkout;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPoints\Model\Config;
use Lof\RewardPointsRule\Model\Config as RewardsRuleConfig;

class ItemEarningPoints implements ObserverInterface
{
	/**
	 * @var \Lof\RewardPoints\Logger\Logger
	 */
	protected $rewardsLogger;

    /**
     * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger
     */
	public function __construct(
		\Lof\RewardPoints\Logger\Logger $rewardsLogger
	) {
		$this->rewardsLogger = $rewardsLogger;
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
		try {
			$obj      = $observer->getObj();
			$points   = $obj->getPoints();
			$purchase = $observer->getPurchase();
			$item     =	$observer->getItem();
			$params   = $purchase->getParams();
			$points   = 0;
			if (isset($params[RewardsRuleConfig::EARNING_CATALOG_RULE]['rules'])) {
				$rules = $params[RewardsRuleConfig::EARNING_CATALOG_RULE]['rules'];
				foreach ($rules as $ruleId => $rule) {
					$rulePoints = 0;
					foreach ($rule['items'] as $sku => $_item) {
						if ($sku == strtolower($item->getSku())) {
							$points += $_item['points'];
						}
					}
				}
			}

			if (isset($params[RewardsRuleConfig::EARNING_CART_RULE]['rules'])) {
				$rules = $params[RewardsRuleConfig::EARNING_CART_RULE]['rules'];
				foreach ($rules as $ruleId => $rule) {
					$rulePoints = 0;
					foreach ($rule['items'] as $sku => $_item) {
						if ($sku == strtolower($item->getSku())) {
							$points += $_item['points'];
						}
					}
				}
			}
			$obj->setPoints($points);

		} catch (\Exception $e) {
			$this->rewardsLogger->addError($e->getMessage());
		}
	}
}