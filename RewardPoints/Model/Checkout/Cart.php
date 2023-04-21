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

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @param \Magento\Framework\Event\ManagerInterface            $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart           $resourceCart
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param \Magento\Framework\Message\ManagerInterface          $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface    $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface           $quoteRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface      $productRepository
     * @param \Lof\RewardPoints\Helper\Customer                    $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Purchase                    $rewardsPurchase
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        array $data = []
        ) {
    	parent::__construct(
            $eventManager,
            $scopeConfig,
            $storeManager,
            $resourceCart,
            $checkoutSession,
            $customerSession,
            $messageManager,
            $stockRegistry,
            $stockState,
            $quoteRepository,
            $productRepository,
            $data
        );
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsPurchase = $rewardsPurchase;
    }

    /**
     * {@inheritdoc}
     */
    public function addProduct($productInfo, $requestInfo = null)
    {
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();

        if ($productId) {
            $request = $this->getQtyRequest($product, $requestInfo);
            try {
                $this->_eventManager->dispatch(
                    'checkout_cart_product_add_before',
                    ['info' => $requestInfo, 'product' => $product]
                );
                $result = $this->getQuote()->addProduct($product, $request);
                /**
                 * Start Custom Code Lof_RewardPoints
                 */
                $purchase = $this->rewardsPurchase
                                ->getPurchase()
                                ->verifyPointsWithCartItems()
                                ->save();
                $result   = $this->rewardsCustomer
                                ->setPurchase($purchase)
                                ->proccessRule($requestInfo, $result, $product);
                /**
                 * End Custom Code
                 */
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Get request quantity
     *
     * @param Product $product
     * @param \Magento\Framework\DataObject|int|array $request
     * @return int|DataObject
     */
    private function getQtyRequest($product, $request = 0)
    {
        $request = $this->_getProductRequest($request);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minimumQty = $stockItem->getMinSaleQty();
        //If product quantity is not specified in request and there is set minimal qty for it
        if ($minimumQty
            && $minimumQty > 0
            && !$request->getQty()
        ) {
            $request->setQty($minimumQty);
        }

        return $request;
    }


    /**
     * {@inheritdoc}
     */
    public function updateItems($data)
    {
        $customer        = $this->rewardsCustomer->getCustomer();
        $purchase        = $this->rewardsPurchase->getPurchase();
        $purchaseParams  = $purchase->getParams();
        if ($customer && $customer->getId()) {
            $availablePoints = $customer->getAvailablePoints();
        } else {
            // No logged
            $availablePoints = 99999999;
        }

        if (isset($purchaseParams[Config::SPENDING_PRODUCT_POINTS]['points'])) {
            $availablePoints += $purchaseParams[Config::SPENDING_PRODUCT_POINTS]['points'];
        }

        $params             = $purchase->getParams();
        $quote              = $this->getQuote();
        $itemsCollection    = $quote->getItemsCollection();

        if (isset($params[Config::SPENDING_PRODUCT_POINTS]['items']) && $availablePoints > 0) {
            $items       = $params[Config::SPENDING_PRODUCT_POINTS]['items'];
            $showMessage = false;
            foreach ($data as $itemId => $item) {
                foreach ($items as $sku => $_item) {

                    // Get item id if empty
                    if (!$_item['item_id']) {
                        foreach ($itemsCollection as $cartItem) {
                            if (strtolower($cartItem->getSku()) == $sku) {
                                $_item['item_id'] = $cartItem->getItemId();
                                break;
                            }
                        }
                    }
                    if (isset($_item['item_id']) && $_item['item_id'] == $itemId && $_item['points']) {
                        $maxAvaiableItem = (float)($availablePoints / $_item['points']);
                        if ($maxAvaiableItem) {
                            if ($item['qty'] <= $maxAvaiableItem) {
                                $_item['qty'] = $item['qty'];
                            } else {
                                $_item['qty'] = floor($maxAvaiableItem);
                                $showMessage = true;
                            }
                            // Calculator cart item qty
                            $data[$itemId]['qty'] = $_item['qty'];
                            $availablePoints -= ($_item['points'] * $_item['qty']);
                            $items[$sku] = $_item;
                        }
                    }
                }
            }

            if ($showMessage) {
                $this->messageManager->addError(__('You don\'t have enough points to buy more quantiy.'));
            }

            /**
             * Update Purchase Points
             */
            $params[Config::SPENDING_PRODUCT_POINTS]['items'] = $items;
            $purchase->setParams($params);
            $purchase->refreshPoints();
            $purchase->save();
        }
        return parent::updateItems($data);
    }
}
