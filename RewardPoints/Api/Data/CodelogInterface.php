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

interface CodelogInterface
{

    const LOG_ID = 'log_id';
    const CODE_ID = 'code_id';
    const USER_ID = 'user_id';
    const STORE_ID = 'store_id';


    /**
     * Get log_id
     * @return string|null
     */
    public function getLogId();

    /**
     * Set log_id
     * @param string $logId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setLogId($logId);

    /**
     * Get code_id
     * @return string|null
     */
    public function getCodeId();

    /**
     * Set code_id
     * @param string $codeId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setCodeId($codeId);

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId();

    /**
     * Set user_id
     * @param string $userId
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setUserId($userId);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $store_id
     * @return \Lof\RewardPoints\Api\Data\CodelogInterface
     */
    public function setStoreId($store_id);
}
