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

namespace Lof\RewardPoints\Model\Email\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Lof\RewardPoints\Model\Email;

class Actions implements OptionSourceInterface
{
    /**
     * @var \Lof\RewardPoints\Model\Email
     */
    protected $rewardsEmail;

    /**
     * @param \Lof\RewardPoints\Model\Email $rewardsEmail [description]
     */
    public function __construct(
        \Lof\RewardPoints\Model\Email $rewardsEmail
    ) {
        $this->rewardsEmail = $rewardsEmail;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray($status = '')
    {
        $options = $this->rewardsEmail->getAvailableTrigger();
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
