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

namespace Lof\RewardPointsBehavior\Model;

class Config
{
	const ROUTES                       = 'rewardpointsbehavior';
	
	const BEHAVIOR_SIGNIN              = 'signin';
	const BEHAVIOR_SIGNUP              = 'signup';
	const BEHAVIOR_NEWSLETTER_SIGNUP   = 'newsletter_signup';
	const BEHAVIOR_NEWSLETTER_UNSIGNUP = 'newsletter_un_signup';
	const BEHAVIOR_REVIEW              = 'review';
	const BEHAVIOR_BIRTHDAY            = 'birthday';
	
	const BEHAVIOR_FACEBOOK_SHARE      = 'facebook_share';
	const BEHAVIOR_FACEBOOK_LIKE       = 'facebook_like';
	const BEHAVIOR_FACEBOOK_UNLIKE     = 'facebook_unlike';
	const BEHAVIOR_TWITTER_TWEET       = 'twitter_tweet';
	const BEHAVIOR_GOOGLEPLUS_LIKE     = 'googleplus_like';
	const BEHAVIOR_GOOGLEPLUS_UNLIKE   = 'googleplus_unlike';
	const BEHAVIOR_PRINTEREST_PIN      = 'pinterest_pin';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig  
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	$this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null|string $store
     * @return array
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function isEnable($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/behaviorplugin/enable',
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
    public function getFacebookApiKey($store = null)
    {
        $value = $this->scopeConfig->getValue(
            'lofrewardpoints/behaviorplugin/facebook_api_key',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;  
    }
}