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

namespace Lof\RewardPointsBehavior\Model;

use Magento\Quote\Model\Quote\Address;

class Earning extends \Lof\RewardPoints\Model\Earning
{
    const BEHAVIOR                     = 'behavior';
        
    const BEHAVIOR_SIGNIN              = 'signin';
    const BEHAVIOR_SIGNUP              = 'signup';
    const BEHAVIOR_NEWSLETTER_SIGNUP   = 'newsletter_signup';
    const BEHAVIOR_NEWSLETTER_UNSIGNUP = 'newsletter_un_signup';
    const BEHAVIOR_REVIEW              = 'review';
    const BEHAVIOR_BIRTHDAY            = 'birthday';
    const BEHAVIOR_REFER_FRIEND        = 'refer_friend';
    const BEHAVIOR_FACEBOOK_SHARE      = 'facebook_share';
    const BEHAVIOR_FACEBOOK_LIKE       = 'facebook_like';
    const BEHAVIOR_FACEBOOK_UNLIKE     = 'facebook_unlike';
    const BEHAVIOR_TWITTER_TWEET       = 'twitter_tweet';
    const BEHAVIOR_GOOGLEPLUS_LIKE     = 'googleplus_like';
    const BEHAVIOR_GOOGLEPLUS_UNLIKE   = 'googleplus_unlike';
    const BEHAVIOR_PRINTEREST_PIN      = 'pinterest_pin';

    /**
     * Earning Rule cache tag
     */
    const CACHE_TAG = 'rewardpointsbehavior_earning';

    /**
     * @var string
     */
    protected $_cacheTag = 'rewardpointsbehavior_earning';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rewardpointsbehavior_earning';


    public function __construct(
        \Lof\RewardPoints\Model\Earning\Condition\CombineFactory $condCombineFactory,
        \Lof\RewardPointsBehavior\Model\Earning\Condition\CombineFactory $condCombineFactory1,
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
        $this->_init('Lof\RewardPointsBehavior\Model\ResourceModel\Earning');
    }

    public function getEarnPointsByBehaviors(){
        $result = ["message"=>"Cannot get data", "earn_points" => null];
        if(isset($_GET["customer_id"]) && isset($_GET["behavior"])){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $customer_obj = $objectManager->get("Magento\Customer\Api\CustomerRepositoryInterface");
            $customer_data = $customer_obj->getById($_GET["customer_id"]);
            $helper = $objectManager->get("Lof\RewardPointsBehavior\Helper\Behavior");
            $post_data = $helper->processRule($_GET["behavior"], $customer_data);
            $result = ["message"=>"Get data success", "earn_points" => ($post_data > 0)? $post_data : 0];
        }
        return json_encode($result, true);
    }
}
