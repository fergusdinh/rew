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

use Lof\RewardPoints\Model\Config as Config;

class CustomerActions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $applyType;
    /**
     * @param \Magento\Backend\Block\Template\Context              $context
     * @param \Magento\Framework\Registry                          $registry
     * @param \Magento\Framework\Data\FormFactory                  $formFactory
     * @param \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType                      $applyType
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Lof\RewardPointsBehavior\Model\Config\Source\ApplyType $applyType,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->applyType   = $applyType;
    }
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Actions');
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
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('earning_rate');
        $form = $this->addTabToForm($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Handles addition of actions tab to supplied form.
     *
     * @param \Magento\SalesRule\Model\Rule $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addTabToForm($model, $fieldsetId = 'actions_fieldset', $formName = 'sales_rule_form')
    {
        $model      = $this->_coreRegistry->registry('earning_rate');
        $isReadonly = false;

        $storeId = $this->getRequest()->getParam('store');
        
        $param = unserialize($model->getUseDefault());
        if(!$param) $param = [];

        $actionsFieldSetId = $model->getActionsFieldSetId($formName);

        $newChildUrl = $this->getUrl(
            'sales_rule/promo_quote/newActionHtml/form/rule_actions_fieldset_' . $actionsFieldSetId,
            ['form_namespace' => $formName]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Actions')]);
        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        // Create element for multiple store
        $element = $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Fieldset\Element')
        ->setTemplate('Lof_RewardPoints::form/renderer/fieldset/element.phtml');

        $isElementDisabled = in_array('earn_points', $param);
        $field = $fieldset->addField(
            'earn_points',
            'text',
            [
                'name'     => 'earn_points',
                'label'    => __('Earn Points (X)'),
                'title'    => __('Earn Points (X)'),
                'guide'    => __('Earn points of matched customer or of referrer customer when referred customer register new account or place first valid order.'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('earn_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('referred_points', $param);
        $field = $fieldset->addField(
            'referred_points',
            'text',
            [
                'name'     => 'referred_points',
                'label'    => __('Referred Earn Points (X)'),
                'title'    => __('Referred Earn Points (X)'),
                'guide'    => __('Earn points referred customer when register new account or place first valid order. Use for behaviour: Refer to friend'),
                'required' => false,

                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('referred_points')
            ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('apply_type', $param);
        $field = $fieldset->addField(
            'apply_type',
            'select',
            [
                'name'     => 'apply_type',
                'label'    => __('Apply rule type'),
                'title'    => __('Apply rule type'),
                'guide'    => __('Apply rule type: when referred register new account, or place first valid order, or for both. Use for behaviour: Refer to friend'),
                'required' => false,
                'options'  => $this->applyType->toArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('apply_type')
            ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('min_qty_orders', $param);
        $field = $fieldset->addField(
            'min_qty_orders',
            'text',
            [
                'name'     => 'min_qty_orders',
                'label'    => __('Min Qty Orders'),
                'title'    => __('Min Qty Orders'),
                'guide'    => __('Min valid orders quantity values for referrer and referred friends while collectting points. Use for behaviour: Refer to friend and Apply Rule Type = Advanced'),
                'required' => false,

                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('min_qty_orders')
            ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('max_qty_orders', $param);
        $field = $fieldset->addField(
            'max_qty_orders',
            'text',
            [
                'name'     => 'max_qty_orders',
                'label'    => __('Max Qty Orders'),
                'title'    => __('Max Qty Orders'),
                'guide'    => __('Max valid orders quantity values for referrer and referred friends while collectting points. Use for behaviour: Refer to friend and Apply Rule Type = Advanced'),
                'required' => false,

                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('max_qty_orders')
            ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('points_limit', $param);
        $field = $fieldset->addField(
            'points_limit',
            'text',
            [
                'name'     => 'points_limit',
                'label'    => __('Maximum per day'),
                'title'    => __('Maximum per day'),
                'required' => false,
                'guide'    => __('Maximum number of earned points for one customer per day'),
                'note'     => __('Set 0 to disable limit.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('points_limit')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('points_limit_month', $param);
        $field = $fieldset->addField(
            'points_limit_month',
            'text',
            [
                'name'     => 'points_limit_month',
                'label'    => __('Maximum per month'),
                'title'    => __('Maximum per month'),
                'required' => false,
                'guide'    => __('Maximum number of earned points for one customer per month'),
                'note'     => __('Set 0 to disable limit.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('points_limit_month')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('points_limit_year', $param);
        $field = $fieldset->addField(
            'points_limit_year',
            'text',
            [
                'name'     => 'points_limit_year',
                'label'    => __('Maximum per year'),
                'title'    => __('Maximum per year'),
                'required' => false,
                'guide'    => __('Maximum number of earned points for one customer per year'),
                'note'     => __('Set 0 to disable limit.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('points_limit_month')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $this->_eventManager->dispatch('adminhtml_block_salesrule_actions_prepareform', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setActionFormName($model->getActions(), $formName);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        return $form;
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
