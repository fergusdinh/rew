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

use Lof\RewardPoints\Model\Config;

class Purchase extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Lof\RewardPoints\Model\PurchaseFactory
     */
    protected $purchaseFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Framework\App\Helper\Context                            $context
     * @param \Magento\Quote\Api\CartRepositoryInterface                       $quoteRepository
     * @param \Magento\Framework\Registry                                      $registry
     * @param \Lof\RewardPoints\Model\PurchaseFactory                          $purchaseFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory
     * @param \Lof\RewardPoints\Helper\Data                                    $rewardsData
     * @param \Lof\RewardPoints\Logger\Logger                                  $rewardsLogger
     * @param \Magento\Quote\Model\QuoteFactory                                $mageQuoteFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Registry $registry,
        \Lof\RewardPoints\Model\PurchaseFactory $purchaseFactory,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Magento\Quote\Model\QuoteFactory $mageQuoteFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry             = $registry;
        $this->quoteRepository           = $quoteRepository;
        $this->purchaseFactory           = $purchaseFactory;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->rewardsData               = $rewardsData;
        $this->rewardsLogger             = $rewardsLogger;
        $this->mageQuoteFactory          = $mageQuoteFactory;
    }

    public function getByOrder($order, $forceRefresh = false)
    {
        try{
            $quote = $this->mageQuoteFactory->create()->load($order->getQuoteId());
            //$quote = $this->quoteRepository->get($order->getQuoteId());
            if($quote) {
                $purchase = $this->getByQuote($quote);
                if (!$purchase->getOrderId()) {
                    $purchase->setOrderId($order->getId());
                }
                if ($forceRefresh) {
                    $purchase->refreshPoints();
                    $purchase->save();
                }
                return $purchase;
            }
        } catch(\Exception $e) {

        }
        return false;
    }

    public function getPurchase($quote = '')
    {
        if (!$quote) {
            $quote = $this->rewardsData->getQuote();
        }

        $purchase = $this->purchaseCollectionFactory->create()
        ->addFieldToFilter('quote_id', $quote->getId())
        ->getFirstItem();

        if (!$purchase->getId()) {
            $purchase = $this->purchaseFactory->create();
        }

        if ($customerId = $quote->getCustomer()->getId()){
            $purchase->setCustomerId($customerId);
        }

        if (!$purchase->getQuoteId() && $quote->getId()) {
            $purchase->setQuoteId($quote->getId());
            $purchase->refreshPoints();
            $purchase->save();
        }

        return $purchase;
    }

    public function refreshPoints($quote)
    {
        $purchase = $this->purchaseCollectionFactory->create()->addFieldToFilter('quote_id', $quote->getId())->getFirstItem();
        if (!$purchase->getId()) {
            $purchase = $this->purchaseFactory->create();
            $purchase->resetFullData();
        }
        $purchase->setQuoteId($quote->getId());
    }

    public function getProductPoints($productId)
    {
        $points = 0;
        $purchase = $this->getPurchase();
        $params = $purchase->getParams();
        if (isset($params[Config::EARNING_CATALOG_RULE]['rules'])) {
            $earningCatalogRules = $params[Config::EARNING_CATALOG_RULE]['rules'];
            foreach ($earningCatalogRules as $ruleId => $rule) {
                foreach ($rule['items'] as $id => $item) {
                    if($id == $productId) {
                        $points += $item['points'];
                    }
                }
            }
        }
        return $points;
    }

    public function getByQuote($quote)
    {
        $purchase = $this->purchaseCollectionFactory->create()->addFieldToFilter('quote_id', $quote->getId())->getFirstItem();
        if (!$purchase->getId()) {
            $purchase = $this->purchaseFactory->create();
            $purchase->resetFullData();
            $purchase->setQuoteId($quote->getId());
        }
        return $purchase;
    }

    public function getCurrentPurchase()
    {
        $purchase = $this->_coreRegistry->registry('rewards_purchase');
        return $purchase;
    }

    public function resetRewardsPurchase()
    {
        $collection = $this->purchaseCollectionFactory->create()
        ->addFieldToFilter('quote_id', [
            ['eq' => 0],
            ['null' => true]
        ])
        ->addFieldToFilter('customer_id', [
            ['eq' => 0],
            ['null' => true]
        ])
        ->addFieldToFilter('spend_points', [
            ['gteq' => 0],
            ['null' => true]
        ]);
        foreach ($collection as $purchase) {
            $purchase->delete();
        }
    }
}
