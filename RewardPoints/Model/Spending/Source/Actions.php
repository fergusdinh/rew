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

namespace Lof\RewardPoints\Model\Spending\Source;

use Lof\RewardPoints\Model\Spending;

class ProductEaringRuleSimpleAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Lof\RewardPoints\Model\Spending
     */
    protected $rewardsSpending;

    /**
     * @param \Lof\RewardPoints\Model\Spending $rewardsSpending
     */
    public function __construct(
        \Lof\RewardPoints\Model\Spending $rewardsSpending
    ) {
        $this->rewardsSpending = $rewardsSpending;
    }

    /**
     * @return arrat
     */
    public function toOptionArray()
    {
        $options = $this->rewardsSpending->getSpendingActions();
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
