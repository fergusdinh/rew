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

namespace Lof\RewardPoints\Model;

class Email extends \Magento\Framework\Model\AbstractModel
{

    /**
     * Transaction States
     */
    const STATE_FAIELD                = 'failed';

    const STATE_SENT                  = 'sent';

    const ACTION_BALANCE_UPDATE       = 'balance_update';

    const ACTION_POINTS_EXPIRE        = 'points_expire';

    const ACTION_POINTS_EXPIRED       = 'points_expired';

    const ACTION_EARN_POINTS          = 'earn_points';

    const ACTION_SPEND_POINTS         = 'spend_points';

    const ACTION_CANCEL_EARNED_POINTS = 'cancel_earn_points';

    const ACTION_CANCEL_SPENT_POINTS  = 'cancel_spend_points';

    const ACTION_APPLY_REWARD_CODE    = 'apply_reward_code';

    protected $rewardsTransaction;

    /**
     * @param \Magento\Framework\Model\Context                            $context
     * @param \Magento\Framework\Registry                                 $registry
     * @param \Lof\RewardPoints\Model\ResourceModel\Email|null            $resource
     * @param \Lof\RewardPoints\Model\ResourceModel\Email\Collection|null $resourceCollection
     * @param Transaction                                                 $rewardsTransaction
     * @param array                                                       $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\RewardPoints\Model\ResourceModel\Email $resource = null,
        \Lof\RewardPoints\Model\ResourceModel\Email\Collection $resourceCollection = null,
        Transaction $rewardsTransaction,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->rewardsTransaction = $rewardsTransaction;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Email');
    }

    public function getAvailableTrigger($empty = false)
    {
        $options = [
            self::ACTION_BALANCE_UPDATE             => __('Balance update'),
            self::ACTION_POINTS_EXPIRE              => __('Points expire'),
            self::ACTION_POINTS_EXPIRED             => __('Points expired'),
            self::ACTION_EARN_POINTS                => __('Earn Points'),
            self::ACTION_SPEND_POINTS               => __('Spend Points'),
            self::ACTION_CANCEL_EARNED_POINTS       => __('Cancel Earned Points'),
            self::ACTION_CANCEL_SPENT_POINTS        => __('Cancel Spent Points'),
            self::ACTION_APPLY_REWARD_CODE          => __('Apply Reward Code')
        ];
        if ($empty) {
            array_unshift($options, "");
        }
        return $options;
    }

    public function getTriggerLabel($trigger = '')
    {
        $label = '';
        if ($trigger == '') {
            $trigger = $this->getData('trigger');
        }

        $triggers = $this->getAvailableTrigger();
        foreach ($triggers as $key => $value) {
            if ($key == $trigger) {
                $label = $value;
                break;
            }
        }

        if ($this->rewardsTransaction->getActions($trigger)) {
            $label = $this->rewardsTransaction->getActions($trigger);
        }
        return $label;
    }
}
