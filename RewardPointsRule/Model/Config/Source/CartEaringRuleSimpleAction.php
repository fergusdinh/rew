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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Model\Config\Source;

use Lof\RewardPoints\Model\Earning;
use Lof\RewardPointsRule\Model\Earning as EarningRule;

class CartEaringRuleSimpleAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            Earning::ACTION_GIVE                          => __('Give X points to customer'),
            Earning::ACTION_AMOUNT_SPENT                  => __('Give X points for every spent Y'),
            Earning::ACTION_PERCENTAGE_BY_FINALPOINT_GIVE => __('Give X points as product final price'),
            Earning::ACTION_PERCENTAGE_BY_PRODUCT_PRICE   => __('Give X% of orginal price'),
            EarningRule::ACTION_PERCENTAGE_BY_CARTTOTAL   => __('Give X% of cart total'),
            EarningRule::ACTION_BY_CART_QTY               => __('Give X points for every Y qty')
        ];
        return $result;
    }

    /**
     * @return arrat
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
