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

use Magento\Quote\Model\Quote\Address;

class Spending extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Rule Statues
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;
    const TYPE            = 'spending_rate';

    /**
     * Rule Actions
     */
    const ACTION_GIVE                           = 'spending_action_give';
    const ACTION_AMOUNT_SPENT                   = 'spending_action_amount_spent';
    const ACTION_QTY_SPENT                      = 'spending_action_qty_spent';
    const ACTION_PERCENTAGE_BY_PRODUCT_PRICE    = 'spending_action_percentage_by_product_price';
    const ACTION_PERCENTAGE_BY_FINALPOINT_GIVE  = 'spending_action_by_final_point_give';
    const ACTION_PERCENTAGE_BY_CARTTOTAL        = 'spending_action_by_cart_total';
    const ACTION_BY_CART_QTY                    = 'spending_action_by_cart_qty';


    /**
     * Earning Rule cache tag
     */
    const CACHE_TAG = 'rewardpoints_earning';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpoints_earning';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardpoints_earning';

    /**
     * Is model readonly
     *
     * @var bool
     */
    protected $_isReadonly = false;

    public function __construct(
        \Lof\RewardPoints\Model\Spending\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);

        $this->_condProdCombineF = $condProdCombineF;
        $this->_combineFactory = $condCombineFactory;
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Spending');
    }

    public function getSpendingActions($empty = false)
    {
        $options = [
            self::ACTION_AMOUNT_SPENT => __('Discount X points for every spent Y'),
        ];
        if ($empty) {
            array_unshift($options, "");
        }
        return $options;
    }

    /**
     * @return Lof\RewardPoints\Model\Spending\Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        $combine = $this->_combineFactory->create();
        return $combine;
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        $actions = $this->_condProdCombineF->create();
        return $actions;
    }

    /**
     * Return id for address
     *
     * @param Address $address
     * @return string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Address) {
            return $address->getId();
        }
        return $address;
    }


    /**
     * Set validation result for specific address to results cache
     *
     * @param Address $address
     * @param bool $validationResult
     * @return $this
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;
        return $this;
    }

    /**
     * Check if rule is readonly
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }

    /**
     * Get Rule statues
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED  => __('Active'),
            self::STATUS_DISABLED => __('Inactive')
        ];
    }

    /**
     * Validate rule data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[] - return true if validation passed successfully. Array with errors description otherwise
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        $fromDate = $toDate = null;

        if ($dataObject->hasActiveFrom() && $dataObject->hasActiveTo()) {
            $fromDate = $dataObject->getActiveFrom();
            $toDate = $dataObject->getActiveTo();
        }

        if ($fromDate && $toDate) {
            $fromDate = new \DateTime($fromDate);
            $toDate = new \DateTime($toDate);
            if ($fromDate > $toDate) {
                $result[] = __('End Date must follow Start Date.');
            }
        }

        return !empty($result) ? $result : true;
    }
}
