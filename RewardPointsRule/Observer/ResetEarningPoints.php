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

namespace Lof\RewardPointsRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPointsRule\Model\Config as RewardsRuleConfig;

class ResetEarningPoints implements ObserverInterface
{
	/**
	 * @var \Lof\RewardPointsRule\Helper\Balance\Earn
	 */
	protected $rewardsBalanceEarn;

	/**
	 * @var \Lof\RewardPointsRule\Model\Config
	 */
	protected $rewardsRuleConfig;

	/**
	 * @param \Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn 
	 * @param \Lof\RewardPointsRule\Model\Config        $rewardsRuleConfig  
	 */
	public function __construct(
		\Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn,
		\Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
	) {
		$this->rewardsBalanceEarn = $rewardsBalanceEarn;
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
			$obj    = $observer->getObj();
	        $params = $obj->getParams();
	        $quote	= $observer->getQuote();
	        $params[RewardsRuleConfig::EARNING_CATALOG_RULE] = $this->rewardsBalanceEarn->getCatalogRulePoints($quote);
	        $obj->setParams($params);
	    }
	}
}