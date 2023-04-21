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

namespace Lof\RewardPoints\Model\Transaction\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Lof\RewardPoints\Model\Transaction;

class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray($status = '')
    {
        $options = [
            [
                'label' => __('New'),
                'value' => Transaction::STATE_NEW
            ],
            [
                'label' => __('Processing'),
                'value' => Transaction::STATE_PROCESSING
            ],
            [
                'label' => __('Complete'),
                'value' => Transaction::STATE_COMPLETE
            ],
            [
                'label' => __('Closed'),
                'value' => Transaction::STATE_CLOSED
            ],
            [
                'label' => __('Canceled'),
                'value' => Transaction::STATE_CANCELED
            ],
            [
                'label' => __('Holded'),
                'value' => Transaction::STATE_HOLDED
            ]
        ];

        if ($status) {
        	foreach ($options as $option) {
        		if ($option['value'] == $status) {
        			return $option['label'];
        		}
        	}
        }
        return $options;
    }
}
