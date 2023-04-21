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

namespace Lof\RewardPoints\CustomerData;

class Points implements \Magento\Customer\CustomerData\SectionSourceInterface
{
    /**
     * @var Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Lof\RewardPoints\Helper\Customer $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Data     $rewardsData
     */
	public function __construct(
		\Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData
	) {
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsData     = $rewardsData;
	}

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        if($customer = $this->rewardsCustomer->setForceSave(true)->getCustomer()) {
        	$totalPoints = (float) $customer->getTotalPoints();
        	return [
        		'totalpoints' => $this->rewardsData->formatPoints($totalPoints, true, false)
        	];
        } else {
            return [
                'totalpoints' => 0
            ];
        }
    }
}
