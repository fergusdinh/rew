<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\RewardPoints\Block\Adminhtml\Transaction;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\Transaction
     */
    protected $rewardsTransaction;

    /**
     * @param \Magento\Backend\Block\Template\Context                             $context
     * @param \Magento\Backend\Helper\Data                                        $backendHelper
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
     * @param \Lof\RewardPoints\Model\Transaction                                 $rewardsTransaction
     * @param array                                                               $data
     */
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\Magento\Backend\Helper\Data $backendHelper,
    	\Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Lof\RewardPoints\Model\Transaction $rewardsTransaction,
        array $data = []
    ) {
    	parent::__construct($context, $backendHelper, $data);
        $this->collectionFactory  = $collectionFactory;
        $this->rewardsTransaction = $rewardsTransaction;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
    	parent::_construct();
        $this->setId('transactionGrid');
        $this->setDefaultSort('transaction_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('customer_filter');
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
    	$collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('customer_id', (int) $this->getCustomerId());
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
    	$this->addColumn('transaction_id', [
            'header' => __('ID'),
            'type'   => 'number',
            'index'  => 'transaction_id'
            ]);

        $this->addColumn('title', [
            'header' => __('Title'),
            'index'  => 'title'
            ]);

        $this->addColumn('action', [
            'header'  => __('Action'),
            'width'   => '100px',
            "type"    => 'options',
            'options' => $this->rewardsTransaction->getActions(),
            'index'   => 'action'
            ]);

        $this->addColumn('amount', [
            'header'         => __('Points'),
            'renderer'       => 'Lof\RewardPoints\Block\Adminhtml\Transaction\Renderer\Points',
            'width'          => '100px',
            'validate_class' => 'validate-number',
            'type'           => 'number',
            'index'          => 'amount'
            ]);

        $this->addColumn('amount_used', [
            'header'         => __('Points Used'),
            'width'          => '100px',
            'type'           => 'number',
            'validate_class' => 'validate-number',
            'index'          => 'amount_used'
            ]);

        $this->addColumn('created_at', [
            'header' => __('Created At'),
            'type'   => 'date',
            'width'  => '100px',
            'index'  => 'created_at',
            'align'  => 'center'
            ]);

        $this->addColumn('apply_at', [
            'header' => __('Scheduled At'),
            'type'   => 'date',
            'width'  => '100px',
            'index'  => 'apply_at',
            'align'  => 'center'
            ]);

        $this->addColumn('expires_at', [
            'header' => __('Expires At'),
            'type'   => 'date',
            'width'  => '100px',
            'index'  => 'expires_at',
            'align'  => 'center'
            ]);

        $this->addColumn('status', [
            'header'   => __('Status'),
            "type"     => 'options',
            'options'  => $this->rewardsTransaction->getAvailableStatuses(true),
            'index'    => 'status',
            'renderer' => 'Lof\RewardPoints\Block\Adminhtml\Transaction\Renderer\Status'
            ]);

        $this->addColumn('view', [
            'header'   => __('view'),
            'width'    => '50px',
            'type'     => 'action',
            'renderer' => 'Lof\RewardPoints\Block\Adminhtml\Transaction\Renderer\Action',
            'filter'   => false,
            'sortable' => false,
            ]);

        $this->addExportType('rewardpoints/transaction/customer_exportTransactionCsv', __('CSV'));
        $this->addExportType('rewardpoints/transaction/customer_exportTransactionXml', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
    	return $this->getUrl('rewardpoints/transaction/edit', [
            'transaction_id' => $row['transaction_id']
            ]);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $customerId = $this->getRequest()->getParam('id');
        return $this->getUrl('rewardpoints/transaction/grid', ['id' => $customerId]);
    }
}
