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
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Model;

class Purchase extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Purchase cache tag
     */
    const CACHE_TAG = 'rewardpoints_purchase';

    const DISCOUNT = 'discount';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpoints_purchase';

    protected $quote;

    protected $rewardsBalanceSpend;
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Framework\Model\Context                               $context            
     * @param \Magento\Framework\Registry                                    $registry           
     * @param \Lof\RewardPoints\Model\ResourceModel\Purchase|null            $resource           
     * @param \Lof\RewardPoints\Model\ResourceModel\Purchase\Collection|null $resourceCollection 
     * @param \Lof\RewardPoints\Helper\Data                                  $rewardsData        
     * @param array                                                          $data               
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\RewardPoints\Model\ResourceModel\Purchase $resource = null,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\Collection $resourceCollection = null,
        \Lof\RewardPoints\Helper\Data $rewardsData,
         \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->rewardsData   = $rewardsData;
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Purchase');
    }

    public function toOptionArray($emptyOption = false)
    {
        return $this->getCollection()->toOptionArray($emptyOption);
    }

    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    public function getQuote()
    {
        return $this->quote;
    }

    public function getParams($code = '')
    {   
        $params = [];
        if ($this->getData('params')) {
            $params = unserialize($this->getData('params'));
        }
        return $params;
    }

    public function refreshDiscount()
    {
        $params   = $this->getParams();
        $discount = 0;
        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['discount'])) {
            $discount += $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['discount'];
        }

        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'])) {
            $discount += $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'];
        }

        /* if (isset($params[Config::SPENDING_RATE]['discount']) && !isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'])) {
            $discount += $params[Config::SPENDING_RATE]['discount'];
        } */

        if (isset($params[Config::SPENDING_RATE]['discount'])) {
            $discount += $params[Config::SPENDING_RATE]['discount'];
        }

        $this->setData('discount', $discount);
        return $this;
    }

    public function refreshBaseDiscount()
    {
        $params   = $this->getParams();
        $base_discount = 0;
        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['base_discount'])) {
            $base_discount += $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['base_discount'];
        }

        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['base_discount'])) {
            $base_discount += $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['base_discount'];
        }

        /* if (isset($params[Config::SPENDING_RATE]['base_discount']) && !isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'])) {
            $discount += $params[Config::SPENDING_RATE]['base_discount'];
        } */

        if (isset($params[Config::SPENDING_RATE]['base_discount'])) {
            $base_discount += $params[Config::SPENDING_RATE]['base_discount'];
        }

        $this->setData('base_discount', $base_discount);
        return $this;
    }

    public function refreshSpendingCatalogRules()
    {
        $params = $this->getParams();
        if(!($quote = $this->getQuote())) {
            $quote = $this->rewardsData->getQuote();
        }
        $collection    = $quote->getAllVisibleItems();
        $totalDiscount = 0;
        if(isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules'])) {
            // Compare with cart items, verify discount qty
            $tmp = [];
            foreach ($collection as $item) {
                $tmp[strtolower($item->getSku())] = $item->getQty();
            }
            $rules = [];
            foreach ($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules'] as $ruleId => $items) {
                foreach ($items['items'] as $sku => $_item) {
                    if (isset($tmp[$sku])) {
                        if($_item['qty'] < $tmp[$sku]) {
                            $tmp[$sku] = $tmp[$sku] - $_item['qty'];
                        } else {
                            $_item['qty'] = $tmp[$sku];
                            unset($tmp[$sku]);
                        }
                        $rules[$ruleId]['items'][$sku] = $_item;
                    }
                }
            }
            $totalPoints = 0;

            foreach ($rules as $ruleId => $rule) {
                $ruleSpendPoints = 0;
                $ruleDiscount    = 0;
                if (empty($rule['items'])) {
                    unset($rules[$ruleId]);
                    continue;
                }
                foreach ($rule['items'] as $sku => $product) {
                    $ruleSpendPoints += ($product['points'] * $product['steps']);
                    $ruleDiscount += ($product['discount'] * $product['steps']);
                }
                $rules[$ruleId]['points'] = $ruleSpendPoints;
                $rules[$ruleId]['discount'] = $ruleDiscount;
                $totalPoints += $ruleSpendPoints;
                $totalDiscount += $ruleDiscount;
            }
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules']    = $rules;
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['points']   = $totalPoints;
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['discount'] = $totalDiscount;
            $this->setData('spend_catalog_points', $totalPoints);
        }
        $this->setParams($params);
        $this->refreshSpendPoints();
        return $this;
    }

    public function verifyPointsWithCartItems()
    {
        $params     = $this->getParams();
        $quote      = $this->rewardsData->getQuote();
        $total      = $quote->getTotals();
        $price      = $total['grand_total']->getValue() + $this->getDiscount();
        $collection = $quote->getAllVisibleItems();
        $tmp = [];
        foreach ($collection as $item) {
            $tmp[strtolower($item->getSku())] = $item->getQty();
        }
        // Spending Cart Rules
        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
            $spendingProductPoints = $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'];
            foreach ($spendingProductPoints as $ruleId => $rule) {
                if (isset($rule['discount']) && $rule['discount'] > $price) {
                    $spendingProductPoints[$ruleId]['discount'] = ($price / $rule['stepdiscount']) * $rule['stepdiscount'];
                    $spendingProductPoints[$ruleId]['points'] = $spendingProductPoints[$ruleId]['discount'] * $spendingProductPoints[$ruleId]['steps'];
                }
            }
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'] = $spendingProductPoints;
        }

        // Spending Catalog Rules
        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules'])) {
            if ($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['discount'] > $price) {
                $priceAvaialable = $price;
                $spendingCatalogRules = $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules'];
                $newRules = [];
                foreach ($spendingCatalogRules as $ruleId => $rule) {
                    foreach ($rule['items'] as $sku => $item) {
                        if (($priceAvaialable / $item['discount']) > 1) {
                            $item['steps'] = (float) ($priceAvaialable / $item['discount']);
                            $newRules[$ruleId]['items'][$sku] = $item;
                            $priceAvaialable -= ($item['steps'] * $item['discount']);
                        }
                    }
                }
                $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE]['rules'] = $newRules;
            }
        }

        // Spending product points
        
        if (isset($params[Config::SPENDING_PRODUCT_POINTS]['items'])) {
            $spendingProductPoints = $params[Config::SPENDING_PRODUCT_POINTS]['items'];
            $newProductPoitns = ($spendingProductPoints && is_array($spendingProductPoints))?$spendingProductPoints:[];
            $discount = 0;
            $points = 0;
            foreach ($spendingProductPoints as $sku => $item) {
                if (isset($tmp[$sku])) {
                    $item['qty'] = $tmp[$sku];
                    $discount += ($item['qty'] * $item['discount']);
                    $points += ($item['qty'] * $item['points']);
                    $newProductPoitns[$sku] = $item;
                }
            }

            $params[Config::SPENDING_PRODUCT_POINTS]['items'] = $newProductPoitns;
            $params[Config::SPENDING_PRODUCT_POINTS]['points'] = $points;
            $params[Config::SPENDING_PRODUCT_POINTS]['discount'] = $discount;
        }
        $this->setParams($params);
        $this->refreshPoints();
        return $this;
    }

    public function refreshSpendingProductPoints()
    {
        $params = $this->getParams();
        if (isset($params[Config::SPENDING_PRODUCT_POINTS]['items'])) {
            $spendingProductPoitns = $params[Config::SPENDING_PRODUCT_POINTS]['items'];
            $discount = 0;
            $points = 0;
            foreach ($spendingProductPoitns as $item) {
                $discount += ($item['qty'] * $item['discount']);
                $points += ($item['qty'] * $item['points']);
            }
            $params[Config::SPENDING_PRODUCT_POINTS]['points'] = $points;
            $params[Config::SPENDING_PRODUCT_POINTS]['discount'] = $discount;
        }
        $this->setParams($params);
        return $this;
    }

    public function refreshSpendingRates()
    {
        $params = $this->getParams();
        $points     = 0;
        $discount   = 0;
        if (isset($params[Config::SPENDING_RATE]['rules'])) {
            $rules = $params[Config::SPENDING_RATE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                if (isset($rule['status']) && $rule['status']) {
                    $points += (float) $rule['points'];
                    $discount += $rule['discount'];
                }
            }
            $params[Config::SPENDING_RATE]['rules']    = $rules;
            $params[Config::SPENDING_RATE]['discount'] = $discount;
            $this->setSpendRatePoints($points);
            $this->refreshSpendPoints();

        }
        $this->setParams($params);
        return $this;
    }

    public function refreshSpendingCartRules()
    {
        $params     = $this->getParams();
        if (isset($params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'])) {
            $rules      = $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules'];
            $points     = 0;
            $discount   = 0;
            foreach ($rules as $ruleId => $rule) {
                if (isset($rule['status']) && $rule['status']) {
                    $points += (float) $rule['points'];
                    $discount += $rule['discount'];
                }
            }
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['rules']    = $rules;
            $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]['discount'] = $discount; 
            $this->setParams($params);
            $this->setSpendCartPoints($points);
            $this->refreshSpendPoints();
        }
        return $this;
    }

    public function refreshPoints()
    {
        $this->refreshEarningRulePoints();
        $this->refreshEarningProductPoints();
        $this->refreshEarningCartRulePoints();
        $this->refreshSpendingCatalogRules();
        $this->refreshSpendingCartRules();
        $this->refreshSpendingProductPoints();
        $this->refreshSpendingRates();
        $this->refreshDiscount();
        $this->refreshBaseDiscount();
        return $this;
    }

    // function whatever($array, $key, $val) {
    //     foreach ($array as $item)
    //         if (isset($item[$key]) && $item[$key] == $val)
    //             return true;
    //     return false;
    // }

    public function refreshSpendPoints()
    {
        $params             = $this->getParams();
        $spendProductPoints = 0;
        if (isset($params[Config::SPENDING_PRODUCT_POINTS]['points'])) {
            $spendProductPoints = $params[Config::SPENDING_PRODUCT_POINTS]['points'];
        }
        $spendCatalogPoints = (float) $this->getData('spend_catalog_points');
        $spendCartPoints    = (float) $this->getData('spend_cart_points');
        $spendRatePoints    = (float) $this->getData('spend_rate_points');
        if($this->rewardsData->hasCheckoutSession()) {
            $quote      = $this->rewardsData->getQuote();
        } else {
            $quote_id       = $this->getQuoteId();
            $quote      = $this->rewardsData->getQuote((int)$quote_id);
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product_ids = [];
        $products = $quote->getAllItems();
        $cart_item_product_ids = [];
        $cart_products_qty = [];
        if($products) {
            foreach($products as $item) {
                $cart_products_qty[$item->getData("product_id")] = $item->getData("qty");
                $cart_item_product_ids[] = $item->getData("product_id");
                $product = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($item->getData("product_id"));
                //check for configurable product
                if(count($product_ids) > 0){
                    foreach ($product_ids as &$arr){
                        if (isset($arr['product_id']) && $arr['product_id'] == $item->getData("product_id")){
                            if($arr['qty'] < $item->getData("qty"))
                                $arr['qty'] = $item->getData("qty");
                        }
                    }
                }else{
                    if(isset($product[0])){
                        $product_ids[$product[0]]['product_id'] = $product[0];
                        $product_ids[$product[0]]['qty'] = $item->getData("qty");
                        $cart_item_product_ids[] = $product[0];
                        $cart_products_qty[$product[0]] = $item->getData("qty");
                    }
                }
                $product_ids[$item->getData("product_id")]['product_id'] = $item->getData("product_id");
                $product_ids[$item->getData("product_id")]['qty'] = $item->getData("qty");
            }
        }
        $productSpendingPoints = $this->rewardsBalanceSpend->getArrayProductSpendingPoints($cart_item_product_ids);
        $totalPoints = 0;
    
        if($product_ids) {
            foreach ($productSpendingPoints as $_productSpendingPoints) {
                foreach ($product_ids as $product_id) {
                    if($product_id["product_id"] == $_productSpendingPoints['product_id']) {
                        $totalPoints += (float)$_productSpendingPoints['points']*$product_id["qty"];
                    }
                }
            }
        }
        $total_spend_points = (float) ($spendCatalogPoints + $spendCartPoints + $spendProductPoints + $spendRatePoints+$totalPoints);
        if($total_spend_points > 0) {
            $this->setData('spend_points', $total_spend_points);
        }else {
            $this->setData('spend_points', 0);
        }
        return $this;
    }

    public function refreshEarnPoints()
    {
        $earnCatalogPoints = (float) $this->getData('earn_catalog_points');
        $earnCartPoints = (float) $this->getData('earn_cart_points');
        $earnProductPoints = (float) $this->getData('earn_product_points');
        $earnPoints = $earnCartPoints + $earnCatalogPoints + $earnProductPoints;
        $this->setData('earn_points', $earnPoints);
        return $this;
    }

    public function refreshEarningCartRulePoints()
    {
        $params  = $this->getParams();
        $totalPoints = 0;
        if (isset($params[\Lof\RewardPointsRule\Model\Config::EARNING_CART_RULE]['rules'])) {
            $rules = $params[\Lof\RewardPointsRule\Model\Config::EARNING_CART_RULE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $productId => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['items'][$productId])) {
                        unset($rules[$ruleId]['items'][$productId]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
            }
            $params[\Lof\RewardPointsRule\Model\Config::EARNING_CART_RULE]['rules']  = $rules;
            $params[\Lof\RewardPointsRule\Model\Config::EARNING_CART_RULE]['points'] = $totalPoints;
        }

        $this->setData('earn_cart_points', $totalPoints);
        $this->setParams($params);
        $this->refreshEarnPoints();
        return $this;
    }

    // ---------------------------. product point
    public function refreshEarningProductPoints()
    {
        $params  = $this->getParams();
        $totalPoints = 0;
        if (isset($params[Config::EARNING_PRODUCT_POINTS]['rules'])) {
            $rules = $params[Config::EARNING_PRODUCT_POINTS]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $product_points = 0;
                foreach ($rule as $sku => $product) {

                    $product_points += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $product_points;
                $totalPoints += $rules[$ruleId]['points'];
            }
        }
        $this->setData('earn_product_points', $totalPoints);
        $this->refreshEarnPoints();
        return $this;
    }
    // ---------------------------. end product point

    public function refreshEarningRulePoints()
    {
        $params  = $this->getParams();
        $totalPoints = 0;
        if (isset($params[\Lof\RewardPointsRule\Model\Config::EARNING_CATALOG_RULE]['rules'])) {
            $rules = $params[\Lof\RewardPointsRule\Model\Config::EARNING_CATALOG_RULE]['rules'];
            $totalPoints = 0;
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $sku => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['rules']['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['rules']['items'][$sku])) {
                        unset($rules[$ruleId]['items'][$sku]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
            }
            $params[\Lof\RewardPointsRule\Model\Config::EARNING_CATALOG_RULE]['rules'] = $rules;
            $params[\Lof\RewardPointsRule\Model\Config::EARNING_CATALOG_RULE]['points'] = $totalPoints;
            if (isset($params[Config::EARNING_PRODUCT_POINTS]['items']) && is_array($params[Config::EARNING_PRODUCT_POINTS]['items'])) {
                $points = 0;
                foreach ($params[Config::EARNING_PRODUCT_POINTS]['items'] as $k => $product) {
                    $totalPoints += ($product['qty'] * $product['points']);
                    $points += ($product['qty'] * $product['points']);
                }
                $params[Config::EARNING_PRODUCT_POINTS]['points'] = $points;
            }
        }

        // Duplicate earning rates, change  $params[Config::EARNING_CART_RULE] => $params[Config::EARNING_RATE]
        if (isset($params[Config::EARNING_RATE]['rules'])) {
            $rateTotalPoints = 0;
            $rules = $params[Config::EARNING_RATE]['rules'];
            foreach ($rules as $ruleId => $rule) {
                $rulePoints = 0;
                foreach ($rule['items'] as $productId => $product) {
                    // table lof_rewardpoints_product_earning_points
                    if(isset($params[Config::EARNING_PRODUCT_POINTS]['rules']['items']) && isset($params[Config::EARNING_PRODUCT_POINTS]['rules']['items'][$productId])) {
                        unset($rules[$ruleId]['items'][$productId]);
                        continue;
                    }
                    $rulePoints += ($product['points'] * $product['qty']);
                }
                $rules[$ruleId]['points'] = $rulePoints;
                $totalPoints += $rules[$ruleId]['points'];
                $rateTotalPoints += $rules[$ruleId]['points'];
            }
            $params[Config::EARNING_RATE]['rules'] = $rules;
            $params[Config::EARNING_RATE]['points'] = $rateTotalPoints;
        }

        $this->setData('earn_catalog_points', $totalPoints);
        $this->refreshEarnPoints();

        $this->setParams($params);
        return $this;
    }

    public function resetFullData()
    {
        $params[Config::EARNING_PRODUCT_POINTS]                            = [];
        $params[Config::EARNING_RATE]                                      = [];
        $params[\Lof\RewardPointsRule\Model\Config::EARNING_CATALOG_RULE]  = [];
        $params[\Lof\RewardPointsRule\Model\Config::EARNING_CART_RULE]     = [];
        $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CATALOG_RULE] = [];
        $params[\Lof\RewardPointsRule\Model\Config::SPENDING_CART_RULE]    = [];
        $params[Config::SPENDING_PRODUCT_POINTS]                           = [];
        $params[Config::SPENDING_RATE]                                     = [];
        $this->setParams($params);
        $this->setEarnPoints(0);
        $this->setSpendPoints(0);
        $this->setEarnCatalogPoints(0);
        $this->setEarnCartPoints(0);
        $this->setSpendCartPoints(0);
        $this->setSpendCatalogPoints(0);
        $this->setDiscount(0);
        $this->setBaseDiscount(0);
        $this->setSpendAmount(0);
        return $this;
    }

    public function getDiscount($fullDiscount = false) {
        $params   = $this->getParams();
        $discount = (float) $this->getData('discount');
        if ($fullDiscount && isset($params[Config::SPENDING_PRODUCT_POINTS]['discount'])) {
            $discount += $params[Config::SPENDING_PRODUCT_POINTS]['discount'];
        }
        return $discount;
    }

    public function getBaseDiscount($fullDiscount = false) {
        $params   = $this->getParams();
        $discount = (float) $this->getData('base_discount');
        if ($fullDiscount && isset($params[Config::SPENDING_PRODUCT_POINTS]['base_discount'])) {
            $discount += $params[Config::SPENDING_PRODUCT_POINTS]['base_discount'];
        }
        return $discount;
    }

    public function setParams($newParams)
    {
        $params = [];
        if (is_array($params)) {
            $params = serialize($newParams);
        }
        $this->setData('params', $params);
        return $this;
    }
}