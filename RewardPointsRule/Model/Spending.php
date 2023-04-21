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
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Model;

use Magento\Quote\Model\Quote\Address;

class Spending extends \Lof\RewardPoints\Model\Spending
{
    /**
     * Rule Statues
     */
    const STATUS_ENABLED  = 1;
    const STATUS_DISABLED = 0;
    const TYPE            = 'type';

    const ACTION_AMOUNT_SPENT                  = 'spending_action_amount_spent';
    const ACTION_PERCENTAGE_BY_PRODUCT_PRICE   = 'spending_action_percentage_by_product_price';
    const ACTION_PERCENTAGE_BY_ORGINAL         = 'spending_action_percentage_by_orginal';

    /**
     * Rule Types
     */
    const PRODUCT_RULE       = 'product';
    const CART_RULE          = 'cart';

    /**
     * Earning Rule cache tag
     */
    const CACHE_TAG = 'rewardpointsrule_spending';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpointsrule_spending';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardpointsrule_spending';


    public function __construct(
        \Lof\RewardPoints\Model\Spending\Condition\CombineFactory $condCombineFactory,
        \Lof\RewardPointsRule\Model\Spending\Condition\CombineFactory $condCombineFactory1,
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
        $this->_condProdCombineF = $condProdCombineF;
        $this->_combineFactory = $condCombineFactory1;
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
        $this->_init('Lof\RewardPointsRule\Model\ResourceModel\Spending');
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
