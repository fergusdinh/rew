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

namespace Lof\RewardPoints\Block\Checkout\Cart;

use Lof\RewardPoints\Model\Config as RewardsConfig;

class Usepoints extends \Magento\Framework\View\Element\Template
{
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
    protected $rewardsConfig;

    protected $defaultId;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                 $context
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
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context);
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->rewardsBalanceSpend           = $rewardsBalanceSpend;
        $this->rewardsCustomer               = $rewardsCustomer;
        $this->rewardsPurchase               = $rewardsPurchase;
        $this->rewardsData                   = $rewardsData;
        $this->rewardsConfig                 = $rewardsConfig;
    }

    public function getPurchase()
    {
        $purchase = $this->rewardsPurchase->getCurrentPurchase();
        return $purchase;
    }

    public function _toHtml()
    {
        if (!$this->rewardsBalanceSpend->getRules()) {
            return;
        }

        if (!$this->getPurchase()) {
            return;
        }

        if (!$this->rewardsData->getCustomer()->getId()) {
            return;
        }

        return parent::_toHtml();
    }

    public function getCurrentRule()
    {
        $ruleId = (int) $this->_currentRule;
        return $ruleId;
    }

    public function getRules()
    {
        if ($this->_rules=='') {
            $purchase = $this->getPurchase();
            $params   = $purchase->getParams();
            if(isset($params[RewardsConfig::SPENDING_RATE])) {
                $ruleIds = [];
                if (isset($params[RewardsConfig::SPENDING_RATE])) {
                    foreach ($params[RewardsConfig::SPENDING_RATE]['rules'] as $ruleId => $item) {
                        $ruleIds[] = $ruleId;
                        if (isset($item['status']) && $item['status']) {
                            $this->_currentRule = $ruleId;
                        }
                    }
                }
                $rules = $this->spendingRuleCollectionFactory->create()->addFieldToFilter('rule_id', ['in' => $ruleIds]);
                $this->_rules = $rules;
            }
        }
        return $this->_rules;
    }

    public function getDefaultId()
    {
        return $this->defaultId;
    }

    public function getRuleBySlider()
    {
        $avaiblePoints = 0;
        $json          = [];
        $rules    = $this->getRules();
        $quote    = $this->rewardsData->getQuote();
        //$total    = $quote->getTotals();
        $purchase = $this->getPurchase();
        $params   = $purchase->getParams();
        $customer = $this->rewardsCustomer->getCustomer();
        if ($customer && $customer->getId()) {
            $customerParams = $customer->getParams();
            $avaiblePoints = $customer->getAvailablePoints();
            if (isset($customerParams[$quote->getId()])) {
                $avaiblePoints += $customerParams[$quote->getId()];
            }
        }

        if (isset($params[RewardsConfig::SPENDING_RATE])) {
            $spendingCartRules = $params[RewardsConfig::SPENDING_RATE];
            $subTotal = $purchase->getSubtotal();
            if (isset($spendingCartRules['rules'])) {
                foreach ($rules as $rule) {
                    $ruleId       = $rule->getId();
                    $spendPoints  = (float) $rule->getSpendPoints();
                    $monetaryStep = (float) $rule->getMonetaryStep();
                    $pointsLimit  = (float) $rule->getSpendMaxPoints();
                    $pointsMini   = (float) $rule->getSpendMinPoints();

                    $message = $status = '';
                    // TH1: Normal

                    $max  = ((float)($subTotal / $monetaryStep)  * $spendPoints);
                    $step = $spendPoints;

                    if ($this->rewardsConfig->getMaximumSpendingPointsPerOrder() && $max > $this->rewardsConfig->getMaximumSpendingPointsPerOrder()) {
                        $max = $this->rewardsConfig->getMaximumSpendingPointsPerOrder();
                    }

                    // TH3: Available
                    if ($avaiblePoints && ($max > $avaiblePoints)) {
                        $max = (float) ($avaiblePoints / $spendPoints)  * $spendPoints;

                    }

                    // TH2: Rule Max Points
                    if ($pointsLimit && ($max > $pointsLimit)) {
                        $max = (float) ($pointsLimit / $spendPoints)  * $spendPoints;
                    }

                    if ($max < $spendPoints) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a href="%2">here</a> to learn about it.', ($spendPoints - $max), $this->getUrl(RewardsConfig::ROUTES) . '#lrw-earn-instruction');
                        $status = 'lrw-message-warning';
                        //continue;
                    }

                    if ($max < $pointsMini) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a href="%2">here</a> to learn about it.', ($pointsMini - $max), $this->getUrl(RewardsConfig::ROUTES) . '#lrw-earn-instruction');
                        $status = 'lrw-message-warning';
                        //continue;
                    }

                    $defaultValue = 0;
                    foreach ($spendingCartRules['rules'] as $_ruleId => $_rule) {
                        if ($_ruleId == $ruleId && isset($_rule['points'])) {
                            $defaultValue = $_rule['points'];
                            $this->_currentRule = $_ruleId;
                            break;
                        }
                    }

                    if ($defaultValue > $max) {
                        $defaultValue = $max;
                    }

                    if ($avaiblePoints < $spendPoints) {
                        $message = __('You need to earn more %1 to use this rule. Please click <a href="%2">here</a> to learn about it.', ($spendPoints - $avaiblePoints), $this->getUrl(RewardsConfig::ROUTES) . '#lrw-earn-instruction');
                        $status = 'lrw-message-warning';
                    }

                    $json[$ruleId] = [
                        'value'        => (float) $defaultValue,
                        'step'         => (float) $spendPoints,
                        'max'          => (float) $max,
                        'min'          => (float) $pointsMini,
                        'discount'     => (float) $monetaryStep,
                        'id'           => (int) $ruleId,
                        'type'         => $rule->getType(),
                        'message'      => $message,
                        'status'       => $status,
                        'quote'        => $quote->getId(),
                        'rulemin'      => (float) $pointsMini,
                        'rulemax'      => (float) $pointsLimit
                    ];
                }
            }
        }
        return $json;
    }

    public function getMaximumPoints()
    {
        $purchase       = $this->getPurchase();
        $total          = $this->rewardsData->getQuote()->getTotals();
        $subTotal       = $total['subtotal']->getValue();
        $spendMaxPoints = $purchase->getSpendMaxPoints();
        //$spendRate      = $purchase->getSpendRate();
        if ($subTotal > $spendMaxPoints) {
            $maxiMumPoints = (float) $spendMaxPoints;
        }
        $maxiMumPoints = (float) $purchase->getSpendMaxPoints();
        if($this->getBalancePoints() < $maxiMumPoints) {
            $maxiMumPoints = $this->getBalancePoints();
        }
        return $maxiMumPoints;
    }

    public function getBalancePoints()
    {
        if (!$this->rewardsData->isLoggedIn()) {
            return;
        }
        $customer      = $this->rewardsCustomer->getCustomer();
        $avaiblePoints = (float) $customer->getAvailablePoints();
        return $avaiblePoints;
    }

    public function getPointsLabel()
    {
        return $this->rewardsConfig->getPointsLabel();
    }

    public function getApplyPointsUrl()
    {
       return $this->getUrl('rewardpoints/checkout/applypoints');
   }
}
