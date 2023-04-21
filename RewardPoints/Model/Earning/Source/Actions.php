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

namespace Lof\RewardPoints\Model\Earning\Source;

class ProductEaringRuleSimpleAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Lof\RewardPoints\Model\Earning
     */
    protected $rewardsEarning;

    /**
     * @param \Lof\RewardPoints\Model\Earning $rewardsEarning
     */
    public function __construct(
        \Lof\RewardPoints\Model\Earning $rewardsEarning
    ) {
        $this->rewardsEarning = $rewardsEarning;
    }

    /**
     * @return arrat
     */
    public function toOptionArray()
    {
        $options = $this->rewardsEarning->getEarningActions();
        $result = [];

        foreach ($options as $key => $value) {
            $result[] = [
                'value' => $key,
                'label' => $value,
            ];
        }

        return $result;
    }
}
