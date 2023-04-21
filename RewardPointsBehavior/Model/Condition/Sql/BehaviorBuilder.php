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

namespace Lof\RewardPointsBehavior\Model\Condition\Sql;

use Magento\Framework\DB\Select;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Lof\RewardPointsBehavior\Model\Earning\Condition\Customer;
use Lof\RewardPointsBehavior\Model\Earning\Condition\Wishlist;
use Lof\RewardPointsBehavior\Model\Earning\Condition\Review;
use Lof\RewardPointsBehavior\Model\Earning\Condition\Order;
use Lof\RewardPointsBehavior\Model\Earning\Condition\ShippingAddress;

class BehaviorBuilder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    protected $customer;

    /**
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Rule\Model\Condition\Sql\ExpressionFactory $expressionFactory
     * @param \Lof\RewardPointsBehavior\Helper\Data               $rewardsData      
     * @param \Lof\RewardPoints\Helper\Customer                   $rewardsCustomer  
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Sql\ExpressionFactory $expressionFactory,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData,
        \Lof\RewardPointsBehavior\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($expressionFactory);
        $this->rewardsData     = $rewardsData;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger   = $rewardsLogger;
    }

    /**
     * @var array
     */
    protected $_conditionOperatorMap = [
        '=='    => ':field = ?',
        '!='    => ':field <> ?',
        '>='    => ':field >= ?',
        '>'     => ':field > ?',
        '<='    => ':field <= ?',
        '<'     => ':field < ?',
        '{}'    => ':field LIKE (CONCAT(\'%\',?,\'%\'))',
        '!{}'   => ':field NOTLIKE (CONCAT(\'%\',?,\'%\'))',
        '()'    => ':field LIKE (CONCAT(\'%\',?,\'%\'))',
        '!()'   => ':field NOT LIKE (CONCAT(\'%\',?,\'%\'))',
    ];

    protected function getCustomerRe($column, $value, $operator)
    {
        $customer = $this->customer;
        $isRe = false;
        switch ($column) {

            case Wishlist::WISHLIST_PRODUCTS_QUANTITY:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getWishListTotalProductsQuantity($customer);
                break;

            case Wishlist::WISHLIST_TOTAL_PRODUCT_COUNT:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getWishListTotalProductsCount($customer);
                break;

            case Wishlist::WISHLIST_SUBTOTAL:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getWishListTotal($customer);
                break;

            case Review::REVIEWS_NUMBER:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getTotalReviewsByCustomer($customer);
                break;

            case Order::ORDERS_NUMBER:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getTotalOrdersByCustomer($customer);
                break;

            case Order::ORDER_PRODUCTS_QUANTITY:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getTotalOrderProductQuantityByCustomer($customer);
                break;

            case Order::ORDER_TOTAL_PRODUCT_COUNT:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getTotalOrderProductByCustomer($customer);
                break;

            case Order::ORDER_GRANDTOTAL:
                $isRe = true;
                $column = (int) $this->rewardsCustomer->getOrderGrandTotalByCustomer($customer, $value, $operator, 'grand_total');
                break;

            case Order::ORDER_SHIPPINGMETHOD:
                $isRe = true;
                $column = $this->rewardsCustomer->getOrderShippingByCustomer($customer, $value, $operator, 'shipping_method');
                break;

            case Customer::CUSTOMER_LIFETIME_SALES:
                $isRe = true;
                $column = $this->rewardsCustomer->getTotalSalesByCustomer($customer);
                break;
        }

        if(!$isRe){
            $column = $this->_connection->getIfNullSql($column, 0);
        }
        return $column;
    }

    public function setCustomer($customer) {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @param AbstractCondition $condition
     * @param string $value
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlCondition(AbstractCondition $condition, $value = '', bool $isDefaultStoreUsed = true)
     :string{
        $argument = $condition->getMappedSqlField();
        if ($argument) {
            $conditionOperator = $condition->getOperatorForValidate();

            if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
            }
            $customer = $this->customer;

            try {
                switch ($argument) {
                    case ShippingAddress::SHIPPING_ADDRESS_COUNTRY:
                    case ShippingAddress::SHIPPING_ADDRESS_CITY:
                    case ShippingAddress::SHIPPING_ADDRESS_STATE:
                    case ShippingAddress::SHIPPING_ADDRESS_REGION:
                    case ShippingAddress::SHIPPING_ADDRESS_POSTCODE:
                        $result = $this->rewardsCustomer->getOrderShippingFull($customer, $condition->getBindArgumentValue(), $conditionOperator, $argument);
                    break;

                    default:
                        $column = $this->getCustomerRe($argument, $condition->getBindArgumentValue(), $conditionOperator);

                        $sql = str_replace(
                            ':field',
                            $column,
                            $this->_conditionOperatorMap[$conditionOperator]
                        );
                        $result = $this->_expressionFactory->create(
                            ['expression' => $value . $this->_connection->quoteInto($sql, $condition->getBindArgumentValue())]
                        );
                        break;
                }
            } catch (\Exception $e) {
                $this->rewardsLogger->addError($e->getMessage());
            }
            return $result;
        }
        return '';
    }

    /**
     * @param Combine $combine
     * @param string $value
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getMappedSqlCombination(Combine $combine, $value = '', bool $isDefaultStoreUsed = true)
    :string{
        $out = (!empty($value) ? $value : '');
        $value = ($combine->getValue() ? '' : ' NOT ');

        $getAggregator = $combine->getAggregator();
        $conditions = $combine->getConditions();

        foreach ($conditions as $key => $condition) {
            /** @var $condition AbstractCondition|Combine */
            $con = ($getAggregator == 'any' ? Select::SQL_OR : Select::SQL_AND);
            $con = (isset($conditions[$key+1]) ? $con : '');

            if ($condition instanceof Combine) {
                $out .= $this->_getMappedSqlCombination($condition, $value,$isDefaultStoreUsed);
            } else {
                $out .= $this->_getMappedSqlCondition($condition, $value,$isDefaultStoreUsed);
            }
            $out .=  $out ? (' ' . $con) : '';
        }
        return $this->_expressionFactory->create(['expression' => $out]);
    }

    /**
     * Attach conditions filter to collection
     *
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
     * @param Combine $combine
     *
     * @return void
     */
    public function attachConditionToCollection(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection,
        Combine $combine
    ) :void{
        $this->_connection = $collection->getResource()->getConnection();
        $this->_joinTablesToCollection($collection, $combine);
        $whereExpression = (string)$this->_getMappedSqlCombination($combine);

        if (!empty($whereExpression)) {
            // Select ::where method adds braces even on empty expression
            $collection->getSelect()->where($whereExpression);
        }
    }
}