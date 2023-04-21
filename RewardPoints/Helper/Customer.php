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

namespace Lof\RewardPoints\Helper;

use \Magento\Sales\Model\Order;
use Lof\RewardPoints\Model\Config;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Model\Purchase as RewardsPurchase;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Lof\RewardPoints\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var boolean
     */
    protected $forceSave = false;

    /**
     * @var Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var Lof\RewardPoints\Model\Purchase
     */
    protected $purchase;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    protected $_customers = [];

    /**
     * @param \Magento\Framework\App\Helper\Context                               $context
     * @param \Magento\Framework\Message\ManagerInterface                         $messageManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                     $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface                          $storeManager
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory    $customerCollectionFactory
     * @param \Lof\RewardPoints\Model\CustomerFactory                             $customerFactory
     * @param \Lof\RewardPoints\Helper\Purchase                                   $rewardsPurchase
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $dateTime
     * @param \Lof\RewardPoints\Helper\Balance\Spend                              $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Helper\Balance\Earn                               $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Data                                       $rewardsData
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Lof\RewardPoints\Model\CustomerFactory $customerFactory,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($context);
        $this->messageManager               = $messageManager;
        $this->productRepository            = $productRepository;
        $this->storeManager                 = $storeManager;
        $this->dateTime                     = $dateTime;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->customerCollectionFactory    = $customerCollectionFactory;
        $this->purchaseCollectionFactory    = $purchaseCollectionFactory;
        $this->customerFactory              = $customerFactory;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsBalanceSpend          = $rewardsBalanceSpend;
        $this->rewardsBalanceEarn           = $rewardsBalanceEarn;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsLogger                = $rewardsLogger;
    }

    /**
     * @return Lof\RewardPoints\Model\Purchase
     */
    public function getPurchase()
    {
        $purchase = $this->purchase;
        if (!$purchase) {
            $purchase = $this->rewardsPurchase->getPurchase();
        }
        return $purchase;
    }

    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;
        return $this;
    }

    public function getForceSave()
    {
        return $this->forceSave;
    }

    public function setForceSave($status)
    {
        $this->forceSave = $status;
        return $this;
    }

    public function getCustomer($customerId = '', $params = '')
    {
        if (!$customerId || $customerId == '') {
            $customerId = $this->rewardsData->getCustomer()->getId();
        }

        if (!$customerId) {
            return;
        }
        if (!isset($this->_customers[$customerId])) {
            $customer = $this->customerCollectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->getFirstItem();

            // Create new customer if not exit
            if (!$customer->getObjectId() && $customerId) {
                $customer = $this->customerFactory->create();
                $customer->setHoldPoints(0)
                    ->setUpdatePointNotification(1)
                    ->setExpirePointNotification(1)
                    ->setCustomerId($customerId);
                $customer->save();
            }
            // Set customer discount params
            if (is_array($params) && $params) {
                $customerParams = $customer->getParams();
                foreach ($params as $quoteId => $points) {
                    $customerParams[$quoteId] = $points;
                }
                $customer->setParams($customerParams);
                $customer->refreshPoints();

                // Must be used only if we full 100% sure that it will be called once
                //$forceSave = $this->getForceSave();
                //if (!$forceSave) {
                //$customer->save();
                //}
            }
            $this->_customers[$customerId] = $customer;
        }
        return $this->_customers[$customerId];
    }

    public function getCustomerBalancePoints($customerId, $checkDate = false)
    {
        $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', Transaction::STATE_COMPLETE);
        if ($checkDate) {
            $today = $this->rewardsData->getTimezoneDateTime();
            $collection->addFieldToFilter("apply_at", [
                [
                    "lteq" => $today
                ],
                [
                    "null" => true
                ]
            ]);
            $collection->addFieldToFilter("expires_at", [
                [
                    "gteq" => $today
                ],
                [
                    "null" => true
                ]
            ]);
        }
        $totalPoints = 0;
        foreach ($collection as $_transaction) {
            $totalPoints += $_transaction->getAmount();
        }

        // Debug
        if ($totalPoints < 0) {
            $this->rewardsLogger->addError('Customer total points is smaller than 0');
            $totalPoints = 0;
        }
        return $totalPoints;
    }

    public function processSpendingProductPoints($productId, $sku, $qty, $itemId)
    {
        $result['qty']    = $qty;
        $result['status'] = true;
        $errorItem        = 0;
        $sku              = strtolower($sku);
        /**
         * Rewards Purchase
         */
        $purchase       = $this->getPurchase();
        $purchaseParams = $purchase->getParams();
        $spendingProductPoints = $this->rewardsBalanceSpend->setPurchase($purchase)->getProductSpendingPoints($productId, true);
        $spendingProductPoints = is_array($spendingProductPoints) && $spendingProductPoints && isset($spendingProductPoints[$productId]) ? $spendingProductPoints[$productId] : $spendingProductPoints;
        if ($spendingProductPoints && $this->rewardsData->isLoggedIn()) {
            $product  = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());

            /**
             * Quote
             */
            $quote = $this->rewardsData->getQuote();
            $items = $quote->getItemsCollection();
            /**
             * Rewards Customer
             */
            $customer = $this->getCustomer();
            $customerId = $this->rewardsData->getCustomer()->getId();
            //$availablePoints        = $this->getCustomerBalancePoints($customerId);

            $availablePoints = $customer->getAvailablePoints(); //$customer->getAvailablePoints();
            $transaction = $this->transactionExpired($customerId);
            $total = 0;
            foreach ($transaction as $item) {
                $total += $item->getAmount();
            }
            if (isset($purchaseParams[Config::SPENDING_PRODUCT_POINTS]['points'])) {
                $availablePoints += $purchaseParams[Config::SPENDING_PRODUCT_POINTS]['points'];
            }
            /**
             * Rebuild SPENDING_PRODUCT_POINTS
             */
            $productIds = [];
            foreach ($items as $item) {
                $productIds[] = $item->getProductId();
            }
            $spendProducts     = $this->rewardsBalanceSpend->getProductSpendingPoints($productIds);
            $productCollection = $this->rewardsBalanceEarn->getProductCollection();
            $products = [];
            foreach ($productCollection as $_product) {
                $products[$_product->getId()] = $_product->getFinalPrice();
            }
            $spendProductPoints = isset($purchaseParams[Config::SPENDING_PRODUCT_POINTS]['items']) ? $purchaseParams[Config::SPENDING_PRODUCT_POINTS]['items'] : [];
            foreach ($items as $item) {
                $itemSku = strtolower($item->getSku());
                $_productId = $item->getProductId();
                foreach ($spendProducts as $itemId => $itemInfo) {
                    if ($itemSku != $sku) {
                        $itemPrice = $products[$_productId];
                    } else {
                        $itemPrice = $product->getPrice();
                    }
                    if (strtolower($itemInfo['sku']) == $itemSku) {
                        $spendProductPoints[$itemSku] = [
                            'qty'      => $item->getQty(),
                            'points'   => $spendingProductPoints,
                            'discount' => $itemPrice,
                            'item_id'  => (int) $item->getItemId()
                        ];
                        if ($itemSku != $sku) {
                            $availablePoints -= ($spendingProductPoints * $item->getQty());
                        }
                    }
                }
            }
            $newItem         = $spendProductPoints[$sku];
            $maxAvaiableItem = (float)($total / $newItem['points']);
            if ($newItem['qty'] >= $maxAvaiableItem) {
                if ($maxAvaiableItem) {
                    $spendProductPoints[$sku]['qty'] = $maxAvaiableItem;
                    $errorItem = ($newItem['qty'] - $maxAvaiableItem);
                    $result['qty'] = floor($maxAvaiableItem);
                } else {
                    $result['qty'] = 0;
                    unset($spendProductPoints[$sku]);
                }
            } else {
                $result['qty'] = $newItem['qty'];
            }
            $purchaseParams[Config::SPENDING_PRODUCT_POINTS]['items'] = $spendProductPoints;
            $purchase->setParams($purchaseParams);
            $purchase->refreshPoints();
            //if ($purchase->getQuoteId()) {
            //$purchase->save();
            //}
            /**
             * Update Customer Points
             */
            if ($errorItem || $qty > $result['qty']) {
                $result['status'] = false;
                $this->messageManager->addError(__('Your points have expired Or You don\'t have enough points to buy more quantiy of %1.', $product->getName()));
            }
            return $result;
        } else if ((!is_array($spendingProductPoints) && $spendingProductPoints > 0) || (is_array($spendingProductPoints) && count($spendingProductPoints) > 0)) {
            $this->messageManager->addError(__('This product can be purchased by points only. Need more points to get it.'));
            $result['status'] = false;
            $result['qty'] = 0;
            return $result;
        }
    }

    /**
     * process rule when add product to cart
     *
     * @param mixed $params
     * @param mixed $result
     * @param mixed $product
     * @return mixed
     */
    public function proccessRule($params, $result, $product)
    {
        if (is_string($result)) {
            return $result;
        }
        if (!isset($params['product']) || (isset($params['product']) && empty($params['product']))) {
            return $result;
        }
        $quote      = $this->rewardsData->getQuote();
        $collection = $quote->getAllVisibleItems();
        if (!isset($params['qty'])) $params['qty'] = 1;
        if ($tmp = $this->processSpendingProductPoints($params['product'], $result->getSku(), $params['qty'], $result->getId())) {
            if ($tmp['qty'] == 0) {
                foreach ($collection as $item) {
                    if ($item->getProductId() == $params['product']) {
                        $quote->deleteItem($item)->save();
                        break;
                    }
                }
            }
            $result = ($result->getParentItem() ? $result->getParentItem() : $result);
            $price = 0;
            $result->setCustomPrice($price);
            $result->setOriginalCustomPrice($price);
            $result->getProduct()->setIsSuperMode(true);
            $this->rewardsBalanceEarn->resetRatePoints($quote);

            if (!$tmp['status']) {
                $quote->setHasError(true);
            }
            $result->setQty($tmp['qty']);
        } else if ((isset($params['discount']) && (float) $params['discount'] > 0) && (isset($params['spendpoints']) && (float)$params['spendpoints'] > 0) && isset($params['rule'])) {
            if ($customer = $this->getCustomer()) {
                $this->_eventManager->dispatch(
                    'rewardpoints_add_to_cart',
                    [
                        'result'     => $result,
                        'collection' => $collection,
                        'params'     => $params,
                        'quote'      => $quote,
                        'customer'   => $customer,
                        'product'    => $product
                    ]
                );
            }
        } else {
            //Do Nothing
        }
        return $result;
    }

    public function refreshPurchaseAvailable($currenPurchaseId, $customerId)
    {
        try {
            $collection = $this->purchaseCollectionFactory->create()
                ->addFieldToFilter('purchase_id', ['neq' => $currenPurchaseId])
                ->addFieldToFilter('order_id', [
                    ['eq'   => 0],
                    ['null' => true]
                ])
                ->addFieldToFilter('spend_points', ['gt' => 0]);
            //foreach ($collection as $purchase) {
            //$purchase->setData('spend_points', 0)->save();
            //}
            if ($collection->count() > 0) {
                $customer = $this->getCustomer($customerId);
                if ($customer) {
                    $customer->setData('params', '')->refreshPoints()->save();
                }
            }
            return true;
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return false;
    }

    public function transactionExpired($customerId)
    {
        $todayDate = new \DateTime();
        $currentTime = $this->dateTime->gmtDate('Y-m-d h:m:s');
        $collection  = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('is_expired', 0)
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('expires_at', ['gt' => $todayDate]);
        return $collection;
    }
}
