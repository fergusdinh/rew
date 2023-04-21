<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\CreditManagementInterface;
use Lof\RewardPoints\Model\Config;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;


class CreditManagement implements CreditManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    
      /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $cart;
     /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $rewardsCheckout;
     /**
     * @var Request
     */
    protected $request;
    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    private $rewardsPurchase;
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    private $rewardsData;
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    private $rewardsCustomer;
    private $_moduleManager;

    /**
     * Constructs a coupon read service object.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository Quote repository.
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Lof\RewardPoints\Helper\Checkout $rewardsCheckout,
         \Magento\Checkout\Model\Cart $cart,
         \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        Request $request,
        \Magento\Framework\Module\Manager $moduleManager

    ) {
         $this->quoteRepository       = $quoteRepository;
         $this->cart                  = $cart;
         $this->rewardsCheckout       = $rewardsCheckout;
         $this->rewardsData           = $rewardsData;
         $this->rewardsPurchase       = $rewardsPurchase;
         $this->rewardsLogger         = $rewardsLogger;
         $this->rewardsCustomer       = $rewardsCustomer;
         $this->request               = $request;
         $this->_moduleManager        = $moduleManager;
    }
    
    /**
     * {@inheritdoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
//        return $quote->getCreditAmount();
    }
    
    /**
     * {@inheritdoc}
     */
    public function set($cartId)
    {
        $post[] = $this->request->getBodyParams();
        // Advance
        if(isset($cartId)) {

            $response = [];
                $quote    = $this->rewardsData->getQuote($cartId);
                $purchase = $this->rewardsPurchase->getPurchase($quote);
                $total    = $quote->getTotals();
                $params[] = $purchase->getParams();
                $customer = $this->rewardsCustomer->getCustomer($quote->getCustomer()->getId());

                if (!$customer) {
                    return;
                }

                $post[0]['spendpoints'] = (float)$post[0]['spendpoints'];

                if ($post[0]['spendpoints']<0) {
                    return;
                }

                //Verify spend points
                if ($post[0]['rulemax'] && $post[0]['spendpoints'] && $post[0]['spendpoints'] > $post[0]['rulemax']) {
                    $post[0]['spendpoints'] = $post[0]['rulemax'];
                }

                if ($post[0]['rulemin'] && $post[0]['spendpoints'] && $post[0]['spendpoints'] < $post[0]['rulemin']) {
                    $post[0]['spendpoints'] = $post[0]['rulemin'];
                }

                $totalDiscount = $points = 0;
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
                            $points = isset($rule['points'])?$rule['points']:0;
                        }
                        $spendingCartRules[$ruleId] = [
                            'items'        => $rule['items'],
                            'points'       => $points,
                            'discount'     => $discount,
                            'steps'        => $step,
                            'status'       => $status,
                            'stepdiscount' => $post['stepdiscount']
                        ];
                    }

                    $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'] = $totalDiscount;
                    $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules']    = $spendingCartRules;
                }


                $totalDiscount = $points = 0;
                if (isset($params[0][Config::SPENDING_RATE])) {
                    $spendingRate = $params[0][Config::SPENDING_RATE]['rules'];
                    foreach ($spendingRate as $ruleId => $rule) {
                      $items = $rule['items'];
                        if ($ruleId == $post[0]['rule']) {
                            $status   = 1;
                            $points   = $post[0]['spendpoints'];
                            $discount = $post[0]['discount'];
                            $step     = 0;
                            if ($post[0]['discount']) {
                                $step = $post[0]['spendpoints'] / $post[0]['discount'];
                            }
                            $totalDiscount += $discount;
                        } else {
                            // Reset rule not used
                            $status = $step = $discount = 0;
                            $points = isset($rule['points'])?$rule['points']:0;
                        }
                        $spendingRate[$ruleId] = [
                            'items'        => $items,
                            'points'       => $points,
                            'discount'     => $discount,
                            'steps'        => $step,
                            'status'       => $status,
                            'stepdiscount' => $post[0]['stepdiscount']
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
                $cartQuote  = $quote;

                $itemsCount = $cartQuote->getItemsCount();

                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
                $total = $cartQuote->getTotals();
                // --------------------------reCaculate grand_total--------------------------
                $grand_total = ($total['subtotal']->getValue() + $total['shipping']->getValue()) - (float) $purchase->getDiscount(true);
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

            return $response;
        }
        return false;
      }
}
