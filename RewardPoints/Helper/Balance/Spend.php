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

use \Magento\Quote\Model\Quote;
use Lof\RewardPoints\Model\Spending;
use Lof\RewardPoints\Model\Config;

class Spend extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $spendingCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var array
     */
    protected $_productSpendingPoints;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $purchase;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context                            $context
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Magento\Framework\App\ResourceConnection                        $resource
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Magento\Framework\Pricing\Helper\Data                           $priceHelper
     * @param \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingCollectionFactory
     * @param \Lof\RewardPoints\Helper\Purchase                                $rewardsPurchase
     * @param \Lof\RewardPoints\Logger\Logger                                  $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                                   $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingCollectionFactory,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        parent::__construct($context);
        $this->storeManager              = $storeManager;
        $this->resource                  = $resource;
        $this->customerSession           = $customerSession;
        $this->priceHelper               = $priceHelper;
        $this->taxData                   = $taxData;
        $this->spendingCollectionFactory = $spendingCollectionFactory;
        $this->rewardsPurchase           = $rewardsPurchase;
        $this->rewardsLogger             = $rewardsLogger;
        $this->rewardsConfig             = $rewardsConfig;
    }

    /**
     * @return \Lof\RewardPoints\Model\Config
     */
    public function getConfig()
    {
        return $this->rewardsConfig;
    }

    public function getPointLabel($rule, $storeId = 0) {
        $action = $rule['action'];
        $message = __('For %1 points give %2 discount', $rule['spend_points'], $this->priceHelper->currencyByStore($rule['monetary_step'], $storeId));
        return $message;
    }

    public function getStore($storeId = '')
    {
        $store = $this->storeManager->getStore($storeId);
        return $store;
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

    public function getPurchase()
    {
        $purchase = $this->purchase;
        if (!$purchase || ($purchase && !$purchase->getId())) {
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

    public function getArrayProductSpendingPoints($products = ''){
        $store = $this->getStore();
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('lof_rewardpoints_product_spending_points');
        $stores = [0, $store->getId()];
        $select = $connection->select()->from(['main_table' => $tableName])
        ->where('main_table.store_id IN (?)', $stores);
        if (is_array($products) && $products) {
            $select->where('main_table.product_id IN (?)', $products);
        }

        $result = $connection->fetchAll($select);
        $productPoints = [];
        foreach ($result as $k => $v) {
            $productId = $v['product_id'];
            if(isset($productPoints[$productId])){
                if($productPoints[$productId] == 0) continue;
                $productPoints[$productId] = $v;
            } else {
                $productPoints[$productId] = $v;
            }
        }
        return $productPoints;
    }

    public function getProductSpendingPoints($products = '', $isSingeProduct = false)
    {
        if(!$this->_productSpendingPoints){
            $productPoints = $this->getArrayProductSpendingPoints($products);
            $this->_productSpendingPoints = $productPoints;
        }
        if($isSingeProduct && !is_array($products) && $this->_productSpendingPoints){
            $productPoints = $this->getArrayProductSpendingPoints([$products]);
            foreach ($productPoints as $k => $v) {
                if($k == $products){
                    $this->_productSpendingPoints[$k] = $v;
                    return $v["points"];
                }
            }
            return false;
        }
        return $this->_productSpendingPoints;
    }

    public function getCustomer()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer;
    }

    /**
     * Get rule by current store, current customer group id
     * @param  int|null $store
     * @param  int|null $customerGroupId
     * @return Lof\RewardPoints\Model\ResourceModel\Earning\Collection
     */
    public function getRules($storeId = null, $customerGroupId = null)
    {
        $collection      = $this->spendingCollectionFactory->create();
        $store           = $this->getStore();
        $storeId         = ($storeId !== null)?(int)$storeId:$store->getId();
        $customerGroupId = ($customerGroupId!==null)?(int)$customerGroupId:$this->getCustomer()->getGroupId();
        $collection->addStatusFilter()
        ->addDateFilter()
        ->addFieldToFilter('type', Spending::TYPE)
        ->addStoreFilter($storeId, false)
        ->addCustomerGroupFilter($customerGroupId);
        $collection->getSelect()
        ->order('main_table.sort_order asc')
        ->order('main_table.rule_id DESC');
        return $collection;
    }

    public function getSpendingRatePoints($quote = null)
    {
        $quote    = $this->getQuote();
        $purchase = $this->getPurchase();
        $params   = $purchase->getParams();
        $result   = [];
        if (isset($params[Config::SPENDING_RATE]['rules'])) {
            $result = $params[Config::SPENDING_RATE]['rules'];
        }
        $spendingRates = $this->getRules();
        foreach ($spendingRates as $rule) {
           if( $rule->getIsStopProcessing() ) {
                $spendingRates->addFieldToFilter('rule_id', $rule->getId());
                break;
           }
        }
        $collection    = $quote->getAllVisibleItems();
        $cartItems     = [];
        foreach ($collection as $item) {
            $cartItems[strtolower($item->getSku())] = $item->getQty();
        }

        foreach ($spendingRates as $rate) {
            if (!isset($result[$rate->getId()])) {
                $result[$rate->getId()] = [
                    'items' => $cartItems,
                    'status' => 0,
                    'points' => 0
                ];
            }
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
                $params[Config::SPENDING_RATE]['rules'] = $this->getSpendingRatePoints();
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

    public function getLimitedSubtotal($quote)
    {
        $subtotal = 0;
        $priceIncludesTax = $this->isIncludeTax($quote);
        foreach ($quote->getItemsCollection() as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            //$subtotal += $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
            if($priceIncludesTax && $item->getBasePriceInclTax()){
                $subtotal += $item->getBasePriceInclTax() * $item->getQty() - $item->getBaseDiscountAmount();
            }else {
                $subtotal += $item->getBasePrice() * $item->getQty() - $item->getBaseDiscountAmount();
            }

        }
        if($this->getConfig()->isSpendPointsFromShipping()){
            $address = $quote->getShippingAddress();
            $address->collectShippingRates();
            $shippingAmount = 0;
            foreach ($address->getAllShippingRates() as $rate) {
                $shippingAmount += $rate->getPrice();
            }
            $subtotal += $shippingAmount;
        }
        $subtotal = ceil($subtotal);
        return $subtotal;
    }

    /**
     * Check if product prices inputed include tax
     *
     * @return bool
     */
    public function isIncludeTax($quote)
    {
        if ($this->getConfig()->isEarnPointsFromTax()) {
            //$priceIncludesTax = $this->taxData->priceIncludesTax($quote->getStore());
            //return $priceIncludesTax;
            return true;
        }
        return false;
    }
}
