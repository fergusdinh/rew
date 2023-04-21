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

namespace Lof\RewardPoints\Block\Account\Dashboard\Link;

class Policy extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context     
     * @param \Magento\Framework\App\DefaultPathInterface      $defaultPath
     * @param \Lof\RewardPoints\Model\Config                   $rewardsConfig 
     * @param array                                            $data        
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath);
        $this->rewardsConfig = $rewardsConfig;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->rewardsConfig->isUseRewardsPolicyPage()) {
            return;
        }
        return parent::_toHtml();
    }
}