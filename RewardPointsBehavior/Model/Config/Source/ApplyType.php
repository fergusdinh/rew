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

namespace Lof\RewardPointsBehavior\Model\Config\Source;

use Lof\RewardPointsBehavior\Model\Earning;

class ApplyType implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_REGISTER    = 0;
    const TYPE_PLACE_ORDER = 1;
    const TYPE_BOTH        = 2;
    const TYPE_ADVANCED    = 3;
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            self::TYPE_REGISTER            => __('When referred register new account'),
            self::TYPE_PLACE_ORDER         => __('When referred place first valid order'),
            self::TYPE_BOTH                => __('Both register and place first valid order'),
            self::TYPE_ADVANCED                => __('Advanced by min qty orders, max qty orders')
        ];
        return $result;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->toArray();
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
