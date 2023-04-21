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

class Earning extends \Magento\Rule\Model\AbstractModel
{
    /**
     * Rule Statues
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;
    const TYPE            = 'earning_rate';

    /**
     * Rule Actions
     */
    const ACTION_GIVE                           = 'earning_action_give';
    const ACTION_AMOUNT_SPENT                   = 'earning_action_amount_spent';
    const ACTION_QTY_SPENT                      = 'earning_action_qty_spent';
    const ACTION_PERCENTAGE_BY_PRODUCT_PRICE    = 'earning_action_percentage_by_product_price';
    const ACTION_PERCENTAGE_BY_FINALPOINT_GIVE  = 'earning_action_by_final_point_give';
    const ACTION_PERCENTAGE_BY_CARTTOTAL        = 'earning_action_by_cart_total';
    const ACTION_BY_CART_QTY                    = 'earning_action_by_cart_qty';
    const ACTION_PERCENTAGE_BY_ORGINAL          = 'earning_action_by_orginal';


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

    /**
     * AbstractModel constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Lof\RewardPoints\Model\Earning\Condition\CombineFactory $condCombineFactory,
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
        $this->_init('Lof\RewardPoints\Model\ResourceModel\Earning');
    }

    /**
     * @return Lof\RewardPoints\Model\Earning\Condition\Combine
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

    public function getEarningActions($empty = false)
    {
        $options = [
            self::ACTION_GIVE                          => __('Give X points to customer'),
            self::ACTION_AMOUNT_SPENT                  => __('Give X points for every spent Y'),
            self::ACTION_PERCENTAGE_BY_FINALPOINT_GIVE => __('Give X points as product final price'),
            self::ACTION_PERCENTAGE_BY_PRODUCT_PRICE   => __('Give X% points of orginal price')
        ];
        if ($empty) {
            array_unshift($options, "");
        }
        return $options;
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
}
