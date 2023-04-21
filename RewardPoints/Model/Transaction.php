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
use Lof\RewardPoints\Api\Data\TransactionInterface;

class Transaction extends \Magento\Framework\Model\AbstractModel implements TransactionInterface
{
	/**
	 * Transaction Cache Tag
	 */
    const CACHE_TAG           = 'rewardpoints_transaction';

    const EARNED_CODE         = 'order_earn-';

    const SPENT_CODE          = 'order_spen-';

    const CANCEL_EARNED_CODE  = 'order_earn_cancel-';

    const SPENT_RESTORE       = 'order_spend-restore-';

    const SPENT_AMOUNT        = 'order_spend_amount-';

    const CANCEL_SPENT_AMOUNT = 'order_cancel_spent_amount-';

    /**
     * Transaction States
     */
    const STATE_NEW        = 'new';

    const STATE_PROCESSING = 'processing';

    const STATE_COMPLETE   = 'complete';

    const STATE_CLOSED     = 'closed';

    const STATE_CANCELED   = 'canceled';

    const STATE_HOLDED     = 'holded';

    const STATE_EXPIRED    = 'expired';

    const STATE_FAILED     = 'failed';

    /**
     * Transaction Actions
     */
    const EARNING_ORDER          = 'earning_order';

    const EARNING_CREDITMEMO     = 'earning_creditmemo';

    const EARNING_CANCELED       = 'earning_canceled';

    const EARNING_CLOSED         = 'earning_closed';

    const SPENDING_ORDER         = 'spending_order';

    const SPENDING_CREDITMEMO    = 'spending_creditmemo';

    const SPENDING_CANCELED      = 'spending_cancel';

    const SPENDING_CLOSED        = 'spending_close';

    const ADMIN_ADD_TRANSACTION  = 'admin_add';

    const CUSTOMER_NEWSLETTER    = 'customer_newsletter';

    const CUSTOMER_REGISTER      = 'customer_register';

    const CUSTOMER_LOGIN         = 'customer_login';

    const CUSTOMER_FACBOOK_LIKE  = 'customer_fblike';

    const CUSTOMER_FACBOOK_SHARE = 'customer_fblike';

    const CUSTOMER_GOOGLE_PLUS   = 'customer_ggplus';

    const CUSTOMER_PINTEREST_PIN = 'customer_pin';

    const CUSTOMER_BIRTHDAY      = 'customer_birthday';

    const CUSTOMER_REVIEW        = 'customer_review';

    const CUSTOMER_TWITTER_TWEET = 'customer_tweet';


	/**
	 * @var string
	 */
	protected $_cacheTag = 'rewardpoints_transaction';

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer = null;

    /**
     * @param \Magento\Framework\Model\Context                                  $context
     * @param \Magento\Framework\Registry                                       $registry
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction|null            $resource
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection|null $resourceCollection
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory  $customerCollectionFactory
     * @param \Lof\RewardPoints\Helper\Customer                                 $rewardsCustomer
     * @param \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory  $purchaseCollectionFactory
     * @param \Lof\RewardPoints\Logger\Logger                                   $rewardsLogger
     * @param array                                                             $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\RewardPoints\Model\ResourceModel\Transaction $resource = null,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection $resourceCollection = null,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->rewardsCustomer           = $rewardsCustomer;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
        $this->rewardsLogger             = $rewardsLogger;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Transaction');
    }

    public function getAvailableStatuses($empty = false)
    {
        $options = [
            self::STATE_NEW        => __('New'),
            self::STATE_PROCESSING => __('Processing'),
            self::STATE_COMPLETE   => __('Complete'),
            self::STATE_CLOSED     => __('Closed'),
            self::STATE_CANCELED   => __('Canceled'),
            self::STATE_HOLDED     => __('Holded'),
            self::STATE_FAILED     => __('Failed'),
            self::STATE_EXPIRED    => __('Expired')
        ];
        if ($empty) {
            array_unshift($options, "");
        }
        return $options;
    }

    public function getStatusLabel($status = '')
    {
        $label = __('Empty');
        if ($status == '') {
            $status = $this->getData('status');
        }

        $availableStatuses = $this->getAvailableStatuses();
        foreach ($availableStatuses as $key => $value) {
            if ($key == $status) {
                $label = $value;
                break;
            }
        }
        return $label;
    }

    /**
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        if ($this->customer === null && $this->getCustomerId()) {
                $customer = $this->customerCollectionFactory->create()
                    ->addAttributeToSelect('*')
                    ->addFieldToFilter('entity_id', $this->getCustomerId())
                    ->getFirstItem();
                $rewardsCustomer = $this->rewardsCustomer->getCustomer($customer->getId());
                foreach ($rewardsCustomer->getData() as $k => $v) {
                    $customer->setData($k, $v);
                }
                $this->customer = $customer;
        }
        return $this->customer;
    }

    public function getPurchase()
    {
        $collection = $this->purchaseCollectionFactory->create();
        $purchase = $collection->addFieldToFilter('order_id', $this->getOrderId())->getFirstItem();
        return $purchase;
    }

    public function getRewardsCustomer()
    {
        $rewardsCustomer = $this->rewardsCustomer->getCustomer($this->getCustomerId());
        return $rewardsCustomer;
    }

    public function getActionLabel()
    {
        $action = $this->getData('action');
        $label  = $this->getActions($action);
        return $label;
    }

    /**
     * @return int
     */
    public function getDaysLeft()
    {
        if ($expires = $this->getData('expires_at')) {
            $diff = strtotime($expires) - time();
            $days = (int) ($diff / 60 / 60 / 24);

            // Debug
            if ($days<0) {
                $this->rewardsLogger->addError('Transaction Day Left is smaller than 0');
                $days = 1;
            }

            return $days;
        }
        return;
    }

    public function getActions($code = '')
    {
        $options = [
            self::EARNING_ORDER          => __('Earn points for purchasing order'),
            self::EARNING_CREDITMEMO     => __('Retrive points for refunding order'),
            self::EARNING_CANCELED       => __('Retrive points for canceling order'),
            self::EARNING_CLOSED         => __('Retrive points for closing order'),
            self::SPENDING_ORDER         => __('Spend points to purchase order'),
            self::SPENDING_CREDITMEMO    => __('Retrive spent points on refunded order'),
            self::SPENDING_CANCELED      => __('Retrive spent points on canceled order'),
            self::SPENDING_CLOSED        => __('Retrive spent points on cloded order'),
            self::ADMIN_ADD_TRANSACTION  => __('Changed By Admin'),
            self::CUSTOMER_NEWSLETTER    => __('Receive point for subscribing to newsletter'),
            self::CUSTOMER_REGISTER      => __('Receive point for registering successfully'),
            self::CUSTOMER_LOGIN         => __('Receive point for logging in successfully'),
            self::CUSTOMER_FACBOOK_LIKE  => __('Receive points for Facebook like'),
            self::CUSTOMER_FACBOOK_SHARE => __('Receive point for sharing via Facebook'),
            self::CUSTOMER_GOOGLE_PLUS   => __('Receive point for +1 via Google'),
            self::CUSTOMER_PINTEREST_PIN => __('Receive points pin via Pinterest'),
            self::CUSTOMER_BIRTHDAY      => __('Receive points for birthday'),
            self::CUSTOMER_REVIEW        => __('Receive point for reviewing a product'),
            self::CUSTOMER_TWITTER_TWEET => __('Recieve point for tweeting via Twitter'),
        ];
        if ($code) {
            foreach ($options as $k => $v) {
                if ($k == $code ) {
                    return $v;
                }
            }
            return false;
        }
        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId(){
        return $this->getData(self::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId){
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId(){
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param int $customerId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCustomerId($customerId){
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get quote_id
     * @return int|null
     */
    public function getQuoteId(){
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set quote_id
     * @param int $quoteId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setQuoteId($quoteId){
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get amount
     * @return float|null
     */
    public function getAmount(){
        return $this->getData(self::AMOUNT);
    }

    /**
     * Set amount
     * @param float $amount
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAmount($amount){
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get amount_used
     * @return float|null
     */
    public function getAmountUsed(){
        return $this->getData(self::AMOUNT_USED);
    }

    /**
     * Set amount_used
     * @param float $amountUsed
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAmountUsed($amountUsed){
        return $this->setData(self::AMOUNT_USED, $amountUsed);
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle(){
        return $this->getData(self::TITLE);
    }

    /**
     * Set title
     * @param string $title
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setTitle($title){
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get code
     * @return string|null
     */
    public function getCode(){
        return $this->getData(self::CODE);
    }

    /**
     * Set code
     * @param string $code
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCode($code){
        return $this->setData(self::CODE, $code);
    }

    /**
     * Get action
     * @return string|null
     */
    public function getAction(){
        return $this->getData(self::ACTION);
    }

    /**
     * Set action
     * @param string $action
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAction($action){
        return $this->setData(self::ACTION, $action);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus(){
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setStatus($status){
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get params
     * @return string|null
     */
    public function getParams(){
        return $this->getData(self::PARAMS);
    }

    /**
     * Set params
     * @param string $params
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setParams($params){
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * Get is_expiration_email_sent
     * @return int|null
     */
    public function getIsExpirationEmailSent(){
        return $this->getData(self::IS_EXPIRATION_EMAIL_SENT);
    }

    /**
     * Set is_expiration_email_sent
     * @param int $is_expiration_email_sent
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsExpirationEmailSent($is_expiration_email_sent){
        return $this->setData(self::IS_EXPIRATION_EMAIL_SENT, $is_expiration_email_sent);
    }

    /**
     * Get email_message
     * @return string|null
     */
    public function getEmailMessage(){
        return $this->getData(self::EMAIL_MESSAGE);
    }

    /**
     * Set email_message
     * @param string $emailMessage
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setEmailMessage($emailMessage){
        return $this->setData(self::EMAIL_MESSAGE, $emailMessage);
    }

    /**
     * Get apply_at
     * @return string|null
     */
    public function getApplyAt(){
        return $this->getData(self::APPLY_AT);
    }

    /**
     * Set apply_at
     * @param string $applyAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setApplyAt($applyAt){
        return $this->setData(self::APPLY_AT, $applyAt);
    }

    /**
     * Get is_applied
     * @return int|null
     */
    public function getIsApplied(){
        return $this->getData(self::IS_APPLIED);
    }

    /**
     * Set is_applied
     * @param int $isApplied
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsApplied($isApplied){
        return $this->setData(self::IS_APPLIED, $isApplied);
    }

    /**
     * Get is_expired
     * @return int|null
     */
    public function getIsExpired(){
        return $this->getData(self::IS_EXPIRED);
    }

    /**
     * Set is_expired
     * @param int $isExpired
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsExpired($isExpired){
        return $this->setData(self::IS_EXPIRED, $isExpired);
    }

    /**
     * Get expires_at
     * @return string|null
     */
    public function getExpiresAt(){
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * Set expires_at
     * @param string $expiresAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setExpiresAt($expiresAt){
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt(){
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setUpdatedAt($updatedAt){
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt(){
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCreatedAt($createdAt){
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get store_id
     * @return int|null
     */
    public function getStoreId(){
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param int $storeId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setStoreId($storeId){
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get admin_user_id
     * @return int|null
     */
    public function getAdminUserId(){
        return $this->getData(self::ADMIN_USER_ID);
    }

    /**
     * Set admin_user_id
     * @param int $adminUserId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAdminUserId($adminUserId){
        return $this->setData(self::ADMIN_USER_ID, $adminUserId);
    }
}
