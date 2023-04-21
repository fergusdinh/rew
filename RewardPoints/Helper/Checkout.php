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

namespace Lof\RewardPoints\Helper;

use Lof\RewardPoints\Model\Config;

class Checkout extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        parent::__construct($context);
        $this->cart            = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData     = $rewardsData;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger   = $rewardsLogger;
        $this->storeManager    = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    public function convertPrice($amount = 0, $baseCurrency = null, $currentCurrency = null)
    {
        if (!$baseCurrency || !$currentCurrency) {
            return $amount;
        }
        if ($currentCurrency != $baseCurrency) {
            $rate = $this->currencyFactory->create()->load($baseCurrency)->getAnyRate($currentCurrency);
            $amount = $amount * $rate;
        }
        return $amount;
    }
    public function applyPoints($post)
    {
        // Advance
        if (isset($post['isAjax']) && isset($post['quote'])) {
            $response = [];
            try {
                $quote    = $this->rewardsData->getQuote($post['quote']);
                $currencyCode = $quote->getQuoteCurrencyCode();
                $baseCurrencyCode = $quote->getBaseCurrencyCode();
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $total    = $quote->getTotals();

                $params   = $purchase->getParams();
                $customer = $this->rewardsCustomer->getCustomer($quote->getCustomer()->getId());

                if (!$customer) {
                    return;
                }

                $post['spendpoints'] = (float)$post['spendpoints'];

                if ($post['spendpoints']<0) {
                    return;
                }

                //Verify spend points
                if ($post['rulemax'] && $post['spendpoints'] && $post['spendpoints'] > $post['rulemax']) {
                    $post['spendpoints'] = $post['rulemax'];
                }

                if ($post['rulemin'] && $post['spendpoints'] && $post['spendpoints'] < $post['rulemin']) {
                    $post['spendpoints'] = $post['rulemin'];
                }
                $is_greater_grandtotal = false;
                $grand_total = $total['grand_total']->getValue();
                $totalDiscount = $points = 0;
                $totalBaseDiscount = 0;
                //Spending shoping cart rule
                if ($this->_moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
                    $spendingCartRules = $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'];
                    foreach ($spendingCartRules as $ruleId => $rule) {
                        if ($ruleId == $post['rule']) {
                            $status   = 1;
                            $points   = $post['spendpoints'];
                            $discount = $post['discount'];
                            $step     = 0;
                            if ($post['discount']) {
                                $step = $post['spendpoints'] / $post['discount'];
                            }
                            $totalDiscount += $discount;
                        } else {
                            // Reset rule not used
                            $status = $step = $discount = 0;
                            $points = isset($rule['points']) ? $rule['points'] : 0;
                        }
                        $discount_with_currency_rate = $this->convertPrice($discount, $baseCurrencyCode, $currencyCode);
                        $spendingCartRules[$ruleId] = [
                            'items'        => $rule['items'],
                            'points'       => $points,
                            'base_discount' => $discount,
                            'discount'     => $discount_with_currency_rate,
                            'steps'        => $step,
                            'status'       => $status,
                            'currencyCode' => $currencyCode,
                            'baseCurrencyCode' => $baseCurrencyCode,
                            'stepdiscount' => $post['stepdiscount']
                        ];
                    }
                    if ($totalDiscount > $grand_total) {
                        $totalDiscount = $grand_total;
                        $is_greater_grandtotal = true;
                    }
                    $totalDiscount = $this->convertPrice($totalDiscount, $baseCurrencyCode, $currencyCode);
                    $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'] = $totalDiscount;
                    $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules']    = $spendingCartRules;
                }

                $totalDiscount = $points = 0;
                //Normal spending rule
                if (isset($params[Config::SPENDING_RATE]['rules'])) {
                    $spendingRate = $params[Config::SPENDING_RATE]['rules'];
                    foreach ($spendingRate as $ruleId => $rule) {
                        if ($ruleId == $post['rule']) {
                            $status   = 1;
                            $points   = $post['spendpoints'];
                            $discount = $post['discount'];
                            $step     = 0;
                            if ($post['discount']) {
                                $step = $post['spendpoints'] / $post['discount'];
                            }
                            $totalDiscount += $discount;
                        } else {
                            // Reset rule not used
                            $status = $step = $discount = 0;
                            $points = isset($rule['points']) ? $rule['points'] : 0;
                        }
                        $discount_with_currency_rate = $this->convertPrice($discount, $baseCurrencyCode, $currencyCode);
                        $spendingRate[$ruleId] = [
                            'items'        => $rule['items'],
                            'points'       => $points,
                            'base_discount' => $discount,
                            'discount'     => $discount_with_currency_rate,
                            'steps'        => $step,
                            'status'       => $status,
                            'currencyCode' => $currencyCode,
                            'baseCurrencyCode' => $baseCurrencyCode,
                            'stepdiscount' => $post['stepdiscount']
                        ];
                    }
                    if ($totalDiscount > $grand_total) {
                        $totalDiscount = $grand_total;
                        $is_greater_grandtotal = true;
                    }
                    $totalDiscount = $this->convertPrice($totalDiscount, $baseCurrencyCode, $currencyCode);
                    $params[Config::SPENDING_RATE]['discount'] = $totalDiscount;
                    $params[Config::SPENDING_RATE]['rules'] = $spendingRate;
                }
                if ($is_greater_grandtotal) {
                    $purchase->setSubtotal($grand_total);
                }
                $purchase->setParams($params);
                $purchase->refreshPoints();
                if ($purchase->getQuoteId()) {
                    $purchase->save();
                }

                // Reset Quote
                $cartQuote  = $quote;

                $itemsCount = $cartQuote->getItemsCount();

                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
                $total = $cartQuote->getTotals();
                // --------------------------reCaculate grand_total--------------------------
                //$grand_total = ($total['subtotal']->getValue() + $total['shipping']->getValue()) - (float) $purchase->getDiscount(true);
                // ----------------------------------------------------

                $response['ajax']['total_segments'] = [
                    [
                        'code'  => 'grand_total',
                        'value' => $total['grand_total']->getValue()
                    ],
                    [
                        'code'  => 'subtotal',
                        'value' => $total['subtotal']->getValue()
                    ]
                ];
                $response['ajax']['rewardpoints'] = [
                    'currencyCode' => $currencyCode,
                    'baseCurrencyCode' => $baseCurrencyCode,
                    'discount' => [
                        'value'    => (float) $purchase->getDiscount(true)
                    ],
                    'spendpoints' => [
                        'value' => (float) $purchase->getSpendPoints(),
                        'unit'  => $this->rewardsData->getUnit((float) $purchase->getSpendPoints()),
                    ],
                    'earnpoints' => [
                        'value' => (float) $purchase->getEarnPoints(),
                        'unit'  => $this->rewardsData->getUnit((float) $purchase->getEarnPoints()),
                    ],
                ];
            } catch (\Exception $e) {
                $response = [];
                $this->rewardsLogger->addError($e->getMessage());
            }
            return $response;
        }else{

        }
    }
}
