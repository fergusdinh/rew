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

namespace Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Field;

class Message extends \Magento\Framework\Data\Form\Element\AbstractElement
{

	public function getHtml()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$path = 'lof/rewardpoints/default/catalog_conditions.png';
		$condition_image_url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $path;
		$html = '
	 	<div class="lrw-messages message-system-inner">
	 		<div class="message">
			'.__('1. If you want to apply the rule to products that cost more than <span class="price">$200.00</span> and have brand Apple, the condition is:').'            <div style="margin-left: 50px">
			<img title="'.__('conditons').'" alt="'.__('conditons').'" src="'.$condition_image_url.'">
			</div>
		</div>
		';
		return $html;
	}
}
