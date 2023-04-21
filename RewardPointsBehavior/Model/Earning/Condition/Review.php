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

class Review extends \Magento\Rule\Model\Condition\AbstractCondition
{
    const REVIEWS_NUMBER = 'reviews_num';

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
            self::REVIEWS_NUMBER => __('Total Reviews')
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
            self::REVIEWS_NUMBER => __('Total Reviews')
        ];
        $this->setAttributeOption($attributes);
    	return $this;
    }

    public function getInputType()
    {
    	$type = 'string';
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
        return $this;
    }
}