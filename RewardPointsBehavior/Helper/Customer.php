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

namespace Lof\RewardPointsBehavior\Helper;

use \Magento\Sales\Model\Order;
use Lof\RewardPointsBehavior\Model\Earning\Condition\ShippingAddress;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\Collection
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Sale\Collection
     */
    protected $saleCollectionFactory;

    /**
     * @var [\Magento\Wishlist\Model\Wishlist
     */
    protected $wishlistFactory;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Framework\App\Helper\Context                        $context                 
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory   $orderCollectionFactory  
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory 
     * @param \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory    $saleCollectionFactory   
     * @param \Magento\Wishlist\Model\WishlistFactory                      $wishlistFactory         
     * @param \Lof\RewardPoints\Logger\Logger                              $rewardsLogger           
     */
    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory $saleCollectionFactory,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
        ) {
        parent::__construct($context);
        $this->orderCollectionFactory  = $orderCollectionFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->saleCollectionFactory   = $saleCollectionFactory;
        $this->wishlistFactory         = $wishlistFactory;
        $this->rewardsLogger           = $rewardsLogger;
    }

    public function getTotalReviewsByCustomer($customer)
    {
        $collection = $this->reviewCollectionFactory->create()
        ->addFieldToFilter('customer_id', $customer->getId());
        $totalReviews = $collection->count();
        return $totalReviews;
    }

    public function getTotalSalesByCustomer($customer)
    {
        try {
            $collection = $this->saleCollectionFactory->create();
            $collection->addFieldToFilter('customer_email', $customer->getEmail())
            ->setOrderStateFilter(Order::STATE_CANCELED, true)
            ->addFieldToFilter('status', Order::STATE_COMPLETE)
            ->load();

            $collection = $collection->getTotals();
            $totalSales = floatval($collection['lifetime']);
            return $totalSales;
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
    }

    public function getWishListTotal($customer) {
        $wishList               = $this->wishlistFactory->create()->loadByCustomerId($customer->getId());
        $wishListItemCollection = $wishList->getItemCollection();
        $wishListTotal          = 0;
        foreach ($wishListItemCollection as $_item) {
            $_product = $_item->getProduct();
            $wishListTotal += $_product->getPrice();
        }
        return $wishListTotal;
    }

    public function getWishListTotalProductsQuantity($customer) {
        $wishList               = $this->wishlistFactory->create()->loadByCustomerId($customer->getId());
        $wishListItemCollection = $wishList->getItemCollection();
        $totalQuantity          = 0;
        foreach ($wishListItemCollection as $_item) {
            $totalQuantity += $_item->getQty();
        }
        return $totalQuantity;
    }

    public function getWishListTotalProductsCount($customer) {
        $wishList               = $this->wishlistFactory->create()->loadByCustomerId($customer->getId());
        $wishListItemCollection = $wishList->getItemCollection();
        $count                  = $wishListItemCollection->count();
        return $count;
    }


    public function getTotalOrdersByCustomer($customer)
    {
        $totalOrder = $this->orderCollectionFactory->create()
        ->addFieldToFilter('customer_email', $customer->getEmail())
        ->addFieldToFilter('status', 'complete')
        ->count();
        return $totalOrder;
    }

    public function getTotalOrderProductByCustomer($customer)
    {
        $collection = $this->orderCollectionFactory->create()
        ->addFieldToFilter('customer_email', $customer->getEmail())
        ->addFieldToFilter('status', 'complete');
        $data = [];
        foreach ($collection as $order) {
            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $data[$item->getProductId()] = 1; 
            }
        }
        return count($data);
    }

    public function getTotalOrderProductQuantityByCustomer($customer)
    {
        $collection = $this->orderCollectionFactory->create()
        ->addFieldToFilter('customer_email', $customer->getEmail())
        ->addFieldToFilter('status', 'complete');
        $count = [];
        foreach ($collection as $order) {
            $items = $order->getAllVisibleItems();
            foreach ($items as $item) {
                $count += $item->getQty();
            }
        }
        return $count;
    }

    public function getOrderGrandTotalByCustomer($customer, $value, $operator, $field)
    {
        $collection = $this->orderCollectionFactory->create()
        ->addFieldToFilter('customer_email', $customer->getEmail())
        ->addFieldToFilter('status', 'complete');
        $where = $this->_conditionOperatorMap($operator, $field, $value);
        $collection->getSelect()->where($where);
        $order = $collection->getFirstItem();

        if ($order->getId()) {
            $grandTotal = (int) $order->getGrandTotal();
            return $value;
        }
        return 0;
    }

    public function _conditionOperatorMap($operator, $field, $value)
    {
        switch ($operator) {
            case '{}':
            case '()':
            $operator = $field . ' LIKE \'(%' . $value . '%)\'';
            break;
            
            case '!{}':
            case '!()':
            $operator = $field . ' NOT LIKE \'(%' . $value . '%)\'';
            break;

            case '==':
            $operator = $field . ' = ' . $value;
            break;

            case '!=':
            $operator = $field . ' != ' . $value;
            break;

            case '>=':
            $operator = $field . ' >= ' . $value;
            break;

            case '>':
            $operator = $field . ' > ' . $value;
            break;

            case '<=':
            $operator = $field . ' <= ' . $value;
            break;

            case '<':
            $operator = $field . ' < ' . $value;
            break;
        }
        return $operator;
    }

    public function getOrderShippingByCustomer($customer, $value, $operator, $field)
    {
        $where = $this->_conditionOperatorMap($operator, $field, $value);
        $collection = $this->orderCollectionFactory->create()
        ->addFieldToFilter('customer_email', $customer->getEmail())
        ->addFieldToFilter('status', 'complete');
        $collection->getSelect()->where($where);
        $order = $collection->getFirstItem();
        if ($order->getId()) {
            $result = $order->getShippingMethod();
            return $result;
        }
        return $value . '-' .rand();
    }

    public function contains($substring, $string) {
        $pos = strpos($string, $substring);

        if($pos === false) {
                    // string needle NOT found in haystack
            return false;
        }
        else {
                    // string needle found in haystack
            return true;
        }

    }

    public function getOrderShippingFull($customer, $value, $operator, $field)
    {
        try {
            $collection = $this->orderCollectionFactory->create()
            ->addFieldToFilter('customer_email', $customer->getEmail())
            ->addFieldToFilter('status', 'complete');

            $status = false;

            foreach ($collection as $order) {
                $shippingAddress = $order->getShippingAddress();
                switch ($field) {
                    case ShippingAddress::SHIPPING_ADDRESS_COUNTRY:
                        if ($this->contains($value, $shippingAddress->getCountryId())) {
                            $status = true;             
                        } 
                        break;

                    case ShippingAddress::SHIPPING_ADDRESS_CITY:
                        if ($this->contains($value, $shippingAddress->getCity())) {
                            $status = true;             
                        }
                        break;

                    case ShippingAddress::SHIPPING_ADDRESS_STREET:
                        if ($this->contains($value, $shippingAddress->getData('street'))) {
                            $status = true;             
                        }
                        break;

                    case ShippingAddress::SHIPPING_ADDRESS_REGION:
                        if ($this->contains($value, $shippingAddress->getRegion())) {
                            $status = true;             
                        }
                        if ($this->contains($value, $shippingAddress->getRegionCode())) {
                            $status = true;             
                        }
                        break;

                    case ShippingAddress::SHIPPING_ADDRESS_POSTCODE:
                        if ($this->contains($value, $shippingAddress->getPostcode())) {
                            $status = true;             
                        }
                        break;
                }
            }

            if ($status) {
                return '1 = 1';
            } else {
                return '1 = 2';
            }

        } catch (\Excetion $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
    }
}