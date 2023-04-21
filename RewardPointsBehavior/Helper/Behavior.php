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
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Helper;

use Magento\Framework\Stdlib\DateTime;
use Lof\RewardPointsBehavior\Model\Earning;
use Lof\RewardPoints\Model\Transaction;

class Behavior extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var Lof\RewardPoints\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $_transactionCollection;

    /**
     * @var array
     */
    protected $_customerTransaction;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    protected $_order_id;

    /**
     * @param \Magento\Framework\App\Helper\Context                                   $context                      
     * @param \Magento\Framework\Message\ManagerInterface                             $messageManager               
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory        $customerCollectionFactory    
     * @param \Lof\RewardPointsBehavior\Model\Condition\Sql\BehaviorBuilder           $sqlBuilder                   
     * @param \Magento\Store\Model\StoreManagerInterface                              $storeManager                 
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                             $date                         
     * @param \Magento\Customer\Model\Session                                         $customerSession              
     * @param \Magento\Catalog\Model\ProductFactory                                   $productloader                
     * @param \Lof\RewardPointsBehavior\Helper\Data                                   $rewardsData                  
     * @param \Lof\RewardPoints\Helper\Balance                                        $rewardsBalance               
     * @param \Lof\RewardPoints\Helper\Mail                                           $rewardsMail                  
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory     $transactionCollectionFactory 
     * @param \Lof\RewardPointsBehavior\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory 
     * @param \Lof\RewardPoints\Logger\Logger                                         $rewardsLogger                
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPointsBehavior\Model\Condition\Sql\BehaviorBuilder $sqlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPointsBehavior\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($context);
        $this->messageManager               = $messageManager;
        $this->customerCollectionFactory    = $customerCollectionFactory;
        $this->sqlBuilder                   = $sqlBuilder;
        $this->storeManager                 = $storeManager;
        $this->date                         = $date;
        $this->customerSession              = $customerSession;
        $this->productloader                = $productloader;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsBalance               = $rewardsBalance;
        $this->rewardsMail                  = $rewardsMail;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->earningRuleCollectionFactory = $earningRuleCollectionFactory;
        $this->rewardsLogger                = $rewardsLogger;
    }

    protected function getTransactionByCustomerId($customerId)
    {
        if($this->_transactionCollection==''){
            $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customerId);
            $this->_transactionCollection = $collection;
            foreach ($collection as $transaction) {
                $behavior = $transaction->getAction();
                if(isset($this->_customerTransaction[$customerId][$behavior])){
                    $this->_customerTransaction[$customerId][$behavior] += $transaction->getAmount();
                } else {
                    $this->_customerTransaction[$customerId][$behavior] = $transaction->getAmount();
                }
            }
        }
        return $this->_transactionCollection;
    }

    protected function checkIsAllowToProcessRule($customerId, $code)
    {
        $collection = $this->getTransactionByCustomerId($customerId);
        foreach ($collection as $transaction) {
            if($transaction->getCode()==$code){
                return false;
            }
        }
        return true;
    }
    protected function getCurrentReferTransaction($customerId)
    {
        $orderId = $this->getOrderId();
        if($orderId){
            $collection = $this->getTransactionByCustomerId($customerId);
            foreach ($collection as $transaction) {
                if($transaction->getOrderId()==$orderId){
                    return $transaction->getId();
                }
            }
        }
        return null;
    }
    public function setOrderId($order_id = 0){
        $this->_order_id = $order_id;
        return $this;
    }
    public function getOrderId(){
        return $this->_order_id;
    }
    /**
     * The function process Behaviour Rule
     * @param  string $behavior
     * @param  mixed $customer
     * @param  string $code
     * @param bool $showMessage
     * @param string $status
     * @param bool $isReferred
     * @param int $current_orders_qty
     * @param bool $isAdmin
     * @return mixed
     */
    public function processAdvacedRule($behavior, $customer, $code = '', $showMessage = true, $status = Transaction::STATE_COMPLETE, $isReferred = false, $current_orders_qty = 0, $isAdmin = false)
    {
        $totalPoints = 0;
        $customerId = $customer->getId();
        if($code){
            $code = $behavior . '-' . $code;
        } else {
            $code = $behavior;
        }
        if($isReferred){
            $isAllow = $this->checkIsAllowToProcessRule($customerId, $code);
            if (!$isAllow) {
                return;
            }
        }
        $current_transaction_id = $this->getCurrentReferTransaction($customerId);
        
        //Get Advanced refer rule condition by rule settings
        $rules = $this->getReferRules($customer->getGroupId(),'', $current_orders_qty, $isAdmin);
        if($rules->count()){
            $collection = $this->customerCollectionFactory->create();
            $collection->getSelect()->join(
                ['cgf' => 'customer_grid_flat'],
                'e.entity_id = cgf.entity_id',
                []
                );
                try{
                    $currentDate  = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $this->date->gmtDate());
                    $currentYear  = (int) $currentDate->format("Y");
                    $currentMonth = (int) $currentDate->format("m");
                    $currentDay   = (int) $currentDate->format("d");
                    foreach ($rules as $_rule) {
                        $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                        $conditions = $_rule->getConditions();
                        $this->sqlBuilder->setCustomer($customer)->attachConditionToCollection($collection, $conditions);
                        $collection->getSelect()->where('e.entity_id = (?)', $customerId);
                        if($collection){
                            $transactions = $this->transactionCollectionFactory->create()
                            ->addFieldToFilter('customer_id', $customerId)
                            ->addFieldToFilter('action', $behavior);
    
                            $data = [];
                            foreach ($transactions as $transaction) {
                                $dateTime = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $transaction->getCreatedAt());
                                $year  = (int) $dateTime->format("Y");
                                $month = (int) $dateTime->format("m");
                                $day   = (int) $dateTime->format("d");
    
                                if (isset($data[$year])) {
                                    $data[$year]['points'] += $transaction->getAmount();
                                } else {
                                    $data[$year]['points'] = $transaction->getAmount();
                                }
    
                                if (isset($data[$year]['month'][$month]['points'])) {
                                    $data[$year]['month'][$month]['points'] += $transaction->getAmount();
                                } else {
                                    $data[$year]['month'][$month]['points'] = $transaction->getAmount();
                                }
    
                                if (isset($data[$year]['month'][$month]['day'][$day])) {
                                    $data[$year]['month'][$month]['day'][$day] += $transaction->getAmount();
                                } else {
                                    $data[$year]['month'][$month]['day'][$day] = $transaction->getAmount();
                                }
                            }
                            if($isReferred){
                                $earnPoints = $_rule->getReferredPoints();
                            }else {
                                $earnPoints = $_rule->getEarnPoints();
                            }
                            
                            if(count($data) > 0){
                                if ($pointsLimitYear = (int) $_rule->getPointsLimitYear()) {
                                    if (!isset($data[$currentYear]['points'])) {
                                        $data[$currentYear]['points'] = 0;
                                    }
                                    if (($earnPoints+$data[$currentYear]['points']) > $pointsLimitYear) {
                                        $earnPoints = ($pointsLimitYear - $data[$currentYear]['points']);
                                    }
                                }
    
                                if ($pointsLimitMonth = (int) $_rule->getPointsLimitMonth()) {
                                    if (!isset($data[$currentYear]['month'][$currentMonth]['points'])) {
                                        $data[$currentYear]['month'][$currentMonth]['points'] = 0;
                                    }
                                    if (($earnPoints+$data[$currentYear]['month'][$currentMonth]['points']) > $pointsLimitMonth) {
                                        $earnPoints = ($pointsLimitMonth - $data[$currentYear]['month'][$currentMonth]['points']);
                                    }
                                }
    
                                if ($pointsLimit = (int) $_rule->getPointsLimit()) {
                                    if (!isset($data[$currentYear]['month'][$currentMonth]['day'][$currentDay])) {
                                        $data[$currentYear]['month'][$currentMonth]['day'][$currentDay] = 0;
                                    }
                                    if (($earnPoints+$data[$currentYear]['month'][$currentMonth]['day'][$currentDay]) > $pointsLimit) {
                                        $earnPoints = ($pointsLimit - $data[$currentYear]['month'][$currentMonth]['day'][$currentDay]);
                                    }
                                }
                            }
                            $totalPoints += $earnPoints;
    
                            $product = '';
                            if($totalPoints){
                                $min_qty_orders = $_rule->getMinQtyOrders();
                                $max_qty_orders = $_rule->getMaxQtyOrders();
                                $message = $this->getReferMessage($this->rewardsData->formatPoints($totalPoints), $isReferred, $current_orders_qty, $min_qty_orders, $max_qty_orders);
                                if ($message && $showMessage) {
                                    $this->messageManager->addSuccess($message);
                                }
    
                                if($_rule->getHistoryMessage()){
                                    $message = $_rule->getHistoryMessage();
                                    $message = $this->rewardsData->formatCustomVariables($message, $product, $totalPoints);
                                }
                                
                                $params = [
                                    'customer_id'   => $customerId,
                                    'amount'        => $totalPoints,
                                    'amount_used'   => 0,
                                    'is_applied'    => 1,
                                    'title'         => $message,
                                    'email_message' => $_rule->getEmailMessage(),
                                    'code'          => $code,
                                    'action'        => $behavior,
                                    'status'        => $status,
                                    'store_id'      => (int) $this->getStore()->getId()
                                ];
                                if($current_transaction_id){
                                    $params['transaction_id'] = $current_transaction_id;
                                    unset($params['code']);
                                }
                                $transaction = $this->rewardsBalance->changePointsBalance($params);
                                if ($status == Transaction::STATE_COMPLETE) {
                                    $emailData['email_message'] = $_rule->getEmailMessage();
                                    if($_rule->getHistoryMessage()){
                                        $emailData['title'] = $message;
                                    }
                                    $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $emailData); 
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->rewardsLogger->addError($e->getMessage());
                    $this->messageManager->addError(__('An unspecified error occurred. Please contact us for assistance.'));
                }
        }
        return $totalPoints;
    }
    /**
     * The function process Behaviour Rule
     * @param  string $behavior
     * @param  mixed $customer
     * @param  string $code
     * @param bool $showMessage
     * @param string $status
     * @param string $productId
     * @param bool $isReferred
     * @param bool $isAdmin
     * @param bool $isSignup
     * @param bool $isFirstOrder
     * @return mixed
     */
    public function processRule($behavior, $customer, $code = '', $showMessage = true, $status = Transaction::STATE_COMPLETE, $productId = '', $isReferred = false, $isAdmin = false, $isSignup = false, $isFirstOrder = false)
    {
        $totalPoints = 0;
        $customerId = $customer->getId();
        if($code){
            $code = $behavior . '-' . $code;
        } else {
            $code = $behavior;
        }
        if($isReferred){
            $isAllow = $this->checkIsAllowToProcessRule($customerId, $code);
            if (!$isAllow) {
                return;
            }

        }

        $rules = $this->getRules($behavior, $customer->getGroupId(), '', $isSignup, $isAdmin, $isFirstOrder);

        if($rules->count()){
            $collection = $this->customerCollectionFactory->create();
            $collection->getSelect()->join(
                ['cgf' => 'customer_grid_flat'],
                'e.entity_id = cgf.entity_id',
                []
                );
            try{
                $currentDate  = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $this->date->gmtDate());
                $currentYear  = (int) $currentDate->format("Y");
                $currentMonth = (int) $currentDate->format("m");
                $currentDay   = (int) $currentDate->format("d");
                foreach ($rules as $_rule) {
                    $collection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);
                    $conditions = $_rule->getConditions();
                    $this->sqlBuilder->setCustomer($customer)->attachConditionToCollection($collection, $conditions);
                    $collection->getSelect()->where('e.entity_id = (?)', $customerId);
                    if($collection){
                        $transactions = $this->transactionCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('action', $behavior);

                        $data = [];
                        foreach ($transactions as $transaction) {
                            $dateTime = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $transaction->getCreatedAt());
                            $year  = (int) $dateTime->format("Y");
                            $month = (int) $dateTime->format("m");
                            $day   = (int) $dateTime->format("d");

                            if (isset($data[$year])) {
                                $data[$year]['points'] += $transaction->getAmount();
                            } else {
                                $data[$year]['points'] = $transaction->getAmount();
                            }

                            if (isset($data[$year]['month'][$month]['points'])) {
                                $data[$year]['month'][$month]['points'] += $transaction->getAmount();
                            } else {
                                $data[$year]['month'][$month]['points'] = $transaction->getAmount();
                            }

                            if (isset($data[$year]['month'][$month]['day'][$day])) {
                                $data[$year]['month'][$month]['day'][$day] += $transaction->getAmount();
                            } else {
                                $data[$year]['month'][$month]['day'][$day] = $transaction->getAmount();
                            }
                        }
                        if($isReferred){
                            $earnPoints = $_rule->getReferredPoints();
                        }else {
                            $earnPoints = $_rule->getEarnPoints();
                        }
                        
                        if(count($data) > 0){
                            if ($pointsLimitYear = (int) $_rule->getPointsLimitYear()) {
                                if (!isset($data[$currentYear]['points'])) {
                                    $data[$currentYear]['points'] = 0;
                                }
                                if (($earnPoints+$data[$currentYear]['points']) > $pointsLimitYear) {
                                    $earnPoints = ($pointsLimitYear - $data[$currentYear]['points']);
                                }
                            }

                            if ($pointsLimitMonth = (int) $_rule->getPointsLimitMonth()) {
                                if (!isset($data[$currentYear]['month'][$currentMonth]['points'])) {
                                    $data[$currentYear]['month'][$currentMonth]['points'] = 0;
                                }
                                if (($earnPoints+$data[$currentYear]['month'][$currentMonth]['points']) > $pointsLimitMonth) {
                                    $earnPoints = ($pointsLimitMonth - $data[$currentYear]['month'][$currentMonth]['points']);
                                }
                            }

                            if ($pointsLimit = (int) $_rule->getPointsLimit()) {
                                if (!isset($data[$currentYear]['month'][$currentMonth]['day'][$currentDay])) {
                                    $data[$currentYear]['month'][$currentMonth]['day'][$currentDay] = 0;
                                }
                                if (($earnPoints+$data[$currentYear]['month'][$currentMonth]['day'][$currentDay]) > $pointsLimit) {
                                    $earnPoints = ($pointsLimit - $data[$currentYear]['month'][$currentMonth]['day'][$currentDay]);
                                }
                            }
                        }
                        $totalPoints += $earnPoints;

                        $product = '';
                        if ($productId) {
                            $product = $this->productloader->create()->load($productId);
                        }
                        if($totalPoints){
                            $message = $this->getMessage($this->rewardsData->formatPoints($totalPoints), $behavior, $isReferred, $isSignup, $isAdmin, $isFirstOrder);
                            if ($message && $showMessage) {
                                $this->messageManager->addSuccess($message);
                            }

                            if($_rule->getHistoryMessage()){
                                $message = $_rule->getHistoryMessage();
                                $message = $this->rewardsData->formatCustomVariables($message, $product, $totalPoints);
                            }

                            $params = [
                                'customer_id'   => $customerId,
                                'amount'        => $totalPoints,
                                'amount_used'   => 0,
                                'is_applied'    => 1,
                                'title'         => $message,
                                'email_message' => $_rule->getEmailMessage(),
                                'code'          => $code,
                                'action'        => $behavior,
                                'status'        => $status,
                                'store_id'      => (int) $this->getStore()->getId()
                            ];

                            $transaction = $this->rewardsBalance->changePointsBalance($params);
                            if ($status == Transaction::STATE_COMPLETE) {
                                $emailData['email_message'] = $_rule->getEmailMessage();
                                if($_rule->getHistoryMessage()){
                                    $emailData['title'] = $message;
                                }
                                $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $emailData); 
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->rewardsLogger->addError($e->getMessage());
                $this->messageManager->addError(__('An unspecified error occurred. Please contact us for assistance.'));
            }
        }
        return $totalPoints;
    }


    public function getRules($behavior = '', $customerGroupId = '', $storeId = '', $isSignup = false, $isAdmin = false, $isFirstOrder = false)
    {
        $collection = $this->earningRuleCollectionFactory->create();
        if(!$storeId){
            $store = $this->getStore();
            $storeId = $store->getId();
        }
        if(!$customerGroupId){
            $customerGroupId = $this->customerSession->getGroupId();
        }
        $collection->addFieldToFilter('type', \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR);

        $collection->addStatusFilter()
        ->addDateFilter()
        ->addStoreFilter($storeId, $isAdmin)
        ->addCustomerGroupFilter($customerGroupId);

        //Map Behavior
        if($behavior) {
            switch ($behavior) {
                case Earning::BEHAVIOR_FACEBOOK_UNLIKE:
                    $behavior = Earning::BEHAVIOR_FACEBOOK_LIKE;
                    break;

                case Earning::BEHAVIOR_GOOGLEPLUS_UNLIKE:
                    $behavior = Earning::BEHAVIOR_GOOGLEPLUS_LIKE;
                    break;
            }
            $collection->addFieldToFilter('action', $behavior);
        }
        if($behavior == \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR_REFER_FRIEND){
            if($isSignup){
                $collection->addFieldToFilter('apply_type', [
                    ['eq' => \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType::TYPE_REGISTER],
                    ['eq' => \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType::TYPE_BOTH]
                ]);
            }elseif($isFirstOrder) {
                $collection->addFieldToFilter('apply_type', [
                    ['eq' => \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType::TYPE_PLACE_ORDER],
                    ['eq' => \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType::TYPE_BOTH]
                ]);
            }
        }
        $collection->getSelect()
                    ->order('main_table.sort_order asc')
                    ->order('main_table.rule_id DESC');
        return $collection;
    }

    public function getReferRules( $customerGroupId = '', $storeId = '', $current_orders_qty = 0, $isAdmin = false)
    {
        $collection = $this->earningRuleCollectionFactory->create();
        if(!$storeId){
            $store = $this->getStore();
            $storeId = $store->getId();
        }
        if(!$customerGroupId){
            $customerGroupId = $this->customerSession->getGroupId();
        }
        $collection->addFieldToFilter('type', \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR);

        $collection->addStatusFilter()
        ->addDateFilter()
        ->addStoreFilter($storeId, $isAdmin)
        ->addCustomerGroupFilter($customerGroupId);

        $collection->addFieldToFilter('action', \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR_REFER_FRIEND);
        $collection->addFieldToFilter('apply_type', \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType::TYPE_ADVANCED);
        $collection->addFieldToFilter('min_qty_orders', ['lt' => $current_orders_qty]);
        $collection->addFieldToFilter('max_qty_orders', ['gt' => $current_orders_qty]);
        $collection->getSelect()
                    ->order('main_table.sort_order asc')
                    ->order('main_table.rule_id DESC');

        return $collection;
    }

    public function getStore($storeId = '')
    {
        $store = $this->storeManager->getStore($storeId);
        return $store;
    }

    public function getMessage($points, $behavior, $isReferred = false, $isSignup = false, $isAdmin = false, $isFirstOrder =false)
    {
        $message = '';
        switch ($behavior) {
            case Earning::BEHAVIOR_REFER_FRIEND:
                $message = __('Your friend received %1 for refer friend', $points);
                if($isReferred && $isSignup && $isFirstOrder){
                    $message = __('You received %1 from referer friend', $points);
                }
                break;

            case Earning::BEHAVIOR_SIGNIN:
                $message = __('You received %1 for signing in', $points);
                break;

            case Earning::BEHAVIOR_SIGNUP:
                $message = __('You received %1 for signing up', $points);
                break;
            
            case Earning::BEHAVIOR_NEWSLETTER_SIGNUP:
                $message = __('You received %1 for sign up for newsletter', $points);
                break;

            case Earning::BEHAVIOR_NEWSLETTER_UNSIGNUP:
                $message = __('Newsletter Signup points has been canceled', $points);
                break;

            case Earning::BEHAVIOR_REVIEW:
                $message = __('You will receive %1 after approving of this review', $points);
                break;

            case Earning::BEHAVIOR_BIRTHDAY:
                $message = __('Happy birthday! You received %1', $points);
                break;

            case Earning::BEHAVIOR_FACEBOOK_LIKE:
                $message = __('You have earned %1 for Facebook Like', $points);
                break;

            case Earning::BEHAVIOR_FACEBOOK_SHARE:
                $message = __('You have earned %1 for Facebook Share', $points);
                break;

            case Earning::BEHAVIOR_FACEBOOK_UNLIKE:
                $message = __('Facebook Like Points has been canceled', $points);
                break;

            case Earning::BEHAVIOR_TWITTER_TWEET:
                $message = __('You have earned %1 for Tweet', $points);
                break;

            case Earning::BEHAVIOR_GOOGLEPLUS_LIKE:
                $message = __('You have earned %1 for Google+', $points);
                break;

            case Earning::BEHAVIOR_GOOGLEPLUS_UNLIKE:
                $message = __('G+1 Points has been canceled', $points);
                break;
            case Earning::BEHAVIOR_PRINTEREST_PIN:
                $message = __('You have earned %1 for Pin', $points);
                break;
        }
        return $message;
    }

    public function getReferMessage($points, $isReferred = false, $current_orders_qty =0, $min_qty_orders =0, $max_qty_orders = 0){
        $message = __('Advanced Refer: Your friend received %1 for refer friend with rule min orders = %2, max order = %3', $points, $min_qty_orders, $max_qty_orders);
        if($isReferred){
            $message = __('Advanced Refer: You received %1 from referer friend with total order qty %2 with rule min orders = %3, max order = %4', $points, $current_orders_qty, $min_qty_orders, $max_qty_orders);
        }
        return $message;
    }

}