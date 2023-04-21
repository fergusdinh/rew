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

namespace Lof\RewardPointsRule\Block\Product;

use Lof\RewardPoints\Model\Config;

class View extends \Magento\Catalog\Block\Product\View
{
    protected $_template = 'product/view/points.phtml';
    /**
     * @var Lof\RewardPointsRule\Model\ResourceModel\Spending\Collection
     */
    protected $_rules = [];

    /**
     * @var array
     */
    protected $_currentRule = [];

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPointsRule\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpendRule;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Magento\Catalog\Block\Product\Context              $context                 
     * @param \Magento\Framework\Url\EncoderInterface             $urlEncoder              
     * @param \Magento\Framework\Json\EncoderInterface            $jsonEncoder             
     * @param \Magento\Framework\Stdlib\StringUtils               $string                  
     * @param \Magento\Catalog\Helper\Product                     $productHelper           
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig       
     * @param \Magento\Framework\Locale\FormatInterface           $localeFormat            
     * @param \Magento\Customer\Model\Session                     $customerSession         
     * @param \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository       
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface   $priceCurrency           
     * @param \Lof\RewardPoints\Helper\Balance\Spend              $rewardsBalanceSpend     
     * @param \Lof\RewardPointsRule\Helper\Balance\Spend          $rewardsBalanceSpendRule 
     * @param \Lof\RewardPoints\Helper\Customer                   $rewardsCustomer         
     * @param \Lof\RewardPoints\Helper\Data                       $rewardsData             
     * @param \Lof\RewardPoints\Model\Config                      $rewardsConfig           
     * @param \Lof\RewardPointsRule\Model\Config                  $rewardsRuleConfig       
     * @param array                                               $data                    
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPointsRule\Helper\Balance\Spend $rewardsBalanceSpendRule,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency
            );
        $this->rewardsBalanceSpend     = $rewardsBalanceSpend;
        $this->rewardsBalanceSpendRule = $rewardsBalanceSpendRule;
        $this->rewardsCustomer         = $rewardsCustomer;
        $this->rewardsData             = $rewardsData;
        $this->rewardsConfig           = $rewardsConfig;
        $this->rewardsRuleConfig       = $rewardsRuleConfig;
    }

    public function _toHtml()
    {
        if (!$this->rewardsRuleConfig->isEnable()) {
            return false;
        }
        if (!$this->rewardsData->isLoggedIn()) {
            return false;
        }
        return parent::_toHtml();
    }

    public function getPointsLabel() {
        return $this->rewardsConfig->getPointsLabel();
    }

    public function isCustomerLoggedIn()
    {
        return $this->rewardsData->isLoggedIn();
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product;
    }

    public function getRules()
    {
        $product = $this->getProduct();
        if ($this->rewardsData->isLoggedIn() && !$this->isProductSpendingRules()) {
            $this->_rules = $this->rewardsBalanceSpendRule->getProductSpendingRules($product);
        }
        return $this->_rules;
    }

    public function isProductSpendingRules()
    {
        $product = $this->getProduct();
        if ($this->rewardsBalanceSpend->getProductSpendingPoints($product->getId(), true)) {
            return true;
        }
        return false;
    }

    public function getRewardSlider()
    {
        $customer      = $this->rewardsCustomer->getCustomer();
        $avaiblePoints = 0;
        if ($customer) {
            $avaiblePoints = (int) $customer->getAvailablePoints();
        }
        $rules = $this->getProductSpendingRules();
        $currentRule = [];
        if (count($rules) > 0) {
            $currentRule = $rules[0];
        }

        $rewardPoints = [
            'rules'         => $this->getProductSpendingRules(),
            'ajaxurl'       => '',
            'currentrule'   => $currentRule,
            'avaiblepoints' => $avaiblePoints,
            'pointslabel'   => $this->rewardsData->getUnit($avaiblePoints),
            'pointsimage'   => $this->rewardsData->getPointImage()
         ];
        $data['rewardpoints'] = $rewardPoints;
        return $data;
    }

    public function getProductSpendingRules()
    {   
        $avaiblePoints = 0;
        $json          = [];
        $rules         = $this->getRules();
        $quote         = $this->rewardsData->getQuote();
        $customer      = $this->rewardsCustomer->getCustomer();
        if ($customer && $customer->getId()) {
            $customerParams = $customer->getParams();
            $avaiblePoints = $customer->getAvailablePoints();
            // $avaiblePoints = $customer->getAvailablePoints() + $purchase->getSpendCartPoints();
        } else {
            return $json;
        }
        $currentRuleId = 0;
        $values        = [];
        $rules         = $this->getRules();
        $product       = $this->getProduct();
        $price         = $this->getProductFinalPrice();
        $i             = 0;
        foreach ($rules as $rule) {
            $ruleId       = $rule->getId();
            $spendPoints  = (int) $rule->getSpendPoints();
            $monetaryStep = (int) $rule->getMonetaryStep();
            $maxPoints    = (int) $rule->getSpendMaxPoints();
            $minPoints    = (int) $rule->getSpendMinPoints();
            $message      = $messageStatus = '';

            if ($maxPoints && $minPoints && $maxPoints<$minPoints) {
                continue;
            }

            // TH1: Normal
            $max  = ((int)($price / $monetaryStep)  * $spendPoints);
            if ($this->rewardsConfig->getMaximumSpendingPointsPerOrder() && $max > $this->rewardsConfig->getMaximumSpendingPointsPerOrder()) {
                $max = $this->rewardsConfig->getMaximumSpendingPointsPerOrder();
            }

            // TH3: Available
            if ($avaiblePoints && ($max > $avaiblePoints)) {
                $max = (int) ($avaiblePoints / $spendPoints)  * $spendPoints;
            }

            // TH2: Rule Max Points
            if ($maxPoints && ($max > $maxPoints)) {
                $max = (int) ($maxPoints / $spendPoints)  * $spendPoints;
            }

            if ($max && $max < $spendPoints) {
                $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $max), false), $this->getUrl() . Config::ROUTES . '#earn-points');
                $messageStatus = 'lrw-message-warning';
            }

            if ($max && $max < $minPoints) {
                $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $max),false), $this->getUrl() . Config::ROUTES . '#earn-points');
                $messageStatus = 'lrw-message-warning';
            }

            if ($avaiblePoints < $spendPoints) {
                $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($spendPoints - $avaiblePoints), false), $this->getUrl() . Config::ROUTES . '#earn-points');
                $messageStatus = 'lrw-message-warning';
            }

            if ($minPoints && ((int)($avaiblePoints/$minPoints) == 1)) {
                $message = __('You need to earn more %1 to use this rule. Please click <a target="_blank" href="%2">here</a> to learn about it.', $this->rewardsData->formatPoints(($minPoints-($avaiblePoints-$minPoints)), false), $this->getUrl() . Config::ROUTES . '#earn-points');
                $messageStatus = 'lrw-message-warning';
            }

            $value = isset($values[$ruleId])?$values[$ruleId]:0;

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
                'value'         => (int) $value,
                'step'          => (int) $spendPoints,
                'max'           => (int) $max,
                'min'           => (int) $minPoints,
                'discount'      => (int) $monetaryStep,
                'id'            => (int) $ruleId,
                'name'          => $rule->getName(),
                'type'          => $rule->getType(),
                'message'       => $message,
                'messagestatus' => $messageStatus,
                'quote'         => (int) $quote->getId(),
                'rulemin'       => (int) $minPoints,
                'rulemax'       => (int) $maxPoints,
            ];

            if ($currentRuleId == $rule->getId()) {
                $this->_currentRule = $json[$i];
            }
            $i++;
        }
        if (empty($this->_currentRule) && isset($json[0])) {
            $json[0]['value']   = $json[0]['rulemin'];
            $this->_currentRule = $json[0];
        }
        return $json;
    }


    public function getProductFinalPrice()
    {
        $product      = $this->getProduct();
        $finalPrice   = (int) $product->getFinalPrice();
        $priceInclTax = $this->_taxData->getShippingPrice($finalPrice, true);
        $priceExclTax = $this->_taxData->getShippingPrice($finalPrice);
        $finalPrice = 0;
        if ($this->rewardsBalanceSpendRule->isIncludeTax()) {
            $finalPrice = $priceInclTax;
        } else {
            $finalPrice = $priceExclTax;
        }
        return $finalPrice;
    }

}