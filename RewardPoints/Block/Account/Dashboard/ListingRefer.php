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

use Lof\RewardPointsBehavior\Model\ResourceModel\Refer\CollectionFactory as ReferCollectionFactory;

class ListingRefer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    protected $collectionFactory;

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
        ReferCollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    public function getListReferredCustomer()
    {
        $customerId = $this->customerSession->getId();
        return $refer = $this->collectionFactory->create()->addFieldToFilter('customer_refer_id',$customerId);

    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    public function _toHtml()
    {
        $customerId = $this->customerSession->getId();
        $grid_pagination = true;
        $template = 'Lof_RewardPoints::account/dashboard/list.phtml';
        $this->setTemplate($template);
        $item_per_page = 5;
        $store = $this->_storeManager->getStore();
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_refer_id',$customerId)->getSelect()->order('id DESC');
        if ($grid_pagination) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setLimit($item_per_page)->setCollection($collection);
            $this->setChild('pager', $pager);
        }
        $this->setCollection($collection);
        return parent::_toHtml();
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
    public function getLimitPage()
    {
        $size = $this->getCollection()->getSize();
        $limit = ceil($size/$this->getLimit());
        return $limit;
    }
}