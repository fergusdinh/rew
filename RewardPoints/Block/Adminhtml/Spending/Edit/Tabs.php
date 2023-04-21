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

namespace Lof\RewardPoints\Block\Adminhtml\Spending\Edit;

use Lof\RewardPoints\Model\Spending;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Framework\Registry              $registry
     * @param \Magento\Backend\Block\Widget\Context    $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session      $authSession
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->registry = $registry;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Spending Information'));
    }

    protected function _prepareLayout()
    {
    	$rule = $this->registry->registry('spending_rate');

    	$this->addTab(
                'general',
                [
                    'label' => __('General'),
                    'content' => $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Spending\Edit\Tab\Main')->toHtml()
                ]
            );

        $this->addTab(
            'actions',
            [
                'label' => __('Actions'),
                'content' => $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Spending\Edit\Tab\Actions')->toHtml()
            ]
        );
    }
}
