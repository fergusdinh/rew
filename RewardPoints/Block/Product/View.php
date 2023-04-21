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
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Block\Product;

use Lof\RewardPoints\Model\Config;

class View extends \Magento\Catalog\Block\Product\View
{
    protected $_template = 'product/view/points.phtml';

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $_rules;

    /**
     * @param \Magento\Catalog\Block\Product\Context              $context
     * @param \Magento\Framework\Url\EncoderInterface             $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface            $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils               $string
     * @param \Magento\Catalog\Helper\Product                     $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface           $localeFormat
     * @param \Magento\Customer\Model\Session                     $customerSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface     $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface   $priceCurrency
     * @param \Lof\RewardPoints\Helper\Balance\Spend              $rewardsBalanceSpend
     * @param \Lof\RewardPoints\Helper\Data                       $rewardsData
     * @param array                                               $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        array $data = []
        ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency
            );
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsData         = $rewardsData;
    }

    /**
     * Retrieve current product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product;
    }

    public function getRules()
    {
        $product = $this->getProduct();
        if($this->rewardsData->isLoggedIn() && !$this->isProductSpendingRules()) {
            if(!$this->_rules) {
                $this->_rules = $this->rewardsBalanceSpend->getProductSpendingRules($product);
            }
        }
        return $this->_rules;
    }

    public function isProductSpendingRules()
    {
        $product = $this->getProduct();
        if ($this->rewardsBalanceSpend->getProductSpendingPoints($product->getId(), true)) {
            return true;
        }
        return false;
    }

    public function enableSpendingPoints(){
        return $this->rewardsData->getConfig()->getConfig("display/show_spending_on_product");
    }
    public function enableEarningPoints(){
        return $this->rewardsData->getConfig()->getConfig("display/show_earning_on_product");
    }

}
