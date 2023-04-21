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

use Magento\Framework\Api\SearchCriteriaInterface;

interface RedeemRepositoryInterface
{
    /**
     * Save Redeem
     * @param \Lof\RewardPoints\Api\Data\RedeemInterface $redeem
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Lof\RewardPoints\Api\Data\RedeemInterface $redeem);

    /**
     * Retrieve Redeem
     * @param string $redeemId
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($redeemId);

    /**
     * Retrieve Redeem matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\RewardPoints\Api\Data\RedeemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Redeem by ID
     * @param string $redeemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($redeemId);

    /**
     * Apply Code
     * @param string $customerId
     * @param string $code
     * @param int $storeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyCode($customerId, $code, $storeId);
}
