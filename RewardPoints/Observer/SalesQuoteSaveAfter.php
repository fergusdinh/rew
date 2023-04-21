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

class SalesQuoteSaveAfter implements ObserverInterface
{
    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Lof\RewardPoints\Helper\Purchase      $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Balance\Earn  $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Model\Config         $rewardsConfig
     */
    public function __construct(
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->rewardsPurchase     = $rewardsPurchase;
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
        $quote = $observer->getQuote();
        if ($quote && $quote->getId() && $this->rewardsConfig->isEnable()) {
            $purchase = $this->rewardsPurchase->getPurchase($quote);
            $this->rewardsBalanceEarn->setQuote($quote)->setPurchase($purchase)->resetRatePoints();
            $this->rewardsBalanceSpend->setQuote($quote)->setPurchase($purchase)->resetRatePoints();
        }
    }
}
