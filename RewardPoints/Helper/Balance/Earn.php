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

namespace Lof\RewardPoints\Helper\Balance;

use Lof\RewardPoints\Model\Config as RewardsConfig;
use Lof\RewardPoints\Model\Earning;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;

class Earn extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var CheckoutSession */
    protected $checkoutSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * \Magento\Catalog\Model\Config
     * @var [type]
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $earningRuleCollectionFactory;

    /**
     * @var array
     */
    protected $earingProductsPoints;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var Lof\RewardPoints\Model\Purchase
     */
    protected $purchase;
    /**
     * @var Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     */
    protected $quoteItem;

    private $productRepository;
    /**
     * @param \Magento\Framework\App\Helper\Context                           $context
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility                       $catalogProductVisibility
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     * @param \Magento\Catalog\Model\Config                                   $catalogConfig
     * @param \Magento\Tax\Helper\Data                                        $taxHelper
     * @param \Magento\Framework\Pricing\Helper\Data                          $priceHelper
     * @param \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory
     * @param \Lof\RewardPoints\Helper\Data                                   $rewardsData
     * @param \Lof\RewardPoints\Helper\Purchase                               $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Balance\Spend                          $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Logger\Logger                                 $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                                  $rewardsConfig
     * @param \Magento\Checkout\Model\Session as CheckoutSession              $checkoutSession
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                 $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        CheckoutSession $checkoutSession,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->storeManager                 = $storeManager;
        $this->catalogProductVisibility     = $catalogProductVisibility;
        $this->resource                     = $resource;
        $this->productCollectionFactory     = $productCollectionFactory;
        $this->catalogConfig                = $catalogConfig;
        $this->taxHelper                    = $taxHelper;
        $this->priceHelper                  = $priceHelper;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsBalanceSpend          = $rewardsBalanceSpend;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsConfig                = $rewardsConfig;
        $this->checkoutSession = $checkoutSession;
        $this->quoteItem = $quoteItem;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrive rule point label in UI grid
     * @param  array $rule
     * @param int $storeId
     * @param bool $is_referred
     * @return string
     */
    public function getPointLabel($rule, $storeId = 0, $is_referred = false)
    {
        if ($is_referred) {
            $earn_points = $rule['referred_points'];
        } else {
            $earn_points = $rule['earn_points'];
        }
        switch ($rule['action']) {
            case Earning::ACTION_AMOUNT_SPENT:
                $message = __('Give %1 points for each %2', $earn_points, $this->priceHelper->currencyByStore($rule['monetary_step'], $storeId));
                break;

            case Earning::ACTION_PERCENTAGE_BY_FINALPOINT_GIVE:
                $message = __('Give %1 points by final price', $earn_points);
                break;

            case Earning::ACTION_PERCENTAGE_BY_PRODUCT_PRICE:
                $message = __('Give %1% points of orginal price', $earn_points);
                break;

            case Earning::ACTION_PERCENTAGE_BY_CARTTOTAL:
                $message = __('Give %1% points of cart total', $earn_points);
                break;

            case Earning::ACTION_BY_CART_QTY:
                $message = __('Give %1 points for every %2 qty', $earn_points, $rule['qty_step']);
                break;

            default:
                $message = __('Give %1 points to customer', $earn_points);
                break;
        }
        return $message;
    }

    /** ----------------------------- BACKEND ----------------------------- */

    public function setCustomerGroupId($customerGroupId)
    {
        $this->customerGroupId = $customerGroupId;
        return $this;
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

    public function setStore($store)
    {
        if (is_numeric($store)) {
            $store = $this->storeManager->getStore($store);
        }
        $this->store = $store;
        return $this;
    }

    public function getStore($storeId = '')
    {
        if (!$this->store) {
            if ($this->getQuote()) {
                $storeId = $this->getQuote()->getStoreId();
            }
            $this->store = $this->storeManager->getStore($storeId);
        }
        return $this->store;
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        if ($this->getQuote()) {
            $customerGroupId = $this->getQuote()->getCustomer()->getGroupId();
        } else {
            $customerId = $this->rewardsData->getCustomer()->getId();
            if ($customerId) {
                $customerGroupId = $this->rewardsData->getCustomer()->getGroupId();
            } else {
                $customerGroupId = 0;
            }
        }
        return $customerGroupId;
    }

    /**
     * Check if product prices inputed include tax
     *
     * @return bool
     */
    public function isIncludeTax()
    {
        if ($this->rewardsConfig->isEarnPointsFromTax()) {
            $store            = $this->getStore();
            $priceIncludesTax = $this->taxHelper->priceIncludesTax($store);
            return $priceIncludesTax;
        }
        return false;
    }

    /**
     * Get rule by store && customer group id
     * @param  string|null $type
     * @param  int|null $storeId
     * @param  int|null $customerGroupId
     * @return Lof\RewardPoints\Model\ResourceModel\Earning\Collection
     */
    public function getRules($type = '', $storeId = null, $customerGroupId = null)
    {
        $storeId         = ($storeId !== null) ? (int)$storeId : $this->getStore()->getStoreId();
        $customerGroupId = ($customerGroupId!=null) ? (int)$customerGroupId : $this->getCustomerGroupId();
        $collection = $this->earningRuleCollectionFactory->create();
        if ($type) {
            $collection->addFieldToFilter('type', $type);
        }
        $collection->addStatusFilter()
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
     * @return Lof\RewardPoints\Model\Purchase
     */
    public function getPurchase()
    {
        $purchase = $this->purchase;
        if (!$purchase || !$purchase->getId()) {
            $quote    = $this->getQuote();
            $purchase = $this->rewardsPurchase->getPurchase($quote);
        }
        return $purchase;
    }

    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    /**
     * [getProductPoints description]
     * @param  Product $product
     * @param  \Lof\RewardsPoints\Model\Earning
     * @return int
     */
    public function getProductPointsByRule(Product $product, $rule)
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $quote = $this->quoteItem->create();
        $q = $quote->addFieldToFilter('quote_id', $quoteId);
        $q->getData();
        $qty = isset($q->getData()[0]) ? $q->getData()[0]['qty'] : 0;
        $points       = 0;
        $action       = $rule->getAction();
        $earnPoints   = (float) $rule->getEarnPoints();
        $monetaryStep = (float) $rule->getMonetaryStep();
        $pointsLimit  = (float) $rule->getPointsLimit();

        // Get price discount, final price
        $finalPrice   = ((float)$product->getMinimalPrice($qty)) ? (float)$product->getMinimalPrice($qty) : (float) $product->getFinalPrice($qty);
        $priceInclTax = $this->taxHelper->getShippingPrice($finalPrice, true);
        $priceDiscount =  (float)isset($q->getData()[0]) ? $q->getData()[0]['discount_amount'] : 0;
        $finalDiscountPrice = $finalPrice-$priceDiscount;
        $priceExclTax = $this->taxHelper->getShippingPrice($finalPrice);

        if ($this->isIncludeTax()) {
            $finalPrice = $priceInclTax;
        } else {
            $finalPrice = $priceExclTax;
        }

        switch ($action) {
            case Earning::ACTION_GIVE :
                $points = $earnPoints;
                break;

            case Earning::ACTION_PERCENTAGE_BY_PRODUCT_PRICE:
                if ($earnPoints > 100) {
                    $earnPoints = 100;
                }
                $points = ($finalPrice / 100) * $earnPoints;
                if ($pointsLimit && ($points > $pointsLimit)) {
                    $points = $pointsLimit;
                }
                break;
            case Earning::ACTION_PERCENTAGE_BY_FINALPOINT_GIVE:
                if ($finalPrice >= $earnPoints) {
                    $points = $earnPoints;
                }
                break;
            case Earning::ACTION_AMOUNT_SPENT:
                if ($monetaryStep) {
                    $tier_pice = (float)$product->getTierPrice($qty, $product);
                    if (isset($tier_pice)) {
                        $steps  = (float) ($tier_pice / $monetaryStep);
                        $amount = $steps * $earnPoints;
                        $points = $amount;
                    }
                    if ($priceDiscount!=0) {
                        $steps  = (float) ($finalDiscountPrice / $monetaryStep);
                        $amount = $steps * $earnPoints;
                        if ($pointsLimit && ($amount > $pointsLimit)) {
                            $amount = $pointsLimit;
                        }
                        $points = $amount;
                    } else {
                        $steps  = (float) ($finalPrice / $monetaryStep);
                        $amount = $steps * $earnPoints;
                        if ($pointsLimit && ($amount > $pointsLimit)) {
                            $amount = $pointsLimit;
                        }
                        $points = $amount;
                    }
                }
                break;
        }

        // Debug
        if ($points<0) {
            $this->rewardsLogger->addError('1. Product points is smaller than 0');
            $points = 0;
        }

        $points = $this->rewardsData->getFormatEarningRuleNum($points);
        return $points;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $store = $this->getStore();
        $collection = $this->productCollectionFactory->create();
        //$collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addMinimalPrice()
        ->addFinalPrice()
        ->addTaxPercents()
        ->addAttributeToSelect('*')
        ->addStoreFilter($store->getStoreId());
        return $collection;
    }

    /**
     * Retrie numeber product points in collection
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function getProductCollectionPoints($collection)
    {
        $products = $this->getProductEarningRatePoints($collection);
        $object = new \Magento\Framework\DataObject(['products' => $products]);
        $this->_eventManager->dispatch(
            'rewardpoints_product_collection_points',
            [
                'collection' => $collection,
                'products'   => $products,
                'obj'        => $object
            ]
        );
        $products = $object->getProducts();

        $earningPointsByProduct = $this->getProductEarningPoints();
        foreach ($earningPointsByProduct as $productId => $points) {
            // Debug
            if ($points<0) {
                $this->rewardsLogger->addError('2. Product points is smaller than 0');
                $points = 0;
            }
            $products[$productId] = $points;
        }
        return $products;
    }

    /**
     * @param  Product $product
     * @return int
     */
    public function getProductPoints(Product $product)
    {
        $productCollection = $this->getProductCollection()
        ->addFieldToFilter('entity_id', ['eq'=>$product->getId()]);
        $products = $this->getProductCollectionPoints($productCollection);

        $points = 0;
        if (isset($products[$product->getId()])) {
            $points = $products[$product->getId()];
        }

        // Debug
        if ($points<0) {
            $this->rewardsLogger->addError('3. Product points is smaller than 0');
            $points = 0;
        }

        $this->_eventManager->dispatch(
            'rewardpoints_product_points',
            [
                'product' => $product,
                'points'  => $points
            ]
        );
        return $points;
    }

    /**
     * Get product earning points
     * @return array
     */
    public function getProductEarningPoints()
    {
        $connection    = $this->resource->getConnection();
        $earningTable  = $this->resource->getTableName('lof_rewardpoints_product_earning_points');
        $spendingTable = $this->resource->getTableName('lof_rewardpoints_product_spending_points');
        $stores        = [0, $this->getStore()->getId()];
        $select = $connection->select()
                            ->from($earningTable)
                            ->where('product_id NOT IN (?)', $connection->select()->from($spendingTable, 'product_id')->where('store_id IN (?)', $stores))
                            ->where('store_id IN (?)', $stores);
        $productPoints = $connection->fetchAll($select);
        $products = [];
        foreach ($productPoints as $product) {
            $products[$product['product_id']] = $product['points'];
        }
        return $products;
    }

    /**
     * Get product earning points
     * @return array
     */
    public function getProductEarningPointsArr()
    {
        $quote    = $this->getQuote();
        $products = $this->getProductEarningPoints();
        $items    = $quote->getAllVisibleItems();
        $result   = [];

        // Multiple item id, but product id is same
        foreach ($products as $k => $v) {
            $available = false;
            $qty = 0;
            foreach ($items as $item) {
                if ($k == $item->getProductId() && $v) {
                    $result['items'][strtolower($item->getSku())] = [
                        'qty'        => $item->getQty(),
                        'points'     => $v,
                        'product_id' => $item->getProductId(),
                        'item_id'    => $item->getId()
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Get Product Earning Rate Points
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function getProductEarningRatePoints($collection)
    {
        $products = [];
        $rules    = $this->getRules(Earning::TYPE);
        foreach ($rules as $rule) {
            foreach ($collection as $product) {
                $productId = $product->getId();
                $points = $this->getProductPointsByRule($product, $rule);
                if (isset($products[$productId])) {
                    $products[$productId] += $points;
                } else {
                    $products[$productId] = $points;
                }
            }
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }
        return $products;
    }

    /**
     * Load points by collection
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return array
     */
    public function loadCatalogRatePoints($collection)
    {
        $result = [];
        $rules  = $this->getRules(Earning::TYPE);
        foreach ($rules as $rule) {
            $ruleId = $rule->getId();
            foreach ($collection as $product) {
                $productId = $product->getId();
                $point     = $this->getProductPointsByRule($product, $rule);
                if (isset($result[$ruleId][$productId])) {
                    $result[$ruleId][$productId] += $point;
                } else {
                    $result[$ruleId][$productId] = $point;
                }
            }
            if ($rule->getIsStopProcessing()) {
                break;
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCatalogRatePoints()
    {
        $quote                 = $this->getQuote();
        $productSpendingPoints = $this->rewardsBalanceSpend->getProductSpendingPoints();
        $productEarningPoints  = $this->getProductEarningPoints();
        $result                = [];

        try {
            $productIds = [];
            $parentProductIds = [];
            $collection = $quote->getAllVisibleItems();
            foreach ($collection as $item) {
                if($item->getProductType() == 'configurable'){
                    $sku = $item->getSku();
                    $product = $this->productRepository->get($sku);
                    if($product){
                        $productIds[] = $product->getId();
                        $parentProductIds[$product->getId()] = $item->getProductId();
                    }else {
                        $productIds[] = $item->getProductId();
                    }
                }else {
                    $productIds[] = $item->getProductId();
                }
            }
            $productCollection = $this->getProductCollection()
                                    ->addFieldToFilter('entity_id', ['in' => $productIds]);
            if (!empty($productSpendingPoints)) {
                $productCollection->addFieldToFilter('entity_id', ['nin' => array_keys($productSpendingPoints)]);
            }
            if (!empty($productEarningPoints)) {
                $productCollection->addFieldToFilter('entity_id', ['nin' => array_keys($productEarningPoints)]);
            }

            $rules = $this->loadCatalogRatePoints($productCollection);
            foreach ($collection as $item) {
                foreach ($rules as $ruleId => $rule) {
                    foreach ($rule as $productId => $points) {
                        $parent_product_id = isset($parentProductIds[$productId])?$parentProductIds[$productId]:$productId;
                        if ($item->getProductId() == $parent_product_id && $points) {
                            $result[$ruleId]['items'][strtolower($item->getSku())] = [
                                'points'     => $points,
                                'qty'        => $item->getQty(),
                                'product_id' => $parent_product_id,
                                'item_id'    => $item->getId()
                            ];
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log Error
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $result;
    }

    public function resetRatePoints()
    {
        try {
            $quote    = $this->getQuote();
            $purchase = $this->getPurchase();
            $params   = $purchase->getParams();
            if ($quote && $quote->getId()) {
                $object = new \Magento\Framework\DataObject(['params' => $params]);
                $this->_eventManager->dispatch(
                    'rewardpoints_reset_earning_points',
                    [
                            'obj'   => $object,
                            'quote' => $quote
                        ]
                );
                $params = $object->getParams();

                $params[RewardsConfig::EARNING_RATE]['rules']           = $this->getCatalogRatePoints();
                $params[RewardsConfig::EARNING_PRODUCT_POINTS]['rules'] = $this->getProductEarningPointsArr();
                $purchase->setQuote($quote)->setParams($params);
                try {
                    $purchase->refreshPoints();
                    // Reset when cart is empty
                    if (!$quote->getItemsCount()) {
                        $purchase->resetFullData();
                    }
                    if ($purchase->getQuoteId()) {
                        $purchase->save();
                    }
                } catch (\Exception $e) {
                    $this->rewardsLogger->addError($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $this;
    }
}
