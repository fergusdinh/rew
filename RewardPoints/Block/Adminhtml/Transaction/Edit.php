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

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }	

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_objectId   = 'transaction_id';
        $this->_blockGroup = 'Lof_RewardPoints';
        $this->_controller = 'adminhtml_transaction_view';

        $transaction = $this->_coreRegistry->registry('rewardpoints_transaction');
        if ($transaction->getStatus() != \Lof\RewardPoints\Model\Transaction::STATE_CANCELED) {
            $this->updateButton('save', 'label', __('Cancel'));
        } else {
            $this->buttonList->remove('save');
        }

        $this->addButton(
                'delete',
                [
                    'label' => __('Delete'),
                    'class' => 'delete',
                    'onclick' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getDeleteUrl() . '\')'
                ]
            );

        $this->buttonList->remove('reset');
    }
}