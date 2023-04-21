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

namespace Lof\RewardPointsRule\Helper;

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

    /**
     * @param \Magento\Framework\App\Helper\Context      $context         
     * @param \Magento\Checkout\Model\Cart               $cart            
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository 
     * @param \Lof\RewardPoints\Helper\Purchase          $rewardsPurchase 
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData     
     * @param \Lof\RewardPoints\Helper\Customer          $rewardsCustomer 
     * @param \Lof\RewardPoints\Logger\Logger            $rewardsLogger   
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
    	parent::__construct($context);
        $this->cart            = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData     = $rewardsData;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger   = $rewardsLogger;
        $this->currencyFactory = $currencyFactory;
    }

    public function convertPrice($amount = 0, $baseCurrency = null, $currentCurrency = null)
    {
        if(!$baseCurrency || !$currentCurrency){
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
        if(isset($post['isAjax']) && isset($post['quote'])) {
            $response = [];
            try {
                $quote    = $this->rewardsData->getQuote($post['quote']);
                $currencyCode = $quote->getQuoteCurrencyCode();
                $baseCurrencyCode = $quote->getBaseCurrencyCode();
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $total    = $quote->getTotals();
                $params   = $purchase->getParams();
                $customer = $this->rewardsCustomer->getCustomer($quote->getCustomer()->getId());

                //Verify spend points
                if ($post['rulemax'] && $post['spendpoints'] > $post['rulemax']) {
                    $post['spendpoints'] = $post['rulemax'];
                }

                if ($post['rulemin'] && $post['spendpoints'] < $post['rulemin']) {
                    $post['spendpoints'] = $post['rulemin'];
                }

                $totalDiscount = $points = 0;
                if (isset($params[Config::SPENDING_RATE]['rules'])) {
                    $spendingRate = $params[Config::SPENDING_RATE]['rules'];
                    foreach ($spendingRate as $ruleId => $rule) {
                        if ($ruleId == $post['rule']) {
                            $status   = 1;
                            $points   = $post['spendpoints'];
                            $discount = $post['discount'];
                            $discount = $this->convertPrice($discount, $baseCurrencyCode, $currencyCode);
                            $step     = 0;
                            if ($post['discount']) {
                                $step = $post['spendpoints'] / $post['discount'];
                            }
                            $totalDiscount += $discount;
                        } else {
                            // Reset rule not used
                            $status = $step = $discount = 0;
                            $points = isset($rule['points'])?$rule['points']:0;
                        }
                        $spendingRate[$ruleId] = [
                            'items'        => $rule['items'],
                            'points'       => $points,
                            'discount'     => $discount,
                            'steps'        => $step,
                            'status'       => $status,
                            'stepdiscount' => $post['stepdiscount']
                        ];
                    }
                    $params[Config::SPENDING_RATE]['discount'] = $totalDiscount;
                    $params[Config::SPENDING_RATE]['rules'] = $spendingRate;
                }

                $purchase->setParams($params);
                $purchase->refreshPoints();
                if ($purchase->getQuoteId()) {
                    $purchase->save();
                }


                // Reset Quote
                $cartQuote  = $this->cart->getQuote();
                $itemsCount = $cartQuote->getItemsCount();

                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
                $total = $cartQuote->getTotals();

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
                    'discount' => [
                        'value'    => (int) $purchase->getDiscount(true)
                    ],
                    'spendpoints' => [
                        'value' => (int) $purchase->getSpendPoints(),
                        'unit'  => $this->rewardsData->getUnit((int) $purchase->getSpendPoints()),
                    ],
                    'earnpoints' => [
                        'value' => (int) $purchase->getEarnPoints(),
                        'unit'  => $this->rewardsData->getUnit((int) $purchase->getEarnPoints()),
                    ],
                ];
            } catch (\Exception $e) {
                $response = [];
                $this->rewardsLogger->addError($e->getMessage());
            }
            return $response;
        }
        return false;
    }
}