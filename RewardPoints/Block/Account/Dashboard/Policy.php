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

namespace Lof\RewardPoints\Block\Account\Dashboard;

class Policy extends \Magento\Framework\View\Element\Template
{
	/**
	 * @var \Magento\Cms\Model\PageFactory
	 */
	protected $pageFactory;

	/**
	 * @var \Lof\RewardPoints\Helper\Data
	 */
	protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context     
     * @param \Magento\Cms\Model\PageFactory                   $pageFactory 
     * @param \Lof\RewardPoints\Helper\Data                    $rewardsData 
     * @param array                                            $data        
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context);
        $this->pageFactory   = $pageFactory;
        $this->rewardsData   = $rewardsData;
        $this->rewardsConfig = $rewardsConfig;
    }

    public function _toHtml()
    {
    	if (!$this->rewardsConfig->isUseRewardsPolicyPage()) {
            return;
        }
		$policyPageId      = $this->rewardsConfig->getRewardsPolicyPageId();
		$policyPageContent = $this->rewardsData->filter($this->pageFactory->create()->load($policyPageId)->getContent());
    	return $policyPageContent;
    }
}