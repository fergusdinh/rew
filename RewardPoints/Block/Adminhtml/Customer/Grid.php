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

namespace Lof\RewardPoints\Block\Adminhtml\Customer;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context                             $context
     * @param \Magento\Backend\Helper\Data                                        $backendHelper
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory    $collectionFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory       $groupCollectionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Magento\Store\Model\System\Store                                   $systemStore
     * @param array                                                               $data
     */
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Backend\Helper\Data $backendHelper,
    	\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
    	\Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
    	\Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
    	\Magento\Store\Model\System\Store $systemStore,
    	array $data = []
    ) {
    	parent::__construct($context, $backendHelper, $data);
        $this->context                      = $context;
        $this->collectionFactory            = $collectionFactory;
        $this->groupCollectionFactory       = $groupCollectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->systemStore                  = $systemStore;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customerGrid');
        $this->setDefaultSort('points');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
    	$collection = $this->collectionFactory->create();

    	$collection->addNameToSelect()->addAttributeToSelect('group_id');

    	$collection->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
    	->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
    	->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
    	->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
    	->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
    	->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left');

    	/* @var $collection \Magento\Cms\Model\ResourceModel\Page\Collection */
    	$this->setCollection($collection);

    	return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
    	$this->addColumn('in_transaction_users', [
    		'header_css_class' => 'a-center',
    		'type'             => 'checkbox',
    		'name'             => 'in_transaction_users',
    		'align'            => 'center',
    		'index'            => 'entity_id',
    	]);

    	$this->addColumn('entity_id', [
            'header' => __('ID'),
            'index' => 'entity_id'
        ]);

    	$this->addColumn('name', [
        		'header' => __('Name'),
        		'width'  => '100',
        		'index'  => 'name'
    		]);

    	$this->addColumn('email', [
        		'header' => __('Email'),
        		'width'  => '100',
        		'index'  => 'email'
    		]);

    	$this->addColumn('amount', [
    			'header'   => __('Balance Points'),
    			'width'    => '100',
    			'index'    => 'amount',
    			'align'    => 'center',
    			'renderer' => 'Lof\RewardPoints\Block\Adminhtml\Customer\Renderer\Amount'
    		]);

    	$groups = $this->groupCollectionFactory->create()
    	->addFieldToFilter('customer_group_id', ['gt' => 0])
    	->load()
    	->toOptionHash();
    	$this->addColumn('group', [
			'header'  => __('Group'),
			'width'   => '100',
			'index'   => 'group_id',
			'type'    => 'options',
			'options' => $groups
    	]);

    	$this->addColumn('billing_telephone', [
        		'header' => __('Telephone'),
        		'width'  => '100',
        		'index'  => 'billing_telephone'
    		]);

    	$this->addColumn('billing_postcode', [
        		'header' => __('ZIP'),
        		'width'  => '90',
        		'index'  => 'billing_postcode'
    		]);

    	$this->addColumn('billing_country_id', [
                'header' => __('Country'),
                'width'  => '100',
                'type'   => 'country',
                'index'  => 'billing_country_id'
    		]);

    	$this->addColumn('billing_region', [
        		'header' => __('State/Province'),
        		'width'  => '100',
        		'index'  => 'billing_region'
    		]);

    	$this->addColumn('customer_since', [
        		'header'    => __('Customer Since'),
        		'type'      => 'datetime',
        		'align'     => 'center',
        		'index'     => 'created_at',
        		'gmtoffset' => true
    		]);

    	if(!$this->context->getStoreManager()->isSingleStoreMode()) {
    		$this->addColumn('website_id', [
    			'header'  => __('Website'),
    			'align'   => 'center',
    			'width'   => '80px',
    			'type'    => 'options',
    			'options' => $this->systemStore->getWebsiteOptionHash(true),
    			'index'   => 'website_id',
    			]);
    	}

        $form = $this->getJsFormObject();
        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("rewardsCallBack");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }

    	return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $roleId = $this->getRequest()->getParam('rid');
        return $this->getUrl('*/*/customer_grid', ['rid' => $roleId]);
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
    	if (!($value = $column->getFilter()->getValue())) {
    		return;
    	}

    	$this->getCollection()->addStoreFilter($value);
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
    	return '#';
    }
}
