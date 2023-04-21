<?php

namespace Lof\RewardPoints\Block\Adminhtml\RedeemCode\Generate;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected $_reportTypeOptions = [];

    protected $_fieldVisibility = [];

    protected $_fieldOptions = [];

    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
        ) {
        
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/redeem/generate');
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'post'
                ]
            ]
        );

        $htmlIdPrefix = 'lof_couponcode_generate_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Generate Redeem Code')]);

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);

        $fieldset->addField('store_id', 'hidden', ['name' => 'store_id']);

        $fieldset->addField(
            'code_prefix',
            'text',
            [
                'name' => 'code_prefix',
                'required' => false,
                'label' => __('Code Prefix'),
                'title' =>__('Code Prefix'),
                'maxlength' => 30
            ]
        );
        $fieldset->addField(
            'code',
            'text',
            [
                'name' => 'code',
                'required' => false,
                'label' => __('Code'),
                'title' =>__('Code'),
                'maxlength' => 30
            ]
        );

        $fieldset->addField(
            'auto_generate',
            'checkbox',
            [
                'label' => __('Use Auto Generation'),
                'name' => 'auto_generate',
                'onClick' => "document.getElementById('code_text').disabled=this.checked;",
                'data-form-part' => $this->getData('target_form'),
                'onchange' => "document.getElementById('lof_couponcode_generate_code').disabled=this.checked;"
            ]
        );

        $fieldset->addField(
            'earn_points',
            'text',
            [
                'name' => 'earn_points',
                'required' => true,
                'label' => __('Value'),
                'title' =>__('Value'),
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'uses_per_code',
            'text',
            [
                'name' => 'uses_per_code',
                'required' => true,
                'label' => __('Uses Per Code'),
                'title' =>__('Uses Per Code'),
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'active_from',
            'date',
            [
                'name' => 'active_from',
                'label' => __('Start Time'),
                'title' => __('Start Time'),
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'class' => 'validate-date validate-date-range date-range-attribute-from'
            ]
        );

        $fieldset->addField(
            'active_to',
            'date',
            [
                'name' => 'active_to',
                'label' => __('End Time'),
                'title' => __('End Time'),
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'class' => 'validate-date validate-date-range date-range-attribute-to'
            ]
        );
        $fieldset->addField(
            'generate_coupon',
            'submit',
            [
                'label'    => '',
                'title'    => '',
                'class'    => 'action-secondary' ,
                'name'     => 'generate_coupon',
                'checked' => false,
                'onchange' => "",
                'value' => __('Generate Code'),
            ]

        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

   
}
