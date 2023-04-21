<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
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

namespace Lof\RewardPoints\Model\Quote;

use Lof\RewardPoints\Model\Config;

class SpendPoints extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Discount calculation object
     *
     * @var \Magento\SalesRule\Model\Validator
     */
    protected $calculator;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

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
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * @var \Lof\RewardPoints\Model\Purchase
     */
    protected $purchase;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator         $validator
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData
     * @param \Lof\RewardPoints\Helper\Customer          $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Purchase          $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Balance\Earn      $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend     $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Logger\Logger            $rewardsLogger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
    	$this->setCode('spendpoints');
        $this->storeManager        = $storeManager;
        $this->calculator          = $validator;
        $this->rewardsData         = $rewardsData;
        $this->rewardsCustomer     = $rewardsCustomer;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsLogger       = $rewardsLogger;
    }

    public function getPurchase()
    {
        $purchase = $this->purchase;
        return $purchase;
    }

    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    /**
     * Add shipping totals information to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($quote->getId()) {
            $result = [];
            $purchase = $this->getPurchase();
            if (!$purchase) {
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $this->setPurchase($purchase);
                $this->rewardsCustomer->refreshPurchaseAvailable($purchase->getId(), $quote->getCustomer()->getId());
            }
            $result = [];
            $spentPoints = $purchase->getSpendPoints();
            if ($spentPoints) {
                $result = [
                    'code'        => $this->getCode(),
                    'title'       => __('Spend %1', $this->rewardsData->getUnit($spentPoints)),
                    'value'       => $this->rewardsData->formatPoints($spentPoints),
                    'is_formated' => true,
                    'strong'      => true
                ];
            }
            return $result;
        }
    }


    /**
     * Get Shipping label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Spending Points...');
    }
}
