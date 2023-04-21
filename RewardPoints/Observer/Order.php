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

namespace Lof\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

abstract class Order implements ObserverInterface
{
    protected $_cacheTypeList;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo
     */
    protected $creditmemo;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteCollectionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Order
     */
    protected $rewardsBalanceOrder;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param \Magento\Sales\Model\OrderFactory                          $orderFactory
     * @param \Magento\Sales\Model\Order\Creditmemo                      $creditmemo
     * @param \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface                 $quoteRepository
     * @param \Magento\Framework\ObjectManagerInterface                  $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param \Magento\Checkout\Model\Session                            $checkoutSession
     * @param \Lof\RewardPoints\Helper\Data                              $rewardsData
     * @param \Lof\RewardPoints\Helper\Balance\Order                     $rewardsBalanceOrder
     * @param \Lof\RewardPoints\Helper\Balance\Earn                      $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Balance\Spend                     $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Helper\Balance                           $rewardsBalance
     * @param \Lof\RewardPoints\Helper\Purchase                          $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Customer                          $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Mail                              $rewardsMail
     * @param \Lof\RewardPoints\Logger\Logger                            $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                             $rewardsConfig
     */
	public function __construct(
		\Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Creditmemo $creditmemo,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Balance\Order $rewardsBalanceOrder,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Lof\RewardPoints\Model\PurchaseFactory $purchaseFactory,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
	) {
        $this->coreRegistry                 = $coreRegistry;
        $this->orderFactory                 = $orderFactory;
        $this->creditmemo                   = $creditmemo;
        $this->quoteCollectionFactory       = $quoteCollectionFactory;
        $this->messageManager               = $messageManager;
        $this->objectManager                = $objectManager;
        $this->quoteRepository              = $quoteRepository;
        $this->storeManager                 = $storeManager;
        $this->checkoutSession              = $checkoutSession;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsBalanceOrder          = $rewardsBalanceOrder;
        $this->rewardsBalanceEarn           = $rewardsBalanceEarn;
        $this->rewardsBalanceSpend          = $rewardsBalanceSpend;
        $this->rewardsBalance               = $rewardsBalance;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsCustomer              = $rewardsCustomer;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsConfig                = $rewardsConfig;
        $this->purchaseFactory              = $purchaseFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->_cacheTypeList               = $cacheTypeList;
	}

    /**
     * @return \Lof\RewardPoints\Model\Config
     */
    public function getConfig()
    {
        return $this->rewardsConfig;
    }
}
