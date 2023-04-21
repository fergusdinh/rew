<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\RewardPoints\Block\Adminhtml\Transaction\Renderer;

use Magento\Framework\DataObject;

class Points extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    public function render(DataObject $row)
    {
    	$amount = $row['amount'];
        if ($amount > 0) {
            $amount = '+' . $amount;
        }
    	$status = '<span class="lrw-status-' . $row['status'] . '">' . $amount . '</span>';
    	return $status;
    }
}
