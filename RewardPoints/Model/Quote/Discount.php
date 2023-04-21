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

class Discount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Lof\RewardPoints\Model\Rule
     */
    protected $purchase;


    /**
     * @param \Lof\RewardPoints\Helper\Data $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Customer $rewardsCustomer
     * @param \Lof\RewardPoints\Logger\Logger $rewardsLogger
     */
    public function __construct(
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
        ) {
        $this->setCode('rewardsdiscount');
        $this->rewardsData         = $rewardsData;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->rewardsCustomer     = $rewardsCustomer;
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
            $purchase   = $this->getPurchase();
            if (!$purchase) {
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $this->setPurchase($purchase);
                $this->rewardsCustomer->refreshPurchaseAvailable($purchase->getId(), $quote->getCustomer()->getId());
            }
            $spentPoints = $purchase->getSpendPoints();
            $discount    = $purchase->getDiscount();
            if ($spentPoints && $discount) {
                $result = [
                    'code'        => $this->getCode(),
                    'title'       => __('Use %1', $this->rewardsData->formatPoints($spentPoints)),
                    'value'       => -$discount,
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
        return __('Rewards Discount');
    }
}
