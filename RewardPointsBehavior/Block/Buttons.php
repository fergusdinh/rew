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

namespace Lof\RewardPointsBehavior\Block;

class Buttons extends \Lof\RewardPointsBehavior\Block\Buttons\AbstractButtons
{
	public function _toHtml()
    {
    	if (!$this->rewardsConfig->isEnable()) {
    		return false;
    	}
    	if (!$this->rewardsData->isLoggedIn()) {
    		return false;
    	}
    	return parent::_toHtml();
	}
}