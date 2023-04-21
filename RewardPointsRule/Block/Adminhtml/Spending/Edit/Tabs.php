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

namespace Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit;

use Lof\RewardPointsRule\Model\Spending;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

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
        $this->registry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('form_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule Information'));
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

        $type = $rule->getType();

        switch ($type) {
            case Spending::PRODUCT_RULE:
                $this->addTab(
                    'product_conditions',
                    [
                        'label' => __('Conditions'),
                        'content' => $this->getLayout()->createBlock('Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit\Tab\Conditions')->toHtml()
                    ]
                );
                $this->addTab(
                    'product_actions',
                    [
                        'label' => __('Actions'),
                        'content' => $this->getLayout()->createBlock('Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit\Tab\ProductActions')->toHtml()
                    ]
                );
                break;

            case Spending::CART_RULE:
                $this->addTab(
                    'product_conditions',
                    [
                        'label' => __('Conditions'),
                        'content' => $this->getLayout()->createBlock('Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit\Tab\CartConditions')->toHtml()
                    ]
                );
                $this->addTab(
                    'cart_actions',
                    [
                        'label' => __('Actions'),
                        'content' => $this->getLayout()->createBlock('Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit\Tab\CartActions')->toHtml()
                    ]
                );
                break;
           }
    }
}
