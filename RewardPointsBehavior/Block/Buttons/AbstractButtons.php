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

namespace Lof\RewardPointsBehavior\Block\Buttons;

class AbstractButtons extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $_url;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogImage;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPointsBehavior\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Magento\Framework\Url                           $url             
     * @param \Magento\Catalog\Helper\Image                    $catalogImage    
     * @param \Lof\RewardPointsBehavior\Helper\Data            $rewardsData     
     * @param \Lof\RewardPoints\Helper\Customer                $rewardsCustomer 
     * @param \Lof\RewardPointsBehavior\Model\Config           $rewardsConfig   
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url $url,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPointsBehavior\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_url            = $url;
        $this->catalogImage    = $catalogImage;
        $this->rewardsData     = $rewardsData;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsConfig   = $rewardsConfig;
    }

	public function getLocaleCode()
	{
		$store = $this->_storeManager->getStore();
        $storeLocale = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getCode()
        );
		return $storeLocale;
	}

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        $customer = $this->rewardsCustomer->getCustomer();
        return $customer;
    }

    public function getCurrentUrl()
    {
        $url = $this->_url->getCurrentUrl();
        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getCurrentEncodedUrl($url)
    {
        return urlencode($url);
    }

    public function getCurrentProduct()
    {
        return $this->rewardsData->getCurrentProduct();
    }
}