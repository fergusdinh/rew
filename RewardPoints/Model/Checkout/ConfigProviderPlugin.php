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

namespace Lof\RewardPoints\Model\Checkout;

use Lof\RewardPoints\Model\Config;

class ConfigProviderPlugin
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\Collection
     */
    protected $spendingRuleCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var array
     */
    protected $_currentRule = [];

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $_rules;
    /**
     * @var Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $quoteItem;

    protected $_request;

    /**
     * @param \Magento\Framework\UrlInterface                                  $urlBuilder
     * @param \Magento\Framework\Module\Manager                                $moduleManager
     * @param \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory
     * @param \Lof\RewardPoints\Helper\Balance\Spend                           $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Helper\Customer                                $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Purchase                                $rewardsPurchase
     * @param \Lof\RewardPoints\Model\Config                                   $rewardsConfig
     * @param \Lof\RewardPoints\Helper\Data                                    $rewardsData
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Module\Manager $moduleManager,
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItem,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->moduleManager                 = $moduleManager;
        $this->urlBuilder                    = $urlBuilder;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->rewardsBalanceSpend           = $rewardsBalanceSpend;
        $this->rewardsCustomer               = $rewardsCustomer;
        $this->rewardsPurchase               = $rewardsPurchase;
        $this->rewardsConfig                 = $rewardsConfig;
        $this->rewardsData                   = $rewardsData;
        $this->quoteItem                     = $quoteItem;
        $this->_request = $request;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array                                         $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(\Magento\Checkout\Model\DefaultConfigProvider $subject, array $result)
    {
        $quote    = $this->rewardsData->getQuote();
        $purchase = $this->rewardsPurchase->getByQuote($quote);
        if ($purchase) {
            $customer = $this->rewardsCustomer->getCustomer();
            $result['rewardpoints']['earnpoints']['value']  = $result['rewardpoints']['spendpoints']['value'] = $result['rewardpoints']['discount'] = 0;

            $earnPoints = (float) $purchase->getEarnPoints();
            if ($earnPoints) {
                $result['rewardpoints']['earnpoints'] = [
                    'value' => $earnPoints,
                    'unit'  => $this->rewardsData->getUnit($earnPoints)
                ];
            }

            $spendPoints = (float) $purchase->getSpendPoints();
            if ($spendPoints) {
                $result['rewardpoints']['spendpoints'] = [
                    'value' => $spendPoints,
                    'unit'  => $this->rewardsData->getUnit($spendPoints)
                ];
            }

            if ($fullDiscount = $purchase->getDiscount(true)) {
                $result['rewardpoints']['discount'] = [
                    'value' => $fullDiscount
                ];
            }

            $avaiblePoints = 0;
            if ($customer && $customer->getId()) {
                $avaiblePoints = (float) $customer->getAvailablePoints();
            }
            $result['rewardpoints']['avaiblepoints'] = $avaiblePoints;
            $result['rewardpoints']['rules']         = $this->getRuleBySlider();
            $result['rewardpoints']['currentrule']   = $this->getCurrentRule();
            $result['rewardpoints']['ajaxurl']       = $this->getApplyPointsUrl();
            $result['rewardpoints']['pointslabel']   = $this->rewardsConfig->getPointsLabel();
            $result['rewardpoints']['pointsimage']   = $this->rewardsData->getPointImage();
            $result['rewardpoints']['showonshoppingcart'] = 1;
            $result['rewardpoints']['showoncheckoutpage'] = 1;
            $currentFullAction = $this->_request->getFullActionName();
            $currentFullAction = strtolower($currentFullAction);

            if ($currentFullAction == 'checkout_cart_index') {
                //Cart page
                $result['rewardpoints']['showonshoppingcart'] = (int)$this->rewardsConfig->getConfig("display/show_on_shopping_cart");
            }
            if ($currentFullAction == 'checkout_index_index') {
                //Cart page
                $result['rewardpoints']['showoncheckoutpage'] = (int)$this->rewardsConfig->getConfig("display/show_on_checkout_page");
            }
            $result['rewardpoints']['currentpage'] = $currentFullAction;
        }
        return $result;
    }

    public function getApplyPointsUrl()
    {
        return $this->urlBuilder->getUrl(Config::ROUTES . '/checkout/applypoints');
    }

    public function getPurchase()
    {
        $purchase = $this->rewardsPurchase->getPurchase();
        return $purchase;
    }

    public function getRules()
    {
        if ($this->_rules=='') {
            $purchase = $this->getPurchase();
            $params   = $purchase->getParams();
            if (isset($params[Config::SPENDING_RATE])) {
                $ruleIds = [];
                $rules = $this->spendingRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['in' => $ruleIds]);
                $this->_rules = $rules;
            }
        }
        return $this->_rules;
    }

    public function getRuleBySlider()
    {
        $avaiblePoints = 0;
        $json          = [];
        $rules         = $this->getRules();
        $purchase      = $this->getPurchase();
        $params        = $purchase->getParams();
        $quote         = $this->rewardsData->getQuote();
        $quoteId       = $quote->getId();
        $quoteItem     = $this->quoteItem->create();
        $Items         = $quoteItem->addFieldToFilter('quote_id', $quoteId);
        $checkMaxProductPrice = true;
        if ($this->rewardsConfig->isSpendPointsFromShipping()) {
            $checkMaxProductPrice = false;
        }
        $price = [];
        foreach ($Items->getData() as $items) {
            $price[]= $items['price'];
        }
        $productPrice = array_sum($price);
        $customer      = $this->rewardsCustomer->getCustomer();
        if ($customer && $customer->getId()) {
            $customerParams = $customer->getParams();
            $avaiblePoints = $customer->getAvailablePoints() + $purchase->getSpendCartPoints();
        } else {
            return $json;
        }

        if (isset($params[Config::SPENDING_RATE]) || ($this->moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]))) {
            $currentRuleId = 0;
            $values        = [];
            $ruleIds       = [];
            $spendingRates = $params[Config::SPENDING_RATE];
            if (isset($spendingRates['rules'])) {
                foreach ($spendingRates['rules'] as $ruleId => $item) {
                    $ruleIds[] = $ruleId;
                    if (isset($item['status']) && $item['status']) {
                        $currentRuleId =  $ruleId;
                    }
                    if (isset($item['points'])) {
                        $values[$ruleId] = $item['points'];
                    }
                }

                // LOF REWARD POINTS RULE
                if ($this->moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
                    foreach ($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'] as $ruleId => $item) {
                        $ruleIds[] = $ruleId;
                        if (isset($item['status']) && $item['status']) {
                            $currentRuleId =  $ruleId;
                        }
                        if (isset($item['points'])) {
                            $values[$ruleId] = $item['points'];
                        }
                    }
                }

                $rules = $this->spendingRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['in' => $ruleIds]);

                $subTotal = $quote->getSubtotal();
//                $productPrice = ;
                $i        = 0;
                $json[$i] = ['value' => 0.00,
                            'step'          => 0,
                            'max'           => 0,
                            'min'           => 0,
                            'discount'      => 0,
                            'id'            => 0,
                            'name'          => __("Choose a Spending Rule"),
                            'type'          => \Lof\RewardPointsRule\Model\Spending::ACTION_AMOUNT_SPENT,
                            'message'       => '',
                            'messagestatus' => '',
                            'quote'         => (int) $quoteId,
                            'rulemin'       => 0,
                            'rulemax'       => 0
                        ];
                $i++;
                foreach ($rules as $rule) {
                    $ruleId       = $rule->getId();
                    $spendPoints  = (float) $rule->getSpendPoints();
                    $monetaryStep = (float) $rule->getMonetaryStep();
                    $maxPoints    = (float) $rule->getSpendMaxPoints();
                    $minPoints    = (float) $rule->getSpendMinPoints();
                    $maxPercentProduct = (float) $rule->getPercentageMaxPoints();
                    $message      = $messageStatus = '';

                    if ($maxPoints && $minPoints && $maxPercentProduct && $maxPoints<$minPoints && $maxPercentProduct) {
                        continue;
                    }
                    $maxProduct = ((float)($productPrice / $monetaryStep)  * $spendPoints);

                    // TH1: Normal
                    $max  = ((float)($subTotal / $monetaryStep)  * $spendPoints);
                    if ($maxProduct<$max && $checkMaxProductPrice) {
                        $max = ((float)($productPrice / $monetaryStep)  * $spendPoints);
                    } else {
                        $max  = ((float)($subTotal / $monetaryStep)  * $spendPoints);
                    }

                    if ($this->rewardsConfig->getMaximumSpendingPointsPerOrder() && $max > $this->rewardsConfig->getMaximumSpendingPointsPerOrder()) {
                        $max = $this->rewardsConfig->getMaximumSpendingPointsPerOrder();
                    }

                    // TH3: Available
                    if ($avaiblePoints && ($max > $avaiblePoints)) {
                        $max = (float) ($avaiblePoints / $spendPoints)  * $spendPoints;
                    }

                    // TH2: Rule Max Points
                    if ($maxPoints && ($max > $maxPoints)) {
                        $max = (float) ($maxPoints / $spendPoints)  * $spendPoints;
                    }

                    if ($max && $max < $spendPoints) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $max), false), $this->urlBuilder->getUrl() . Config::ROUTES . '#earn-points');
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($max && $max < $minPoints) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $max), false), $this->urlBuilder->getUrl() . Config::ROUTES . '#earn-points');
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($avaiblePoints < $spendPoints) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $avaiblePoints), false), $this->urlBuilder->getUrl() . Config::ROUTES . '#earn-points');
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($minPoints && ((float)($avaiblePoints/$minPoints) == 1)) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($minPoints-($avaiblePoints-$minPoints)), false), $this->urlBuilder->getUrl() . Config::ROUTES . '#earn-points');
                        $messageStatus = 'lrw-message-warning';
                    }

                    $value = isset($values[$ruleId]) ? $values[$ruleId] : 0;

                    if ($minPoints && $value<$minPoints) {
                        $value = $minPoints;
                    }

                    if ($maxPoints && $value>$maxPoints) {
                        $value = $maxPoints;
                    }

                    if ($message!='') {
                        $value = 0;
                    }

                    $json[$i] = [
                        'value'         => (float) $value,
                        'step'          => (float) $spendPoints,
                        'max'           => (float) $max,
                        'min'           => (float) $minPoints,
                        'discount'      => (float) $monetaryStep,
                        'id'            => (int) $ruleId,
                        'name'          => $rule->getName(),
                        'type'          => $rule->getType(),
                        'message'       => $message,
                        'messagestatus' => $messageStatus,
                        'quote'         => (int) $quoteId,
                        'rulemin'       => (float) $minPoints,
                        'rulemax'       => (float) $maxPoints,
                    ];

                    if ($currentRuleId == $rule->getId()) {
                        $this->_currentRule = $json[$i];
                    }
                    $i++;
                }
                if (empty($this->_currentRule) && isset($json[0])) {
                    //$json[0]['value']   = $json[0]['rulemin'];
                    //$this->_currentRule = $json[0];
                }
            }
        }
        return $json;
    }

    public function getCurrentRule()
    {
        $currentRule =  $this->_currentRule;
        if (!$currentRule) {
            $quote         = $this->rewardsData->getQuote();
            $quoteId       = $quote->getId();
            $currentRule = ['value' => 0.00,
                            'step'          => 0,
                            'max'           => 0,
                            'min'           => 0,
                            'discount'      => 0,
                            'id'            => 0,
                            'name'          => __("Choose a Spending Rule"),
                            'type'          => \Lof\RewardPointsRule\Model\Spending::ACTION_AMOUNT_SPENT,
                            'message'       => '',
                            'messagestatus' => '',
                            'quote'         => (int) $quoteId,
                            'rulemin'       => 0,
                            'rulemax'       => 0
                        ];
            $this->_currentRule = $currentRule;
        }
        return $currentRule;
    }
}
