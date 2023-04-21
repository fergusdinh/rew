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

class CustomerConditions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Lof\RewardPoints\Model\Config\Source\CartEaringRuleSimpleAction
     */
    protected $_customerBehavior;

    /**
     * @param \Magento\Backend\Block\Template\Context                        $context          
     * @param \Magento\Framework\Registry                                    $registry         
     * @param \Magento\Framework\Data\FormFactory                            $formFactory      
     * @param \Magento\Rule\Block\Conditions                                 $conditions       
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset           $rendererFieldset 
     * @param \Lof\RewardPointsBehavior\Model\Config\Source\CustomerBehavior $customerBehavior 
     * @param array                                                          $data             
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Lof\RewardPointsBehavior\Model\Config\Source\CustomerBehavior $customerBehavior,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_conditions       = $conditions;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_customerBehavior = $customerBehavior;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Conditions');
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
        $isReadonly = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        // Create element for multiple store
        $element = $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Fieldset\Element')
        ->setTemplate('Lof_RewardPoints::form/renderer/fieldset/element.phtml');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Conditions')]);
        $param = @unserialize($model->getUseDefault());
        if(!$param) $param = [];
        $isElementDisabled = in_array('action', $param);
        $field = $fieldset->addField(
            'action',
            'select',
            [
                'label'    => __('Events'),
                'title'    => __('Events'),
                'name'     => 'action',
                'options'  => $this->_customerBehavior->toArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('action')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        $renderer = $this->_rendererFieldset->setTemplate(
            'Magento_CatalogRule::promo/fieldset.phtml'
        )->setNewChildUrl(
            $this->getUrl('*/earning/newConditionHtml/form/rule_conditions_fieldset')
        );

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            [
                'legend' => __(
                    'Apply the rule only to customer attributes matching the following conditions.'
                )
            ]
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions')]
        )->setRule(
            $model
        )->setRenderer(
            $this->_conditions
        );

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

