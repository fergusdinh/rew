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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Model;

class Config
{
	/** PURCHASE PARAMS */
	const EARNING_CATALOG_RULE  = 'earning_catalog_rule';
	const EARNING_CART_RULE     = 'earning_cart_rule';
	const SPENDING_CATALOG_RULE = 'spending_catalog_rule';
	const SPENDING_CART_RULE    = 'spending_cart_rule';
    const SPENDING_RATE           = 'spending_rate';

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
            'lofrewardpoints/ruleplugin/enable',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $store
        );
        return $value;  
    }
}