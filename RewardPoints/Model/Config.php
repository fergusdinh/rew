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

class Config
{
	const ROUTES                  = 'rewardpoints';
	const EARNING_RATE            = 'earning_rate';
	const EARNING_PRODUCT_POINTS  = 'earning_product_points';
	const SPENDING_RATE           = 'spending_rate';
	const SPENDING_PRODUCT_POINTS = 'spending_product_points';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
    	$this->scopeConfig = $scopeConfig;
        $this->dateTime      = $dateTime;
    }

    public function getConfig($key, $store = null){
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/'.$key,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }
    /**
     * GENERAL SETTINGS
     */
    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEnable($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/enable',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

     /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPointLabel($store = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        if($storeId != 0){
            $scope = 'store';
        }else{
            $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/point_label',
            $scope,
            $storeId
        );
        if ($value=='') {
            $value = _('point');
        }
        return $value;
    }

     /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPointsLabel($store = null)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getId();
        if($storeId != 0){
            $scope = 'store';
        }else{
            $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/points_label',
            $scope,
            $storeId
        );
        if ($value=='') {
            $value = _('points');
        }
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPointImage($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/point_image',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isUseRewardsPolicyPage($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/use_policypage',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRewardsPolicyPageId($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/general/use_policypage',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }


    /**
     * EARNING POINTS SETTINGS
     */

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getRoundingMethod($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/rounding_method',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }


     /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEarningExpire($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/expire',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getEarningExpireDate($fomrat = 'm/d/Y h:m:s')
    {
        $expireDate = '';
        $todayDate = $this->dateTime->gmtDate($fomrat);
        if ((float)($earningExpireDate = $this->getEarningExpire())) {
            $newdate    = strtotime ( $todayDate . '+' . $earningExpireDate . ' day') ;
            $expireDate = $this->dateTime->gmtDate($fomrat, $newdate);
        }
        return $expireDate;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEarnAfterInvoice($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/is_earn_after_action',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        $return = false;
        if($value == "after_invoice"){
            $return = true;
        }
        return $return;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEarnAfterShipment($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/is_earn_after_action',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        $return = false;
        if($value == "after_shipment"){
            $return = true;
        }
        return $return;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEarnAfterCheckout($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/is_earn_after_action',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        $return = false;
        if($value == "after_checkout"){
            $return = true;
        }
        return $return;
    }

    /**
     * @param null|string $store
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isCancelAfterRefund($store = null)
    {
        return $this->scopeConfig->getValue(
            'lofrewardpoints/earning/is_cancel_after_refund',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralEarnInStatuses($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/earn_in_statuses',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return explode(',', $value);
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getGeneralSpendInStatuses($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/spending/spend_in_statuses',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );

        return explode(',', $value);
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getMaximumEarningPointsPerOrder($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/maximum_points',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEarnPointsFromTax($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/by_tax',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEarnPointsFromShipping($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/earning/by_shipping',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }



    /**
     * SPENDING SETTINGS
     */

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getMaximumSpendingPointsPerOrder($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/spending/maximum_points',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isSpendPointsFromTax($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/spending/by_tax',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isSpendPointsFromShipping($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/spending/by_shipping',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }



    /**
     * NOTIFICATION SETTINGS
     */
    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSenderEmail($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/sender_email',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getBalanceUpdateEmailTemplate($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/balance_update_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }
    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getReferLinkEmail($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/send_referlink_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPointsExpireEmailTemplate($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/points_expire_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getSendBeforeExpiringDays($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/send_before_expiring_days',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getPointsExpiredEmailTemplate($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/notification/points_expired_email_template',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;
    }

}
