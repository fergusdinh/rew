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
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Block\Account\Dashboard;

use \Lof\RewardPoints\Model\Transaction;

class Listing extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $_collection;

    /**
     * @param \Magento\Catalog\Block\Product\Context                              $context
     * @param \Magento\Customer\Model\Session                                     $customerSession
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param array                                                               $data
     */
    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        array $data = []
    ) {
    	parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /**
     * @return \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
     */
    public function getCollection(){
        return $this->_collection;
    }

    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer;
    }

    protected function _beforeToHtml()
    {
        $toolbar    = $this->getLayout()->getBlock('lrw_toolbar');
        $collection = $this->getTransactionCollection();
        $limit      = $this->getLimit();
        if(!$limit) {
            $limit = 5;
        }
        if ($toolbar) {
            $toolbar->setData('_current_limit', $limit)->setCollection($collection);
            $this->setChild('toolbar', $toolbar);
        }
        return $this;
    }

    public function getLimitPage()
    {
        $size = $this->getCollection()->getSize();
        $limit = ceil($size/$this->getLimit());
        return $limit;
    }

    public function getCurrentPage()
    {
        $p = (int) $this->getRequest()->getParam('p');
        $limit = (int) $this->getLimitPage();
        if ($p > $limit) {
            $p = $limit;
        }
        if (!$p) {
            $p = 1;
        }
        return $p;
    }

    /**
     * @return \Lof\RewardPoints\Model\ResourceModel\Transaction\Collection
     */
    public function getTransactionCollection()
    {
    	if(!$this->_collection){
            $limit = $this->getLimit();
            if(!$limit) {
                $limit = 5;
            }
            $collection = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('customer_id', $this->getCustomer()->getId())
            ->addFieldToFilter(['action', 'status'],[
                ['neq' => Transaction::ADMIN_ADD_TRANSACTION],
                ['neq' => Transaction::STATE_PROCESSING]
                ]);
            $collection->setPageSize($limit)->setOrder('transaction_id', 'desc');
            $this->setCollection($collection);
        }
        return $this->getCollection();
    }

}
