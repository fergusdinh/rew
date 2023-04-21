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

namespace Lof\RewardPoints\Plugin\Product;

class Points
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Lof\RewardPoints\Helper\Data $rewardsData
     */
    public function __construct(\Lof\RewardPoints\Helper\Data $rewardsData)
    {
        $this->rewardsData = $rewardsData;
    }

    /**
     * @param \Magento\Review\Block\Product\ReviewRenderer $reviewRenderer
     * @param \Magento\Catalog\Model\Product               $product
     * @param bool                                         $templateType
     * @param bool                                         $displayIfNoReviews
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetReviewsSummaryHtml(
        \Magento\Review\Block\Product\ReviewRenderer $reviewRenderer,
        \Magento\Catalog\Model\Product $product,
        $templateType = false,
        $displayIfNoReviews = false
        ) {
        $this->product = $product;
        return [
            $product,
            $templateType,
            $displayIfNoReviews
        ];
    }

	/**
     * Get product reviews summary
     *
     * @param \Magento\Review\Block\Product\ReviewRenderer $reviewRenderer
     * @param string                                       $result
     * @return string
     */
    public function afterGetReviewsSummaryHtml(
        \Magento\Review\Block\Product\ReviewRenderer $reviewRenderer,
        $result
    ) {
        if (!$this->rewardsData->isProductPage()) {
            $result .= $reviewRenderer->getLayout()
            ->createBlock('\Magento\Framework\View\Element\Template')
            ->setProduct($this->product)
            ->setTemplate('Lof_RewardPoints::product/list/earning_points.phtml')
            ->toHtml();

            $result .= $reviewRenderer->getLayout()
            ->createBlock('\Magento\Framework\View\Element\Template')
            ->setProduct($this->product)
            ->setTemplate('Lof_RewardPoints::product/list/spending_points.phtml')
            ->toHtml();
        }
        return $result;
    }
}
