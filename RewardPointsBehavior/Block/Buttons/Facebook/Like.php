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

namespace Lof\RewardPointsBehavior\Block\Buttons\Facebook;

use Lof\RewardPointsBehavior\Model\Config;

class Like extends \Lof\RewardPointsBehavior\Block\Buttons\AbstractButtons
{
	
	public function getAppId()
	{
		return $this->rewardsConfig->getFacebookApiKey();
	}

	public function getLikeUrl()
	{
		return $this->getUrl(Config::ROUTES . '/facebook/like');
	}

	public function getUnlikeUrl()
	{
		return $this->getUrl(Config::ROUTES . '/facebook/unlike');
	}

	public function getShareUrl()
	{
		return $this->getUrl(Config::ROUTES . '/facebook/share');
	}
}