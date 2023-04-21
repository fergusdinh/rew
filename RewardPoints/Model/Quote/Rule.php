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

class Rule extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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

    protected $purchase;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\Validator         $validator
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase          $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Balance\Earn      $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend     $rewardsBalanceSpend
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\Validator $validator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
    	$this->setCode('earnedpoints');
        $this->storeManager        = $storeManager;
        $this->calculator          = $validator;
        $this->_eventManager       = $eventManager;
        $this->rewardsData         = $rewardsData;
        $this->rewardsPurchase     = $rewardsPurchase;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsConfig       = $rewardsConfig;
        $this->rewardsCustomer     = $rewardsCustomer;
        $this->rewardsLogger       = $rewardsLogger;
        $this->taxData                   = $taxData;
        $this->currencyFactory = $currencyFactory;
    }
    /**
     * @return \Lof\RewardPoints\Model\Config
     */
    public function getConfig()
    {
        return $this->rewardsConfig;
    }
    /**
     * Check if product prices inputed include tax
     *
     * @return bool
     */
    public function isIncludeTax($quote)
    {
        if ($this->getConfig()->isEarnPointsFromTax()) {
            //$priceIncludesTax = $this->taxData->priceIncludesTax($quote->getStore());
            //return $priceIncludesTax;
            return true;
        }
        return false;
    }
    /**
     * Collect address discount amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
    	\Magento\Quote\Model\Quote $quote,
    	\Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
    	\Magento\Quote\Model\Quote\Address\Total $total
    	) {
    	parent::collect($quote, $shippingAssignment, $total);
        $store   = $this->storeManager->getStore($quote->getStoreId());
        $address = $shippingAssignment->getShipping()->getAddress();
        $this->calculator->reset($address);
        $items   = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }
        $this->calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), '');
        $items     = $this->calculator->sortItemsByPriority($items, $address);
        $purchase  = $this->rewardsPurchase->getPurchase($quote);
        $this->setPurchase($purchase);
        $params    = $purchase->getParams();
        $cartItems = [];
        $subtotal  = 0;
        $priceIncludesTax = $this->isIncludeTax($quote);
        // Cart Rules
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $cartItems[strtolower($item->getSku())] = $item->getQty();
            //$subtotal += $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
            if($priceIncludesTax && $item->getBasePriceInclTax()){
                $subtotal += $item->getBasePriceInclTax() * $item->getQty() - $item->getBaseDiscountAmount();
            }else {
                $subtotal += $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
            }
        }
        if($this->getConfig()->isSpendPointsFromShipping()){
            $address = $quote->getShippingAddress();
            $address->collectShippingRates();
            $shippingAmount = 0;
            foreach ($address->getAllShippingRates() as $rate) {
                $shippingAmount += $rate->getPrice();
            }
            $subtotal += $shippingAmount;
        }
        //$subtotal = ceil($subtotal);
        try {
            if ($quote && $quote->getId()) {
                //$subtotal = $this->rewardsData->convertPrice($subtotal, $baseCurrencyCode, $currencyCode);
                $quoteId = $quote->getId();
                $this->rewardsBalanceEarn->setQuote($quote)->setPurchase($purchase);
                $this->rewardsBalanceSpend->setQuote($quote)->setPurchase($purchase);

                $object = new \Magento\Framework\DataObject(['params' => $params]);
                $this->_eventManager->dispatch(
                    'rewardpoints_quote_rule',
                    [
                        'obj'   => $object,
                        'items' => $items,
                        'quote' => $quote
                    ]
                );
                $params = $object->getParams();
                $params[Config::EARNING_RATE]['rules']           = $this->rewardsBalanceEarn->getCatalogRatePoints();
                $params[Config::EARNING_PRODUCT_POINTS]['rules'] = $this->rewardsBalanceEarn->getProductEarningPointsArr();
                $params[Config::SPENDING_RATE]['rules']          = $this->rewardsBalanceSpend->getSpendingRatePoints();

                $purchase->setQuote($quote);
                $purchase->setQuoteId($quote->getId());
                $purchase->setSubtotal($subtotal);
                $purchase->setParams($params);
                $purchase->refreshPoints();
                $purchase->save();

                // Apply Discount
                $exitDiscount = $quote->getRewardsDiscount();
                $exitBaseDiscount = $quote->getBaseRewardsDiscount();
                $base_discount     = $purchase->getBaseDiscount();
                $discount     = $purchase->getDiscount();
                $balance      = $discount - $exitDiscount;
                $balance      = $balance >0?$balance:0;
                $base_balance      = $base_discount - $exitBaseDiscount;
                $base_balance      = $base_balance >0?$base_balance:0;
                $total->setTotalAmount($this->getCode(), - $balance);
                $total->setBaseTotalAmount($this->getCode(), - $base_balance);

                $total->setRewardsDiscount($balance);
                $total->setBaseRewardsDiscount($base_balance);

                // $total->setGrandTotal($total->getGrandTotal() - $balance);
                // $total->setBaseGrandTotal($total->getBaseGrandTotal() - $balance);

                /*$total->setSubtotalWithDiscount($total->getSubtotalWithDiscount() - $balance);
                $total->setBaseSubtotalWithDiscount($total->getBaseSubtotalWithDiscount() - $balance);*/
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $this;
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
            $earnPoints = $purchase->getEarnPoints();
            if ($earnPoints) {
                $result[] = [
                    'code'        => $this->getCode(),
                    'title'       => __('Earn %1', $this->rewardsData->getUnit($earnPoints)),
                    'value'       => $this->rewardsData->formatPoints($earnPoints),
                    'label'       => 'good',
                    'is_formated' => false,
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
        return __('Earning Points...');
    }
}
