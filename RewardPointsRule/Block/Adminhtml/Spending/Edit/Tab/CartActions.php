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

namespace Lof\RewardPointsRule\Block\Adminhtml\Spending\Edit\Tab;

use Lof\RewardPoints\Model\Config as Config;

class CartActions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Lof\RewardPointsRule\Model\Config\Source\CartSpendingRuleSimpleAction
     */
    protected $_cartSpendingRuleSimpleAction;

    /**
     * @param \Magento\Backend\Block\Template\Context                                $context                      
     * @param \Magento\Framework\Registry                                            $registry                     
     * @param \Magento\Framework\Data\FormFactory                                    $formFactory                  
     * @param \Magento\Rule\Block\Actions                                            $ruleActions                  
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset                   $rendererFieldset             
     * @param \Lof\RewardPointsRule\Model\Config\Source\CartSpendingRuleSimpleAction $cartSpendingRuleSimpleAction 
     * @param array                                                                  $data                         
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $ruleActions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Lof\RewardPointsRule\Model\Config\Source\CartSpendingRuleSimpleAction $cartSpendingRuleSimpleAction,
        array $data = []
    ) {
        $this->_ruleActions                  = $ruleActions;
        $this->_rendererFieldset             = $rendererFieldset;
        $this->_cartSpendingRuleSimpleAction = $cartSpendingRuleSimpleAction;
        parent::__construct($context, $registry, $formFactory, $data);
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
        $model = $this->_coreRegistry->registry('spending_rate');
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
        $model      = $this->_coreRegistry->registry('spending_rate');
        $isReadonly = false;

        $storeId = $this->getRequest()->getParam('store');
        
        $useDefault = $model->getUseDefault();
        $param      = !empty($useDefault) ? unserialize($useDefault) : [];
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

        $isElementDisabled = in_array('action', $param);
        $field = $fieldset->addField(
            'action',
            'select',
            [
                'label'    => __('Action'),
                'title'    => __('Action'),
                'name'     => 'action',
                'options'  => $this->_cartSpendingRuleSimpleAction->toArray(),
                'disabled' => $isElementDisabled
            ]
        );
       $renderer = $element->setField('action')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        $isElementDisabled = in_array('spend_points', $param);
        $field = $fieldset->addField(
            'spend_points',
            'text',
            [
                'name'     => 'spend_points',
                'label'    => __('X points'),
                'title'    => __('X points'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('spend_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $currencyCode = $this->_storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $isElementDisabled = in_array('monetary_step', $param);
        $field = $fieldset->addField(
            'monetary_step',
            'text',
            [
                'name'     => 'monetary_step',
                'label'    => __('Y discount'),
                'title'    => __('Y discount'),
                'note'     => __('<span style="font-size: 1.4rem;">[' . $currencyCode . ']</span>'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('monetary_step')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        $isElementDisabled = in_array('spend_min_points', $param);
        $field = $fieldset->addField(
            'spend_min_points',
            'text',
            [
                'name'     => 'spend_min_points',
                'label'    => __('Spend Minimum'),
                'title'    => __('Spend Minimum'),
                'required' => false,
                'note'     => __('Enter 0 or leave empty to disable.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('spend_min_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $isElementDisabled = in_array('spend_max_points', $param);
        $field = $fieldset->addField(
            'spend_max_points',
            'text',
            [
                'name'     => 'spend_max_points',
                'label'    => __('Spend Maximum'),
                'title'    => __('Spend Maximum'),
                'required' => false,
                'note'     => __('Enter 0 or leave empty to disable.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('spend_max_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        /**
         * Is Stop Processing
         */
        $isElementDisabled = in_array('is_stop_processing', $param);
        $field = $fieldset->addField(
            'is_stop_processing',
            'select',
            [
                'label'  => __('Stop further rules processing'),
                'name'   => 'is_stop_processing',
                'values' => [
                    0 => __('No'),
                    1 => __('Yes')
                ],
                'style'    => 'width: 28rem;',
                'guide'    => __('Determines if additional rules can be applied to this purchase. To prevent multiple discounts from being applied to the same purchase, select "Yes."'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('is_stop_processing')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);

        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $newChildUrl
        )->setFieldSetId(
            $actionsFieldSetId
        );

        $fieldset = $form->addFieldset(
            $fieldsetId,
            [
                'legend' => __(
                    'Apply the rule only to cart items matching the following conditions ' .
                    '(leave blank for all items).'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'actions',
            'text',
            [
                'name'           => 'apply_to',
                'label'          => __('Apply To'),
                'title'          => __('Apply To'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->_ruleActions
        );

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
