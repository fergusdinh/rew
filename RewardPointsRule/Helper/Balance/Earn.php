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

namespace Lof\RewardPointsRule\Helper\Balance;

use Lof\RewardPoints\Model\Earning;
use Lof\RewardPointsRule\Model\Earning as EarningRule;
use Magento\Catalog\Model\Product;

class Earn extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPointsRule\Model\ResourceModel\Earning\CollectionFactory
     */
    protected $earningRuleCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPointsRule\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Framework\App\Helper\Context                               $context                      [description]
     * @param \Magento\Store\Model\StoreManagerInterface                          $storeManager                 [description]
     * @param \Magento\Catalog\Model\Product\Visibility                           $catalogProductVisibility     [description]
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory      $productCollectionFactory     [description]
     * @param \Magento\Catalog\Model\Config                                       $catalogConfig                [description]
     * @param \Magento\Rule\Model\Condition\Sql\Builder                           $sqlBuilder                   [description]
     * @param \Lof\RewardPoints\Helper\Balance\Spend                              $rewardsBalanceSpend          [description]
     * @param \Lof\RewardPointsRule\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory [description]
     * @param \Lof\RewardPoints\Helper\Balance\Earn                               $rewardsBalanceEarn           [description]
     * @param \Lof\RewardPointsRule\Helper\Data                                   $rewardsData                  [description]
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        // \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
        \Lof\RewardPointsRule\Model\Condition\Sql\Builder $sqlBuilder,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPointsRule\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPointsRule\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->storeManager                 = $storeManager;
        $this->catalogProductVisibility     = $catalogProductVisibility;
        $this->productCollectionFactory     = $productCollectionFactory;
        $this->catalogConfig                = $catalogConfig;
        $this->sqlBuilder                   = $sqlBuilder;
        $this->rewardsBalanceSpend          = $rewardsBalanceSpend;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->rewardsBalanceEarn           = $rewardsBalanceEarn;
        $this->rewardsData                  = $rewardsData;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addMinimalPrice()
        ->addFinalPrice()
        ->addTaxPercents()
        ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
        ->addUrlRewrite()
        ->addStoreFilter();
        return $collection;
    }

    /**
     * @param string $storeId
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore($storeId = '')
    {
        $this->store = $this->storeManager->getStore($storeId);
        return $this->store;
    }

    /**
     * @return mixed
     */

    public function getCustomerGroupId()
    {
        $customerGroupId = $this->rewardsData->getCustomer()->getCustomerGroupId();
        return $customerGroupId;
    }

    /**
     * Get rule by store && customer group id
     * @param  string $store
     * @param  string $customerGroupId
     * @return Lof\RewardPoints\Model\ResourceModel\Earning\Collection
     */
    public function getRules($type = '', $customerGroupId = '')
    {
        if ($type=='') {
            $type = Earning::PRODUCT_RULE;
        }

        $storeId         = $this->getStore()->getStoreId();
        if ($customerGroupId == '') {
            $customerGroupId = $this->getCustomerGroupId();
        }
        $collection = $this->earningRuleCollectionFactory->create()
        ->addFieldToFilter('type', $type)
        ->addStatusFilter()
        ->addDateFilter()
        ->addStoreFilter($storeId, false)
        ->addCustomerGroupFilter($customerGroupId);

        $collection->getSelect()
        ->order('main_table.is_stop_processing DESC')
        ->order('main_table.sort_order ASC')
        ->order('main_table.rule_id DESC');

        return $collection;
    }

    /**
     * [getProductPoints description]
     * @param  Product $product
     * @param  \Lof\RewardsPoints\Model\Earning
     * @return int
     */
    public function getProductPointsByRule(Product $product, $rule)
    {
        return $this->rewardsBalanceEarn->getProductPointsByRule($product, $rule);
    }
    /**
     * @param $collection
     * @param string $type
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    // Load Catalog Rule
    public function loadCatalogRulePoints($collection, $type = '')
    {
        if ($type == '') {
            $type = EarningRule::PRODUCT_RULE;
        }
        $productIds    = $collection->getAllIds();
        $result = [];
        $rules = $this->getRules($type);
        foreach ($collection as $product) {
            $products[$product->getId()] = $product;
        }

        foreach ($rules as $rule) {
            $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
            $conditions = $rule->getConditions();
            $conditions->collectValidatedAttributes($collection);
            $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
            $collection->getSelect()->where('e.entity_id IN (?) ', $productIds);
            $allIds = $collection->getAllIds();
            $ruleId = $rule->getId();
            foreach ($allIds as $k => $v) {
                if (isset($products[$v])) {
                    $product = $products[$v];
                    $productId = $product->getId();
                    $point = $this->getProductPointsByRule($product, $rule);
                    if (isset($result[$ruleId][$productId])) {
                        $result[$ruleId][$productId] += $point;
                    } else {
                        $result[$ruleId][$productId] = $point;
                    }
                }
            }
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param $quote
     * @param string $ruleType
     * @return array
     */
    public function getCatalogRulePoints($quote, $ruleType = '')
    {
        $productSpendingPoints = $this->rewardsBalanceSpend->getProductSpendingPoints();
        $result = [];
        $productIds = [];
        $collection = $quote->getAllVisibleItems();
        foreach ($collection as $item) {
            $productIds[] = $item->getProductId();
        }
        $productCollection = $this->getProductCollection()->addAttributeToFilter('entity_id', ['in' => $productIds]);
        if (count($productSpendingPoints) > 0) {
            $productCollection->addAttributeToFilter('entity_id', ['nin' => array_keys($productSpendingPoints)]);
        }

        $rules = $this->loadCatalogRulePoints($productCollection, $ruleType);

        foreach ($collection as $item) {
            foreach ($rules as $ruleId => $rule) {
                foreach ($rule as $productId => $points) {
                    if ($item->getProductId() == $productId && $points) {
                        $result['rules'][$ruleId]['items'][strtolower($item->getSku())] = [
                        'points'     => $points,
                        'qty'        => $item->getQty(),
                        'product_id' => $productId,
                        'item_id'    => $item->getId()
                        ];
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $quote
     * @param $applyRules
     * @return array
     */
    public function getCartRulePoints($quote, $applyRules)
    {
        $customerGroupId = $quote->getCustomerGroupId();
        $ruleIds = array_keys($applyRules);
        $rules = $this->getRules(EarningRule::CART_RULE, $customerGroupId);
        $rules->addFieldToFilter('main_table.rule_id', ['in' => $ruleIds]);
        $result = [];
        $total  = $quote->getTotals();
        $productSpendingPoints = $this->rewardsBalanceSpend->getProductSpendingPoints();
        $isEarnPointsFromShipping = $this->rewardsData->getConfig()->isEarnPointsFromShipping();
        foreach ($rules as $rule) {
            $itemIds         = $applyRules[$rule->getId()];
            $action          = $rule->getAction();
            $earnPoints      = (int) $rule->getEarnPoints();
            $earnPoints  = $earnPoints > 0 ? $earnPoints : 0;
            $monetaryStep    = (int) $rule->getMonetaryStep();
            $monetaryStep  = $monetaryStep > 0 ? $monetaryStep : 0;
            $pointsLimit     = (int) $rule->getPointsLimit();
//            $earnPoints  = $pointsLimit > $earnPoints ? $pointsLimit : $earnPoints;

            foreach ($itemIds as $k => $v) {
                if ((int) $v == 0) {
                    continue;
                }
                $points = 0;
                $item = $quote->getItemById($v);

                if ($item && $rule->getActions()->validate($item)) {
                    if (isset($productSpendingPoints[$item->getProductId()])) {
                        continue;
                    }

                    $priceInclTax = $item->getPriceInclTax();
                    $priceExclTax = $item->getPrice();

                    $finalPrice = 0;
                    if ($this->rewardsBalanceEarn->isIncludeTax()) {
                        $finalPrice = $priceInclTax;
                    } else {
                        $finalPrice = $priceExclTax;
                    }
                    switch ($action) {
                        case Earning::ACTION_GIVE:
                            $points = $earnPoints;
                            if ($points>$pointsLimit) {
                                $points = $pointsLimit;
                            }
                        break;

                        case Earning::ACTION_PERCENTAGE_BY_PRODUCT_PRICE:
                            if ($earnPoints > 100) {
                                $earnPoints = 100;
                            }
                            $points = (($finalPrice / 100) * $earnPoints); //example: 1000 / 100 * 1
                            if ($points>$pointsLimit) {
                                $points = $pointsLimit;
                            }
                        break;

                        case Earning::ACTION_AMOUNT_SPENT:
                            if ($monetaryStep) {
                                $steps  = $finalPrice / $monetaryStep;
                                $points = $steps * $earnPoints;
                            }
                            if ($points>$pointsLimit) {
                                $points = $pointsLimit;
                            }
                        break;

                        case Earning::ACTION_PERCENTAGE_BY_ORGINAL:
                            $points = (int) ($finalPrice - (($finalPrice / 100) * $earnPoints));
                            if ($points < 0) {
                                $points = 0;
                            }
                            if ($points>$pointsLimit) {
                                $points = $pointsLimit;
                            }
                        break;

                         case Earning::ACTION_PERCENTAGE_BY_CARTTOTAL:
                            if ($isEarnPointsFromShipping && isset($total['shipping']) && $total['shipping']) {
                                $grand_total = $total['subtotal']->getValue() + $total['shipping']->getValue();
                                $points = ($grand_total / 100) * $earnPoints;
                                if ($points>$pointsLimit) {
                                    $points = $pointsLimit;
                                }
                            } else {
                                $points = ($total['subtotal']->getValue() / 100) * $earnPoints;
                                if ($points>$pointsLimit) {
                                    $points = $pointsLimit;
                                }
                            }

                        break;

                        case Earning::ACTION_BY_CART_QTY:
                            if ((int)$rule->getQtyStep() >= 1) {
                                $steps  = (int) ($item->getQty() / $rule->getQtyStep());
                                $points = $steps * $earnPoints;
                            }
                            $points = $points / $item->getQty();

                        break;
                    }

                    if ($points) {
                        $points = $this->rewardsData->getFormatEarningRuleNum($points);
                        if ($pointsLimit && ($points > $pointsLimit)) {
                            $points = $pointsLimit;
                        }
                        $result['rules'][$rule->getId()]['items'][strtolower($item->getSku())] = [
                        'points'     => $points,
                        'qty'        => $item->getQty(),
                        'product_id' => $item->getProductId(),
                        'item_id'    => $item->getId()
                        ];
                    }
                }
            }

            if ($rule->getIsStopProcessing()) {
                break;
            }
        }

        return $result;
    }

    /**
     * Retrie numeber product points in collection
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function getProductCollectionPoints($collection, $products)
    {
        $catalogRulePoints = $this->loadCatalogRulePoints($collection);
        foreach ($catalogRulePoints as $rule) {
            foreach ($rule as $productId => $point) {
                if (isset($products[$productId])) {
                    $products[$productId] += $point;
                } else {
                    $products[$productId] = $point;
                }
            }
        }
        return $products;
    }
}
