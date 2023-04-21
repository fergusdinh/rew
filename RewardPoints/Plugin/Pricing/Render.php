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

namespace Lof\RewardPoints\Plugin\Pricing;
use \Magento\Framework\Pricing\SaleableInterface;
class Render extends \Magento\Framework\Pricing\Render
{
    /**
     * Render price
     *
     * @param string $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function render($priceCode, SaleableInterface $saleableItem, array $arguments = [])
    {
        if ( ($saleableItem instanceof \Magento\Catalog\Model\Product) && $saleableItem->getSpendingPoints() ) {
            if($saleableItem->getIsProductView()) parent::render($priceCode, $saleableItem, $arguments);
            $html = $this->getLayout()->createBlock('\Magento\Framework\View\Element\Template')->setProduct($saleableItem)->setTemplate('Lof_RewardPoints::product/list/points.phtml')->toHtml();
            return $html;
        }
        return parent::render($priceCode, $saleableItem, $arguments);
    }

}