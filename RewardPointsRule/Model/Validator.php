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
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Validator extends \Magento\SalesRule\Model\Validator
{
    /**
     * @var \Lof\RewardPointsRule\Model\RulesApplier
     */
    protected $rulesApplier;

    /**
     * @var \Lof\RewardPointsRule\Helper\Balance\Earn
     */
    protected $_rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPointsRule\Helper\Balance\Spend
     */
    protected $_rewardsBalanceSpend;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param   \Magento\Framework\Model\Context $context,
     * @param    \Magento\Framework\Registry $registry,
     * @param    \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory1,
     * @param    \Lof\RewardPointsRule\Model\ResourceModel\Earning\CollectionFactory $collectionFactory,
     * @param    \Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn,
     * @param    \Lof\RewardPointsRule\Helper\Balance\Spend $rewardsBalanceSpend,
     * @param    \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
     * @param    \Magento\Customer\Model\Session $customerSession,
     * @param    \Magento\Catalog\Helper\Data $catalogData,
     * @param    \Magento\SalesRule\Model\Utility $utility,
     * @param    \Lof\RewardPointsRule\Model\RulesApplier $rewardpointRulesApplier,
     * @param    \Magento\SalesRule\Model\RulesApplier $rulesApplier,
     * @param    \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
     * @param    \Magento\SalesRule\Model\Validator\Pool $validators,
     * @param    \Magento\Framework\Message\ManagerInterface $messageManager,
     * @param    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
     * @param    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
     * @param    array $data = []
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory1,
        \Lof\RewardPointsRule\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPointsRule\Helper\Balance\Spend $rewardsBalanceSpend,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\SalesRule\Model\Utility $utility,
        \Lof\RewardPointsRule\Model\RulesApplier $rewardpointRulesApplier,
        \Magento\SalesRule\Model\RulesApplier $rulesApplier,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\SalesRule\Model\Validator\Pool $validators,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct($context, $registry, $collectionFactory1, $catalogData, $utility, $rulesApplier, $priceCurrency, $validators, $messageManager, $resource, $resourceCollection, $data);
        $this->rulesApplier         = $rewardpointRulesApplier;
        $this->_rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->_rewardsBalanceSpend = $rewardsBalanceSpend;
        $this->rewardsPurchase      = $rewardsPurchase;
        $this->_customerSession     = $customerSession;
    }

    /**
     * Quote item discount calculation process
     *
     * @param AbstractItem $item
     * @return array
     */
    public function processEarningCartRules(AbstractItem $item)
    {

        // $item->setDiscountAmount(0);
        // $item->setBaseDiscountAmount(0);
        // $item->setDiscountPercent(0);

        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = $this->rulesApplier->applyRules(
            $item,
            $this->_getEarningCartRules($item->getAddress()),
            $this->_skipActionsValidation,
            $this->getCouponCode()
            );

        return $appliedRuleIds;
    }


    /**
     * Quote item discount calculation process
     *
     * @param AbstractItem $item
     * @return array
     */
    public function processSpendingCartRules(AbstractItem $item)
    {
        // $item->setDiscountAmount(0);
        // $item->setBaseDiscountAmount(0);
        // $item->setDiscountPercent(0);

        $itemPrice = $this->getItemPrice($item);
        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = $this->rulesApplier->applyRules(
            $item,
            $this->_getSpendingCartRules($item->getAddress()),
            $this->_skipActionsValidation,
            $this->getCouponCode()
        );

        return $appliedRuleIds;
    }


    /**
     * Apply discounts to shipping amount
     *
     * @param Address $address
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processShippingAmount(Address $address)
    {
        $purchase = $this->rewardsPurchase->getPurchase();
        $quote = $address->getQuote();
        $appliedRuleIds = [];
        foreach ($this->_getSpendingCartRules($address) as $rule) {
            /* @var \Magento\SalesRule\Model\Rule $rule */
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            $spendPoints = $purchase->getSpendPoints();
            $address->setShippingDiscountAmount($spendPoints);
            $address->setBaseShippingDiscountAmount($spendPoints);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
            $this->rulesApplier->maintainAddressCouponCode($address, $rule, $this->getCouponCode());
            $this->rulesApplier->addDiscountDescription($address, $rule);
        }
        $address->setAppliedRuleIds($this->validatorUtility->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->validatorUtility->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));
        return $this;
    }

    /**
     * Get rules collection for current object state
     *
     * @param Address|null $address
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function _getEarningCartRules(Address $address = null)
    {
        $addressId = $this->getAddressId($address);
        $key = $this->getWebsiteId() . '_'
        . $this->getCustomerGroupId() . '_'
        . $this->getRuleId() . '_'
        . $addressId;
        if (!isset($this->_rules[$key])) {
        	$customerGroupId = $this->_customerSession->getCustomerGroupId();
        	$collection = $this->_rewardsBalanceEarn->getRules(\Lof\RewardPointsRule\Model\Earning::CART_RULE, $customerGroupId);
            foreach ($collection as $rule) {
               if( $rule->getIsStopProcessing() ) {
                    $collection->addFieldToFilter('rule_id', $rule->getId());
                    break;
               }
            }
            $this->_rules[$key] = $collection;
        }
        return $this->_rules[$key];
    }

    /**
     * Get rules collection for current object state
     *
     * @param Address|null $address
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function _getSpendingCartRules(Address $address = null)
    {
        $addressId = $this->getAddressId($address);
        $key = $this->getWebsiteId() . '_'
        . $this->getCustomerGroupId() . '_'
        . $this->getRuleId() . '_'
        . $addressId;

        if (!isset($this->_rules[$key])) {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
            $collection = $this->_rewardsBalanceSpend->getRules(\Lof\RewardPointsRule\Model\Spending::CART_RULE, $customerGroupId);
            foreach ($collection as $rule) {
               if( $rule->getIsStopProcessing() ) {
                    $collection->addFieldToFilter('rule_id', $rule->getId());
                    break;
               }
            }
            $this->_rules[$key] = $collection;
        }
        return $this->_rules[$key];
    }
}
