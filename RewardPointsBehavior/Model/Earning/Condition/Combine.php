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

namespace Lof\RewardPointsBehavior\Model\Earning\Condition;

use Lof\RewardPointsBehavior\Model\Earning;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $conditionAddress;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context                             $context             
     * @param \Magento\Framework\Event\ManagerInterface                         $eventManager        
     * @param \Magento\CatalogRule\Model\Rule\Condition\ProductFactory          $conditionFactory    
     * @param \Magento\SalesRule\Model\Rule\Condition\Address                   $conditionAddress    
     * @param \Magento\SalesRule\Model\Rule\Condition\Product                   $ruleConditionProduct
     * @param \Lof\RewardPointsBehavior\Model\Earning\Condition\CustomerFactory $customerFactory     
     * @param \Lof\RewardPointsBehavior\Model\Earning\Condition\BehaviorFactory $behaviorFactory     
     * @param array                                                             $data                
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\CatalogRule\Model\Rule\Condition\ProductFactory $conditionFactory,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\CustomerFactory $customerFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\BehaviorFactory $behaviorFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\WishlistFactory $wishlistFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\ReviewFactory $reviewFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\OrderFactory $orderFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\ShippingAddressFactory $shippingAddressFactory,
        array $data = []
    ) {
        $this->conditionAddress       = $conditionAddress;
        $this->productFactory         = $conditionFactory;
        $this->ruleConditionProd      = $ruleConditionProduct;
        $this->customerFactory        = $customerFactory;
        $this->behaviorFactory        = $behaviorFactory;
        $this->wishlistFactory        = $wishlistFactory;
        $this->reviewFactory          = $reviewFactory;
        $this->orderFactory           = $orderFactory;
        $this->shippingAddressFactory = $shippingAddressFactory;
        parent::__construct($context, $data);
        $this->setType('Lof\RewardPoints\Model\Earning\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $rule       = $this->getRule();
        $ruleType   = $rule->getType();
        $conditions = '';

        $conditions = $this->_getCustomerBehaviorConditions();

        return $conditions;
    }

    protected function _getCustomerBehaviorConditions()
    {
        $attributes = [];
        $customerAttributes = $this->customerFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Customer|' . $code,
                'label' => $label,
            ];
        }

        $attributes2 = [];
        $customerRe = $this->behaviorFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes2[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Behavior|' . $code,
                'label' => $label,
            ];
        }

        $attributes3 = [];
        $customerRe = $this->wishlistFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes3[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Wishlist|' . $code,
                'label' => $label,
            ];
        }

        $attributes4 = [];
        $customerRe = $this->reviewFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes4[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Review|' . $code,
                'label' => $label,
            ];
        }

        $attributes5 = [];
        $customerRe = $this->orderFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes5[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Order|' . $code,
                'label' => $label,
            ];
        }

        $attributes6 = [];
        $customerRe = $this->shippingAddressFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($customerRe as $code => $label) {
            $attributes6[] = [
                'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\ShippingAddress|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'label' => __('Conditions Combination'),
                    'value' => 'Lof\RewardPointsBehavior\Model\Earning\Condition\Combine|' . Earning::BEHAVIOR
                ],
                [
                    'label' => __('Customer Attribute'),
                    'value' => $attributes
                ],
                [
                    'label' => __('Order'),
                    'value' => $attributes5
                ],
                [
                    'label' => __('Shipping Address'),
                    'value' => $attributes6
                ],
                [
                    'label' => __('Wishlist'),
                    'value' => $attributes3
                ],
                [
                    'label' => __('Review'),
                    'value' => $attributes4
                ]
            ]
        );
        return $conditions;
    }


    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

}