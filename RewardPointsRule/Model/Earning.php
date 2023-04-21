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

namespace Lof\RewardPointsRule\Model;

use Magento\Quote\Model\Quote\Address;

class Earning extends \Lof\RewardPoints\Model\Earning
{
    /**
     * Rule Statues
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;
    /*
     * OLD
    */
    // const TYPE            = 'type';
    /*  NEW  */
    const TYPE            = 'type';

    /**
     * Rule Types
     */
    const PRODUCT_RULE                   = 'product';
    const CART_RULE                      = 'cart';

    const ACTION_PERCENTAGE_BY_CARTTOTAL = 'earning_action_by_cart_total';
    const ACTION_BY_CART_QTY             = 'earning_action_by_cart_qty';

    /**
     * Earning Rule cache tag
     */
    const CACHE_TAG = 'rewardpointsrule_earning';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpointsrule_earning';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardpointsrule_earning';

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory
     */
    protected $_condProdCombineF;

    /**
     * @var \Lof\RewardPointsRule\Model\Earning\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
     * @param \Lof\RewardPoints\Model\Earning\Condition\CombineFactory       $condCombineFactory
     * @param \Lof\RewardPointsRule\Model\Earning\Condition\CombineFactory   $condCombineFactory1
     * @param \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF
     * @param \Magento\Framework\Model\Context                               $context
     * @param \Magento\Framework\Registry                                    $registry
     * @param \Magento\Framework\Data\FormFactory                            $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null   $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null             $resourceCollection
     * @param array                                                          $data
     */
    public function __construct(
        \Lof\RewardPoints\Model\Earning\Condition\CombineFactory $condCombineFactory,
        \Lof\RewardPointsRule\Model\Earning\Condition\CombineFactory $condCombineFactory1,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($condCombineFactory, $condProdCombineF, $context, $registry, $formFactory, $localeDate, $resource, $resourceCollection);
        $this->_combineFactory   = $condCombineFactory1;
        $this->_condProdCombineF = $condProdCombineF;
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

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\RewardPointsRule\Model\ResourceModel\Earning');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    public function setType($type)
    {
        if((self::PRODUCT_RULE != $type) && (self::CART_RULE != $type))
        {
            $type = self::PRODUCT_RULE;
        }
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get Rule types
     *
     * @return array
     */
    protected function getRuleTYpe()
    {
        return [
            self::PRODUCT_RULE => __('Product Rule'),
            self::CART_RULE    => __('Cart Rule')
        ];
    }

    public function convertSerializedDataToJson(){
        $this->getResource()->convertSerializedDataToJson();
    }
    /**
     * Getter for conditions field set ID
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }
    /**
     * Get actions field set id.
     *
     * @param string $formName
     * @return string
     * @since 100.1.0
     */
    public function getActionsFieldSetId($formName = '')
    {
        return $formName . 'rule_actions_fieldset_' . $this->getId();
    }
}
