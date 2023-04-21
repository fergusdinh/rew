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

namespace Lof\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductView implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @param \Magento\Framework\Registry            $coreRegistry
     * @param \Lof\RewardPoints\Helper\Balance\Earn  $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Model\Config         $rewardsConfig
     */
	public function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Model\Config $rewardsConfig
	) {
        $this->coreRegistry        = $coreRegistry;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsConfig       = $rewardsConfig;
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
        if ($this->rewardsConfig->isEnable()) {
        	$product = $this->coreRegistry->registry('product');
            if($product) {
            	$earningPoint = (float) $this->rewardsBalanceEarn->getProductPoints($product);
                $product->setEarningPoints($earningPoint);
                $spendingPoints = (float) $this->rewardsBalanceSpend->getProductSpendingPoints($product->getId(), true);
                if($spendingPoints) {
                	$product->setSpendingPoints($spendingPoints)->setIsProductView(true);
                    $product->setEarningPoints(0);
                } else if ($earningPoint) {
                	$product->setEarningPoints($earningPoint);
                }
                $this->coreRegistry->unregister('product');
                $this->coreRegistry->register('product', $product);
            }
        }
    }
}
