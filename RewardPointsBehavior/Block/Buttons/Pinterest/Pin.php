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

namespace Lof\RewardPointsBehavior\Block\Buttons\Pinterest;

use Lof\RewardPointsBehavior\Model\Config;

class Pin extends \Lof\RewardPointsBehavior\Block\Buttons\AbstractButtons
{
	public function getPinUrl()
	{
		return $this->getUrl(Config::ROUTES . '/pinterest/pin');
	}

	public function getProduct(){
		$product = $this->rewardsData->getCurrentProduct();
		return $product;
	}

	public function getProductDescription()
	{
		$product = $this->getProduct();
		if(!$product) {
			return;
		}
		$description = $product->getShortDescription();
		$description = $this->rewardsData->filter($description);
		return $description;
	}

	public function getProductImage()
	{
		$product = $this->getProduct();
		if(!$product) return;
		$image = $this->catalogImage->init($product, 'product_base_image')->getUrl();
		return $image;
	}

}