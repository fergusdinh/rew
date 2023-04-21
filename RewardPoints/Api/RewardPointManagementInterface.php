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
namespace Lof\RewardPoints\Api;

interface RewardPointManagementInterface
{
    /**
     * GET for  total point
     * @param string $customerId
     * @return mixed
     */
    public function getTotalCustomerPoints($customerId);

    /**
     * GET spending total point by customer
     * @param int $cartId
     * @return mixed
     */
    public function getTotalSpentPoint($cartId);
    /**
     * GET Transaction
     * @param string $customer_id
     * @return mixed
     */
    public function getTransaction($customer_id);
    /**
     * GET for total earn points by order id
     * @param int $order_id
     * @return mixed
     */
    public function getOrderEarnPoints($order_id);
    /**
     * GET for total earn points by order id
     * @param int $order_id
     * @return mixed
     */
    public function getOrderEarnSpentPoints($order_id);
    /**
     * GET List spend rule in cart
     * @param int $cartId
     * @return mixed
     */
    public function getListSpendingRule($cartId);
    /**
     * apply point in cart
     * @param int $cartId
     * @param float $spendPoint
     * @param int $spendingRuleId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyPoint($cartId,$spendPoint,$spendingRuleId);
    /**
     * Retrieve  matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\RewardPoints\Api\Data\RewardPointsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
