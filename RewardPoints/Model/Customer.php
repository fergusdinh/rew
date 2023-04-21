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

namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Model\Purchase as RewadsPurchase;
use \Lof\RewardPoints\Model\Transaction;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    const AVAILABLE_POINTS = 'available_points';
    const HOLD_POINTS      = 'hold_points';
    const TOTAL_POINTS     = 'total_points';

    /**
     * Customer cache tag
     */
    const CACHE_TAG = 'rewardpoints_customer';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpoints_customer';

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context                                    $context
     * @param \Magento\Framework\Registry                                         $registry
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $dateTime
     * @param \Lof\RewardPoints\Model\ResourceModel\Customer|null                 $resource
     * @param \Lof\RewardPoints\Model\ResourceModel\Customer\Collection|null      $resourceCollection
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory    $purchaseCollectionFactory
     * @param \Lof\RewardPoints\Helper\Purchase                                   $rewardsPurchase
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Model\ResourceModel\Customer $resource = null,
        \Lof\RewardPoints\Model\ResourceModel\Customer\Collection $resourceCollection = null,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->purchaseCollectionFactory    = $purchaseCollectionFactory;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsLogger                = $rewardsLogger;
        $this->dateTime                     = $dateTime;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Customer');
    }

    public function getParams($code = '')
    {
        $params = [];
        if ($this->getData('params')) {
            $params = unserialize($this->getData('params'));
        }
        if ($code) {
            foreach ($params as $k => $v) {
                if ($k == $code) {
                    return $params[$code];
                }
            }
        }
        return $params;
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

    public function refreshPoints()
    {
        $this->refreshTotalPoints();
        $this->refreshAvaiablePoints();
        $this->refreshHoldPoints();
        return $this;
    }

    public function refreshTotalPoints()
    {
        $totalPoints = 0;
        $collection = $this->transactionCollectionFactory->create()
        ->addFieldToFilter('customer_id', $this->getCustomerId());
        $collection->getSelect()->columns(['total_amount' => new \Zend_Db_Expr('SUM(amount)')]);
        $collection->setOrder("transaction_id", "DESC");
        $collection->addFieldToFilter('status', ['in' => [Transaction::STATE_COMPLETE, Transaction::SPENDING_CLOSED]]);
        /*if($collection->getSize()) {
            $first_item = $collection->getFirstItem();
            foreach ($collection as $_transaction) {
                $amount = (float) $_transaction->getAmount() - (float) $_transaction->getAmountUsed();
                $this->rewardsLogger->addError(__('amount = ').$amount);
                $totalPoints += $amount;
            }
            if($first_item && (0 > $first_item->getAmount())){
                $totalPoints -= (float)$first_item->getAmount();
            }
            $this->rewardsLogger->addError(__('totalPoints = ').$totalPoints);
        }*/
        if($collection->getSize()) {
            $item = $collection->getFirstItem();
            $totalPoints = $item->getTotalAmount();
        }

        $this->setTotalPoints($totalPoints);
        return $this;
    }

    public function getSpendPoints()
    {
        $totalPoints = (float) $this->getTotalPoints();
        $spendPoints = 0;
        $params      = $this->getParams();
        if (isset($params[RewadsPurchase::DISCOUNT]) && is_array($params[RewadsPurchase::DISCOUNT])) {
            foreach ($params[RewadsPurchase::DISCOUNT] as $k => $points) {
                $spendPoints += (float) $points;
            }
        }
        return $spendPoints;
    }

    public function refreshAvaiablePoints()
    {
        $totalPoints               = (float) $this->getTotalPoints();
        $purchaseCollectionFactory = $this->purchaseCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToFilter('order_id', [
                ['eq' => NULL],
                ['eq' => 0],
                ]);
        $spendPoints = 0;
        foreach ($purchaseCollectionFactory as $purchase) {
            $spendPoints += $purchase->getSpendPoints();
        }

        $params = $this->getParams();
        if (is_array($params)) {
            $bug = '';
            try {
                foreach ($params as $k => $v) {
                    $spendPoints += $v;
                }
            } catch (\Exception $e) {
                $this->rewardsLogger->addError($bug);
            }
        }

        // Debug Log
        if ($spendPoints > $totalPoints) {
            $this->rewardsLogger->addError(__('Bugs5: Total Points is bigger than spend points'));
            $avaiblePoints = 0;
        } else {
            $avaiblePoints = (float) ($totalPoints - $spendPoints);
        }
        $this->setAvailablePoints($avaiblePoints);
        return $this;
    }

    public function getExpiresCollection()
    {
        $transactions = $this->transactionCollectionFactory->create()
        ->addFieldToFilter('customer_id', $this->getCustomerId())
        ->addFieldToFilter('is_expired', [
            ['eq'=>0],
            ['null' => true]
        ]);

        $transactions->getSelect()->where('expires_at > NOW()')->order('ABS( DATEDIFF( expires_at, NOW() ) ) ASC');
        $transaction = $transactions->getFirstItem();
        $expireAt    =  $this->dateTime->date('Y-m-d', $transaction->getExpiresAt());


        if ($expireAt) {
            $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('status', ['eq' => Transaction::STATE_COMPLETE])
            ->addFieldToFilter('customer_id', $this->getCustomerId())
            ->addFieldToFilter('is_expired', [
                ['eq'=>0],
                ['null' => true]
            ])
            ->addFieldToFilter('expires_at', ['gt' =>  $expireAt])
            ->addFieldToFilter('expires_at', ['like' => '%' . $expireAt . '%']);
            return $collection;
        }
        return;
    }

    public function getExpirePointsInfo()
    {
        $result         = [];
        $days           = 0;
        $points         = 0;
        $collection     = $this->getExpiresCollection();
        $transactionIds = [];

        foreach ($collection as $transaction) {
            $points += (float)($transaction->getAmount() - $transaction->getAmountUsed());
            if ($points < 0) {
                $points = 0;
                break;
            }
            if (!$days && ($expires = $transaction->getData('expires_at'))) {
                $expires = $this->dateTime->date('Y-m-d H:i:s', $expires);
                $diff = strtotime($expires) - time();
                $days = (float) ($diff / 60 / 60 / 24);
                $transactionIds[] = $transaction->getId();
            }
        }

        if ($points > $this->getAvailablePoints()) {
            $points = $this->getAvailablePoints();
        }

        if (!$points) {
            return false;
        }

        $result['points'] = (float) $points;
        $result['days']   = (int) $days;
        $result['ids']    = $transactionIds;
        return $result;
    }

    public function refreshHoldPoints()
    {
        $holdPoints = $this->getHoldPoints();
        $this->setHoldPoints($holdPoints);
        return $this;
    }

    public function setTotalPoints($points) {
        if ($points < 0) {
            $points = 0;
        }
        $this->setData('total_points', $points);
        return $this;
    }

    public function setAvaiablePoints($points) {
        if ($points < 0) {
            $points = 0;
        }
        $this->setData('available_points', $points);
        return $this;
    }
    // -----------. API get total points
    public function getTotalCustomerPoints($customerId = null){
        $result = [];
        $connection = $this->getResourceCollection();
        $data = $connection->addFieldToFilter('customer_id', $customerId)->getFirstItem()->getData();
        if($data){
            $result = [
                "total_point" => $data["total_points"]
            ];
        }
        return json_encode($result,true);
    }

    public function getTotalSpentPoint(){
        $result = [];
        if($_GET["customer_id"] && ($_GET["qoute_id"] || $_GET["quote_id"] || $_GET["order_id"])){
            if ($_GET["qoute_id"] || $_GET["quote_id"]) {
                $data = $this->purchaseCollectionFactory->create()->addFieldToFilter('quote_id', $_GET["qoute_id"])->getFirstItem();
            }else{
                $data = $this->purchaseCollectionFactory->create()->addFieldToFilter('order_id', $_GET["order_id"])->getFirstItem();
            }
            if($data){
                $result = [
                    "spend_points" => $data["spend_points"],
                    "spend_cart_points" => $data["spend_cart_points"],
                    "spend_catalog_points" => $data["spend_catalog_points"]
                ];
            }
        }
        return json_encode($result, true);

    }
     public function getOrderEarnSpentPoints(){
        $result = [];
        if(isset($_GET["quote_id"]) || $_GET["quote_id"] || isset($_GET["order_id"])){
            if ($_GET["qoute_id"] || $_GET["quote_id"]) {
                $data = $this->purchaseCollectionFactory->create()->addFieldToFilter('quote_id', $_GET["qoute_id"])->getFirstItem();
            }else{
                $data = $this->purchaseCollectionFactory->create()->addFieldToFilter('order_id', $_GET["order_id"])->getFirstItem();
            }
            if($data) {
                $result = [
                    "spend_points" => $data["spend_points"],
                    "spend_cart_points" => $data["spend_cart_points"],
                    "spend_catalog_points" => $data["spend_catalog_points"],
                    "earn_points" => $data["earn_points"],
                    "earn_cart_points" => $data["earn_cart_points"],
                    "earn_catalog_points" => $data["earn_catalog_points"]
                ];
            }
        }
        return json_encode($result, true);

    }
    // ================
}
