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

namespace Lof\RewardPointsBehavior\Model\Config\Source;

use Lof\RewardPointsBehavior\Model\Earning;

class CustomerBehavior implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        $result = [
            Earning::BEHAVIOR_SIGNIN            => __('Customer signs in'),
            Earning::BEHAVIOR_SIGNUP            => __('Customer signs up'),
            Earning::BEHAVIOR_REFER_FRIEND      => __('Refer Friend'),
            Earning::BEHAVIOR_NEWSLETTER_SIGNUP => __('Newsletter sign up'),
            Earning::BEHAVIOR_REVIEW            => __('Customer writes a product\'s review'),
            Earning::BEHAVIOR_BIRTHDAY          => __('Customer birthday'),
            Earning::BEHAVIOR_FACEBOOK_LIKE     => __('Facebook Like'),
            Earning::BEHAVIOR_FACEBOOK_SHARE    => __('Facebook Share'),
            Earning::BEHAVIOR_TWITTER_TWEET     => __('Twitter Tweet'),
            Earning::BEHAVIOR_GOOGLEPLUS_LIKE   => __('Google Like'),
            Earning::BEHAVIOR_PRINTEREST_PIN    => __('Printerest Pin')
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

    public function getCustomerAtributes()
    {
        $attributes = [
            'name'               => __('Name'),
            'email'              => __('Email'),
            'billing_telephone'  => __('Phone'),
            'billing_postcode'   => __('ZIP'),
            'billing_country_id' => __('Country'),
            'billing_region'     => __('State/Province'),
            'created_at'         => __('Customer Since'),
            'confirmation'       => __('Confirmed Email'),
            'created_in'         => __('Account Created in'),
            'billing_full'       => __('Billing Address'),
            'shipping_full'      => __('Shipping Address'),
            'dob'                => __('Date of Birth'),
            'taxvat'             => __('Tax VAT Number'),
            'gender'             => __('Gender'),
            'billing_street'     => __('Street Address'),
            'billing_city'       => __('City'),
            'billing_fax'        => __('Fax'),
            'billing_vat_id'     => __('VAT Number'),
            'billing_company'    => __('Company'),
            'billing_firstname'  => __('Billing Firstname'),
            'billing_lastname'   => __('Billing Lastname')
        ];

        return $attributes;
    }
}
