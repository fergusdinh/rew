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

interface RedeemInterface
{

    const ACTIVE_TO = 'active_to';
    const EARN_POINTS = 'earn_points';
    const CODE = 'code';
    const CODE_PREFIX = 'code_prefix';
    const USES_PER_CODE = 'uses_per_code';
    const STORE_ID = 'store_id';
    const CODE_ID = 'code_id';
    const ACTIVE_FROM = 'active_from';
    const CODE_USED = 'code_used';

    /**
     * Get code_id
     * @return string|null
     */
    public function getCodeId();

    /**
     * Set code_id
     * @param string $codeId
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodeId($codeId);

    /**
     * Get code_prefix
     * @return string|null
     */
    public function getCodePrefix();

    /**
     * Set code_prefix
     * @param string $codePrefix
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodePrefix($codePrefix);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCode($code);

    /**
     * Get earn_points
     * @return string|null
     */
    public function getEarnPoints();

    /**
     * Set earn_points
     * @param string $earnPoints
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setEarnPoints($earnPoints);

    /**
     * Get uses_per_code
     * @return string|null
     */
    public function getUsesPerCode();

    /**
     * Set uses_per_code
     * @param string $usesPerCode
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setUsesPerCode($usesPerCode);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setStoreId($storeId);

    /**
     * Get active_from
     * @return string|null
     */
    public function getActiveFrom();

    /**
     * Set active_from
     * @param string $activeFrom
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setActiveFrom($activeFrom);

    /**
     * Get active_to
     * @return string|null
     */
    public function getActiveTo();

    /**
     * Set active_to
     * @param string $activeTo
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setActiveTo($activeTo);

    /**
     * Get code_used
     * @return string|null
     */
    public function getCodeUsed();

    /**
     * Set code_used
     * @param string $code_used
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodeUsed($code_used);
}
