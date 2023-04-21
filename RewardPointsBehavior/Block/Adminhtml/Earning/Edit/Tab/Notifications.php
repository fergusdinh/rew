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

namespace Lof\RewardPointsBehavior\Block\Adminhtml\Earning\Edit\Tab;

class Notifications extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \\Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context       
     * @param \Magento\Framework\Registry             $registry      
     * @param \Magento\Framework\Data\FormFactory     $formFactory   
     * @param \Magento\Cms\Model\Wysiwyg\Config       $wysiwygConfig 
     * @param array                                   $data          
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_wysiwygConfig = $wysiwygConfig;
    }

    protected function _prepareForm()
    {
        $model      = $this->_coreRegistry->registry('earning_rate');
        $isReadonly = false;

        $storeId = $this->getRequest()->getParam('store');
        
        $param = unserialize($model->getUseDefault());
        if(!$param) $param = [];

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Information')]);
        if ($model->getId()) {
            $fieldset->addField(
                'rule_id',
                'hidden', [
                    'name' => 'rule_id'
                ]
            );
        }

        // Create element for multiple store
        $element = $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Fieldset\Element')
        ->setTemplate('Lof_RewardPoints::form/renderer/fieldset/element.phtml');

        $isElementDisabled = in_array('history_message', $param);
        $field = $fieldset->addField(
            'history_message',
            'textarea',
            [
                'name'     => 'history_message',
                'label'    => __('Message in the rewards history'),
                'title'    => __('Message in the rewards history'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('history_message')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('email_message', $param);
        $field = $fieldset->addField(
            'email_message',
            'editor',
            [
                'name'   => 'email_message',
                'label'  => __('Message for customer notification email'),
                'title'  => __('Message for customer notification email'),
                'style'  => 'height:20em',
                'config' => $this->_wysiwygConfig->getConfig(),
                'note'   => __('<strong>You can use the following variables:</strong><br/>
                    {{var customer.name}} - customer name<br/>
                    {{var customer.totalpoints}} - customer total points<br/>
                    {{store url =""}} - store URL<br/>
                    {{var store.getFrontendName()}} - store name<br/>
                    {{var transaction_amount}} - formatted amount of current transaction (e.g 10 Rewards Points)<br/>
                    {{var balance_total}} - formatted balance of customer account (e.g. 100 Rewards Points)<br/>
                    Leave empty to use default notification email.<br/><br/><strong>Example:</strong><br/>
                    Dear {{var customer.name}},<br>
                    <p>Your account balance has been updated at <a href="{{store url=""}}">{{var store.getFrontendName()}}</a>. </p>
                    Balance Update: <b>{{var transaction_amount}}</b><br>
                    Message: {{var transaction_comment}}<br>
                    Balance Total: <b>{{var balance_total}}</b><br>
                    <br>
                    Thank you,<br>
                    <strong>{{var store.getFrontendName()}}</strong>')
            ]
        );
        $renderer = $element->setField('email_message')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Notifications');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Notifications');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
