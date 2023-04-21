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

use \Lof\RewardPoints\Model\Config;

class RedeemCode extends \Magento\Framework\View\Element\Template
{

    /**
     * [__construct description]
     * @param \Magento\Framework\View\Element\Template\Context $context     [description]
     * @param array                                            $data        [description]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context);
    }

    public function getApplyCodeUrl()
    {
        return $this->getUrl(Config::ROUTES . '/redeemcode/applycode');
    }
}