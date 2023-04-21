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

namespace Lof\RewardPoints\Block\Adminhtml\Earning\Edit\Tab;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $objectConverter;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param GroupRepositoryInterface                $groupRepository
     * @param ObjectConverter                         $objectConverter
     * @param SearchCriteriaBuilder                   $searchCriteriaBuilder
     * @param \Magento\Cms\Model\Wysiwyg\Config       $wysiwygConfig
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->groupRepository       = $groupRepository;
        $this->objectConverter       = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->wysiwygConfig         = $wysiwygConfig;
    }

    protected function _prepareForm()
    {
        $model      = $this->_coreRegistry->registry('earning_rate');
        $isReadonly = false;
        $storeId    = $this->getRequest()->getParam('store');
        $useDefault = $model->getUseDefault();
        $param      = !empty($useDefault) ? unserialize($useDefault) : [];

        if (!$param) $param = [];

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

        $fieldset->addField(
            'store_id',
            'hidden', [
                'name'  => 'store_id'
            ]
        );

        $fieldset->addField(
            'object_id',
            'hidden', [
                'name'  => 'object_id'
            ]
        );

        $fieldset->addField(
            'type',
            'hidden', [
                'name'  => 'type'
            ]
        );

        // Create element for multiple store
        $element = $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Fieldset\Element')
        ->setTemplate('Lof_RewardPoints::form/renderer/fieldset/element.phtml');

        /**
         * Name
         */
        $isElementDisabled = in_array('name', $param);
        $field = $fieldset->addField(
            'name',
            'text',
            [
                'name'     => 'name',
                'label'    => __('Rule Name'),
                'title'    => __('Rule Name'),
                'guide'    => __('This field will be displayed in the list of rules.'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('name')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Description
         */
        $isElementDisabled = in_array('description', $param);
        $field = $fieldset->addField(
            'description',
            'editor',
            [
                'name'     => 'description',
                'label'    => __('Description'),
                'title'    => __('Description'),
                'style'    => 'height:6em;',
                'note'     => __('A description of the rule should include the purpose of the rule, and explain how it is used.'),
                'disabled' => $isElementDisabled,
                'config'   => $this->wysiwygConfig->getConfig()
            ]
        );
        $renderer = $element->setField('description')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Action From
         */
        $isElementDisabled = in_array('active_from', $param);
        $dateFormat        = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $timeFormat        = $this->_localeDate->getTimeFormat();
        $field = $fieldset->addField(
            'active_from',
            'date',
            [
                'label'       => __('From'),
                'name'        => 'active_from',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'disabled'    => $isElementDisabled
            ]
        );
        $renderer = $element->setField('active_from')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Action To
         */
        $isElementDisabled = in_array('active_to', $param);
        $field = $fieldset->addField(
            'active_to',
            'date',
            [
                'label'       => __('To'),
                'name'        => 'active_to',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'disabled'    => $isElementDisabled
            ]
        );
        $renderer = $element->setField('active_to')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Customer Group Id
         */
        $isElementDisabled = (in_array('customer_group_ids', $param) || in_array('customer_group_ids[]', $param))?true:false;
        $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())
            ->getItems();
        $field = $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            [
                'name'     => 'customer_group_ids[]',
                'label'    => __('Customer Groups'),
                'title'    => __('Customer Groups'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'guide'    => __('Identifies the customer groups to which the rule applies.'),
                'values'   =>  $this->objectConverter->toOptionArray($groups, 'id', 'code')
            ]
        );
        $renderer = $element->setField('customer_group_ids')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Sort Order
         */
        $isElementDisabled = in_array('sort_order', $param);
        $field = $fieldset->addField(
            'sort_order',
            'text',
            [
                'label'    => __('Priority'),
                'name'     => 'sort_order',
                'guide'    => __('A number that indicates the priority of this rule in relation to others. The highest priority is number 0.'),
                'style'    => 'width: 15rem;',
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('customer_group_ids')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Status
         */
        $isElementDisabled = in_array('is_active', $param);
        $field = $fieldset->addField(
            'is_active',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'is_active',
                'style'    => 'width: 15rem;',
                'guide'    => __('Determines if the rule is currently active in the store.'),
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('is_active')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }
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
        return __('General Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General Information');
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
