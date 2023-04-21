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

class Order extends \Magento\Rule\Model\Condition\AbstractCondition
{

    const ORDERS_NUMBER             = 'orders_num';
    const ORDER_PRODUCTS_QUANTITY   = 'order_total_products_quantity';
    const ORDER_TOTAL_PRODUCT_COUNT = 'order_total_product_count';
    const ORDER_GRANDTOTAL          = 'order_grandtotal';
    const ORDER_SHIPPINGMETHOD      = 'order_shippingmethod';

    /**
     * @param \Magento\Rule\Model\Condition\Context          $context 
     * @param \Magento\Directory\Model\Config\Source\Country $country 
     * @param \Magento\Customer\Block\Widget\Gender          $gender  
     * @param array                                          $data    
     */
    public function __construct(
       \Magento\Rule\Model\Condition\Context $context,
       \Magento\Directory\Model\Config\Source\Country $country,
       \Magento\Customer\Block\Widget\Gender $gender,
    	array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->_country                 = $country;
        $this->_gender                  = $gender;
    }

    public function loadCustomerReOptions(){
    	$attributes = [
            self::ORDERS_NUMBER             => __('Total orders'),
            self::ORDER_PRODUCTS_QUANTITY   => __('Total quantity of products'),
            self::ORDER_TOTAL_PRODUCT_COUNT => __('Total count of products'),
            self::ORDER_GRANDTOTAL          => __('Grand Total'),
            self::ORDER_SHIPPINGMETHOD      => __('Shipping Method')
    	];
    	$this->setAttributeOption($attributes);
    	return $this;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
    	$attributes = [
            self::ORDERS_NUMBER             => __('Total orders'),
            self::ORDER_PRODUCTS_QUANTITY   => __('Total quantity of products'),
            self::ORDER_TOTAL_PRODUCT_COUNT => __('Total count of products'),
            self::ORDER_GRANDTOTAL          => __('Grand Total'),
            self::ORDER_SHIPPINGMETHOD      => __('Shipping Method')
        ];
        $this->setAttributeOption($attributes);
    	return $this;
    }

    public function getInputType()
    {
    	$type = 'string';
    	// switch ($this->getAttribute()) {
    	// 	case self::CUSTOMER_BILLING_COUNTRY_ID:
    	// 	case self::CUSTOMER_GENDER:
    	// 		$type = 'select';
    	// 		break;

    	// 	default:
    	// 		$type = 'string';
    	// 		break;
    	// }

    	return $type;
    }

    /**
     * Retrieve value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
    	$type = 'text';
    	// switch ($this->getAttribute()) {
    	// 	case self::CUSTOMER_BILLING_COUNTRY_ID:
    	// 	case self::CUSTOMER_GENDER:
    	// 		$type = 'select';
    	// 		break;

    	// 	default:
    	// 		$type = 'text';
    	// 		break;
    	// }

    	return $type;
    }

    /**
     * Retrieve value by option.
     *
     * @param string $option
     *
     * @return string
     */
    public function getValueOption($option = null)
    {
        $this->_prepareValueOptions();

        return $this->getData('value_option'.($option !== null ? '/'.$option : ''));
    }

    protected function _prepareValueOptions()
    {
        // $selectOptions = [];
        // if ($this->getAttribute() === self::CUSTOMER_BILLING_COUNTRY_ID) {
        // 	$selectOptions = $this->_country->toOptionArray(true);
        // }
        // if ($this->getAttribute() === self::CUSTOMER_GENDER) {
        // 	$genderOptions = $this->_gender->getGenderOptions();

        // 	foreach ($genderOptions as $k => $v) {
        // 		$selectOptions[] = [
        // 			'value' => $v->getValue(),
        // 			'label' => $v->getLabel()
        // 		];
        // 	}
        // }

        // $this->setData('value_select_options', $selectOptions);

        // $hashedOptions = [];
        // foreach ($selectOptions as $o) {
        //     $hashedOptions[$o['value']] = $o['label'];
        // }
        // $this->setData('value_option', $hashedOptions);

        return $this;
    }
}