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
 * @package    Lof_Gallery
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\Data\RedeemInterface;

class Redeem extends \Magento\Framework\Model\AbstractModel implements RedeemInterface
{

    protected $_eventPrefix = 'lof_rewardpoints_redeem';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Redeem');
    }

    /**
     * Get code_id
     * @return string
     */
    public function getCodeId()
    {
        return $this->getData(self::CODE_ID);
    }

    /**
     * Set code_id
     * @param string $codeId
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodeId($codeId)
    {
        return $this->setData(self::CODE_ID, $codeId);
    }

    /**
     * Get code_prefix
     * @return string
     */
    public function getCodePrefix()
    {
        return $this->getData(self::CODE_PREFIX);
    }

    /**
     * Set code_prefix
     * @param string $codePrefix
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodePrefix($codePrefix)
    {
        return $this->setData(self::CODE_PREFIX, $codePrefix);
    }

    /**
     * Get code
     * @return string
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * Set code
     * @param string $code
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * Get earn_points
     * @return string
     */
    public function getEarnPoints()
    {
        return $this->getData(self::EARN_POINTS);
    }

    /**
     * Set earn_points
     * @param string $earnPoints
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setEarnPoints($earnPoints)
    {
        return $this->setData(self::EARN_POINTS, $earnPoints);
    }

    /**
     * Get uses_per_code
     * @return string
     */
    public function getUsesPerCode()
    {
        return $this->getData(self::USES_PER_CODE);
    }

    /**
     * Set uses_per_code
     * @param string $usesPerCode
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setUsesPerCode($usesPerCode)
    {
        return $this->setData(self::USES_PER_CODE, $usesPerCode);
    }

    /**
     * Get store_id
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get active_from
     * @return string
     */
    public function getActiveFrom()
    {
        return $this->getData(self::ACTIVE_FROM);
    }

    /**
     * Set active_from
     * @param string $activeFrom
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setActiveFrom($activeFrom)
    {
        return $this->setData(self::ACTIVE_FROM, $activeFrom);
    }

    /**
     * Get active_to
     * @return string
     */
    public function getActiveTo()
    {
        return $this->getData(self::ACTIVE_TO);
    }

    /**
     * Set active_to
     * @param string $activeTo
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setActiveTo($activeTo)
    {
        return $this->setData(self::ACTIVE_TO, $activeTo);
    }

    /**
     * Get code_used
     * @return string
     */
    public function getCodeUsed()
    {
        return $this->getData(self::CODE_USED);
    }

    /**
     * Set code_used
     * @param string $codeUsed
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface
     */
    public function setCodeUsed($codeUsed)
    {
        return $this->setData(self::CODE_USED, $codeUsed);
    }
}
