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

namespace Lof\RewardPoints\Block\Adminhtml\Sales\Order;

use Lof\RewardPoints\Model\Config as RewardsConfig;
use Lof\RewardPoints\Model\Purchase;
use Magento\Quote\Model\Quote;

class Usepoints extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'checkout/cart/usepoints.phtml';

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
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
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $_rules;

    /**
     * @var int
     */
    protected $_currentRule;

    /**
     * @var Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                 $context
     * @param \Magento\Framework\Module\Manager                                $moduleManager
     * @param \Magento\Customer\Helper\View                                    $customerHelperView
     * @param \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory
     * @param \Lof\RewardPoints\Helper\Balance\Spend                           $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Helper\Customer                                $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Purchase                                $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Data                                    $rewardsData
     * @param \Lof\RewardPoints\Model\Config                                   $rewardsConfig
     * @param array                                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Helper\View $customerHelperView,
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context);
        $this->moduleManager                 = $moduleManager;
        $this->customerHelperView            = $customerHelperView;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->rewardsBalanceSpend           = $rewardsBalanceSpend;
        $this->rewardsCustomer               = $rewardsCustomer;
        $this->rewardsPurchase               = $rewardsPurchase;
        $this->rewardsData                   = $rewardsData;
        $this->rewardsConfig                 = $rewardsConfig;
    }

    public function setPurchase(Purchase $purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    public function getPurchase()
    {
        return $this->purchase;
    }

    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function getQuote()
    {
        return $this->quote;
    }

    public function _toHtml()
    {
        $quote    = $this->getQuote();
        $purchase = $this->rewardsPurchase->getPurchase($quote);
        $this->setPurchase($purchase);
        if (!$this->getBalancePoints()) {
            return;
        }
        if (!$this->rewardsBalanceSpend->setQuote($quote)->getRules()) {
            return;
        }

        return parent::_toHtml();
    }

    public function getCustomer()
    {
        $quote = $this->getQuote();
        return $quote->getCustomer();
    }

    public function getCurrentRule()
    {
        $ruleId = (int) $this->_currentRule;
        return $ruleId;
    }

    public function getRules()
    {
        if ($this->_rules=='') {
            //$quote = $this->getQuote();
            $purchase = $this->getPurchase();
            $params   = $purchase->getParams();
            if (isset($params[RewardsConfig::SPENDING_RATE])) {
                $ruleIds = [];
                if (isset($params[RewardsConfig::SPENDING_RATE]['rules']) && is_array($params[RewardsConfig::SPENDING_RATE]['rules'])) {
                    foreach ($params[RewardsConfig::SPENDING_RATE]['rules'] as $ruleId => $item) {
                        $ruleIds[] = $ruleId;
                        if (isset($item['status']) && $item['status']) {
                            $this->_currentRule = $ruleId;
                        }
                    }
                }

                // LOF REWARD POINTS RULE
                if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]) && $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]) {
                    if ($this->moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
                        foreach ($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'] as $ruleId => $item) {
                            $ruleIds[] = $ruleId;
                            if (isset($item['status']) && $item['status']) {
                                $this->_currentRule =  $ruleId;
                            }
                            if (isset($item['points'])) {
                                $values[$this->_currentRule] = $item['points'];
                            }
                        }
                    }
                }

                $rules = $this->spendingRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['in' => $ruleIds]);
                $this->_rules = $rules;
            }
        }
        return $this->_rules;
    }

    public function getRewardSlider()
    {
        $quote         = $this->getQuote();
        $customer      = $this->rewardsCustomer->getCustomer($quote->getCustomerId());
        $avaiblePoints = 0;
        //$purchase      = $this->getPurchase();
        if ($customer) {
            $avaiblePoints = $customer->getAvailablePoints();
        }
        //$rules = $this->getRuleBySlider();

        $rewardPoints = [
            'rules'         => $this->getRuleBySlider(),
            'ajaxurl'       => $this->getApplyPointsUrl(),
            'currentrule'   => $this->getCurrentRule(),
            'avaiblepoints' => $avaiblePoints,
            'pointslabel'   => $this->rewardsData->getUnit($avaiblePoints),
            'pointsimage'   => $this->rewardsData->getPointImage()
         ];
        $data['rewardpoints'] = $rewardPoints;
        return $data;
    }

    public function getRuleBySlider()
    {
        $quote         = $this->getQuote();
        $avaiblePoints = 0;
        $json          = [];
        $rules         = $this->getRules();
        //$totals        = $quote->getTotals();
        $purchase      = $this->getPurchase();
        $params        = $purchase->getParams();
        $customer      = $this->rewardsCustomer->getCustomer($quote->getCustomerId());
        if ($customer->getId()) {
            $avaiblePoints = $customer->getAvailablePoints();
        }

        if (isset($params[RewardsConfig::SPENDING_RATE]) || ($this->moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]))) {
            $currentRuleId = 0;
            $values        = [];
            $ruleIds       = [];
            $spendingRates = $params[RewardsConfig::SPENDING_RATE];
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
                if ($this->moduleManager->isEnabled('Lof_RewardPointsRule') && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]) && isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
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

                $rules        = $this->spendingRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['in' => $ruleIds]);
                $customerName = $this->customerHelperView->getCustomerName($this->getCustomer());
                $subTotal     = $purchase->getSubtotal();
                $i            = 0;
                foreach ($rules as $rule) {
                    $ruleId       = $rule->getId();
                    $spendPoints  = (float) $rule->getSpendPoints();
                    $monetaryStep = (float) $rule->getMonetaryStep();
                    $maxPoints    = (float) $rule->getSpendMaxPoints();
                    $minPoints    = (float) $rule->getSpendMinPoints();
                    $message      = $messageStatus = '';

                    if ($maxPoints && $minPoints && $maxPoints<$minPoints) {
                        continue;
                    }

                    // TH1: Normal
                    $max  = ((float)($subTotal / $monetaryStep)  * $spendPoints);
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
                        $message = __('%1 need to earn more %1 to use this rule.', $customerName);
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($max && $max < $minPoints) {
                        $message = __('%1 need to earn more %1 to use this rule.', $customerName);
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($avaiblePoints < $spendPoints) {
                        $message = __('%1 need to earn more %1 to use this rule.', $customerName);
                        $messageStatus = 'lrw-message-warning';
                    }

                    if ($minPoints && ((float)($avaiblePoints/$minPoints) == 1)) {
                        $message = __('%1 need to earn more %1 to use this rule.', $customerName);
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

                    $json[$ruleId] = [
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
                        'quote'         => (int) $quote->getId(),
                        'rulemin'       => (float) $minPoints,
                        'rulemax'       => (float) $maxPoints,
                    ];

                    if ($currentRuleId == $rule->getId()) {
                        $this->_currentRule = $json[$ruleId];
                    }
                    $i++;
                }
                if (empty($this->_currentRule)) {
                    foreach ($json as $rule) {
                        $rule['value'] = 0;
                        $this->_currentRule = $rule;
                        break;
                    }
                }
            }
        }
        return $json;
    }

    public function getPointsLabel()
    {
        return $this->rewardsConfig->getPointsLabel();
    }

    public function getMaximumPoints()
    {
        //$quote    = $this->getQuote();
        $purchase = $this->getPurchase();
        $total          = $this->rewardsData->getQuote()->getTotals();
        $subTotal       = $total['subtotal']->getValue();
        $spendMaxPoints = $purchase->getSpendMaxPoints();
        if ($subTotal > $spendMaxPoints) {
            $maxiMumPoints = (float) $spendMaxPoints;
        }
        $maxiMumPoints = (float) $purchase->getSpendMaxPoints();
        if ($this->getBalancePoints() < $maxiMumPoints) {
            $maxiMumPoints = $this->getBalancePoints();
        }
        return $maxiMumPoints;
    }

    public function getBalancePoints()
    {
        $avaiblePoints = 0;
        $quote = $this->getQuote();
        if ($quote && $quote->getId() && ($customerId = $quote->getCustomer()->getId())) {
            $customer = $this->rewardsCustomer->getCustomer($customerId);
            if ($customer->getId()) {
                $avaiblePoints = (float) $customer->getAvailablePoints();
            }
            return $avaiblePoints;
        }
        return;
    }

    public function getApplyPointsUrl()
    {
        return $this->getUrl(RewardsConfig::ROUTES . '/checkout/applypoints');
    }
}
