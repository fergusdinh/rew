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

class Actions implements OptionSourceInterface
{
    /**
     * @var \Lof\RewardPoints\Model\Transaction
     */
    protected $rewardsTransaction;

    /**
     * @param \Lof\RewardPoints\Model\Transaction $rewardsTransaction [description]
     */
    public function __construct(
        \Lof\RewardPoints\Model\Transaction $rewardsTransaction
    ) {
        $this->rewardsTransaction = $rewardsTransaction;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray($status = '')
    {
        $options = $this->rewardsTransaction->getActions();
        $result = [];
        foreach ($options as $key => $label) {
            $result[] = [
                'label' => $label,
                'value' => $key
            ];
        }
        if ($status) {
        	foreach ($options as $key => $label) {
        		if ($key == $status) {
        			return $label;
        		}
        	}
        }
        return $result;
    }
}
