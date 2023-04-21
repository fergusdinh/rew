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
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Api\Data;

interface TransactionInterface
{

    const TRANSACTION_ID = 'transaction_id';
    const CUSTOMER_ID = 'customer_id';
    const QUOTE_ID = 'quote_id';
    const AMOUNT = 'amount';
    const AMOUNT_USED = 'amount_used';
    const TITLE = 'title';
    const CODE = 'code';
    const ACTION = 'action';
    const STATUS = 'status';
    const PARAMS = 'params';
    const IS_EXPIRATION_EMAIL_SENT = 'is_expiration_email_sent';
    const EMAIL_MESSAGE = 'email_message';
    const APPLY_AT = 'apply_at';
    const IS_APPLIED = 'is_applied';
    const IS_EXPIRED = 'is_expired';
    const EXPIRES_AT = 'expires_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const ORDER_ID = 'order_id';
    const ADMIN_USER_ID = 'admin_user_id';
    const CREATED_AT = 'created_at';

    /**
     * Get transaction_id
     * @return int|null
     */
    public function getTransactionId();

    /**
     * Set transaction_id
     * @param int $transactionId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * Get customer_id
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param int $customerId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get quote_id
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Set quote_id
     * @param int $quoteId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Get amount
     * @return float|null
     */
    public function getAmount();

    /**
     * Set amount
     * @param float $amount
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAmount($amount);

    /**
     * Get amount_used
     * @return float|null
     */
    public function getAmountUsed();

    /**
     * Set amount_used
     * @param float $amountUsed
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAmountUsed($amountUsed);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setTitle($title);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCode($code);

    /**
     * Get action
     * @return string|null
     */
    public function getAction();

    /**
     * Set action
     * @param string $action
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAction($action);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setStatus($status);

    /**
     * Get params
     * @return string|null
     */
    public function getParams();

    /**
     * Set params
     * @param string $params
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setParams($params);

    /**
     * Get is_expiration_email_sent
     * @return int|null
     */
    public function getIsExpirationEmailSent();

    /**
     * Set is_expiration_email_sent
     * @param int $is_expiration_email_sent
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsExpirationEmailSent($is_expiration_email_sent);

    /**
     * Get email_message
     * @return string|null
     */
    public function getEmailMessage();

    /**
     * Set email_message
     * @param string $emailMessage
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setEmailMessage($emailMessage);

    /**
     * Get apply_at
     * @return string|null
     */
    public function getApplyAt();

    /**
     * Set apply_at
     * @param string $applyAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setApplyAt($applyAt);

    /**
     * Get is_applied
     * @return int|null
     */
    public function getIsApplied();

    /**
     * Set is_applied
     * @param int $isApplied
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsApplied($isApplied);

    /**
     * Get is_expired
     * @return int|null
     */
    public function getIsExpired();

    /**
     * Set is_expired
     * @param int $isExpired
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setIsExpired($isExpired);

    /**
     * Get expires_at
     * @return string|null
     */
    public function getExpiresAt();

    /**
     * Set expires_at
     * @param string $expiresAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setExpiresAt($expiresAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get store_id
     * @return int|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param int $storeId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setStoreId($storeId);

    /**
     * Get admin_user_id
     * @return int|null
     */
    public function getAdminUserId();

    /**
     * Set admin_user_id
     * @param int $adminUserId
     * @return \Lof\RewardPoints\Api\Data\TransactionInterface
     */
    public function setAdminUserId($adminUserId);
    
}
