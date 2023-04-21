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

class ProductActions extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Lof\RewardPoints\Model\Spending
     */
    protected $rewardsSpending;

    /**
     * @var \Lof\RewardPointsRule\Model\Config\Source\CartSpendingRuleSimpleAction
     */
    protected $_catalogSpendingRuleSimpleAction;

    /**
     * @param \Magento\Backend\Block\Template\Context                                   $context                         
     * @param \Magento\Framework\Registry                                               $registry                        
     * @param \Magento\Framework\Data\FormFactory                                       $formFactory                     
     * @param \Lof\RewardPointsRule\Model\Spending                                      $rewardsSpending                 
     * @param \Lof\RewardPointsRule\Model\Config\Source\CatalogSpendingRuleSimpleAction $catalogSpendingRuleSimpleAction 
     * @param array                                                                     $data                            
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Lof\RewardPointsRule\Model\Spending $rewardsSpending,
        \Lof\RewardPointsRule\Model\Config\Source\CatalogSpendingRuleSimpleAction $catalogSpendingRuleSimpleAction,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->rewardsSpending                  = $rewardsSpending;
        $this->_catalogSpendingRuleSimpleAction = $catalogSpendingRuleSimpleAction;
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
        $storeId    = $this->getRequest()->getParam('store');
        $useDefault = $model->getUseDefault();
        $param      = !empty($useDefault) ? unserialize($useDefault) : [];
        if(!$param) $param = [];

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


        /**
         * Action
         */
        $isElementDisabled = in_array('action', $param);
        $field = $fieldset->addField(
            'action',
            'select',
            [
                'label'    => __('Action'),
                'title'    => __('Action'),
                'name'     => 'action',
                'style'    => 'width: 28rem;',
                'options'  => $this->_catalogSpendingRuleSimpleAction->toArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('action')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Spend Points
         */
        $isElementDisabled = in_array('spend_points', $param);
        $field = $fieldset->addField(
            'spend_points',
            'text',
            [
                'name'     => 'spend_points',
                'label'    => __('X points'),
                'title'    => __('X points'),
                'required' => true,
                'style'    => 'width: 28rem;',
                'class'    => 'validate-greater-than-zero',
                'guide'    => __('How many points customers will receive when the rule is performed.'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('spend_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Monetary Step
         */
        $currencyCode = $this->_storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $isElementDisabled = in_array('monetary_step', $param);
        $field = $fieldset->addField(
            'monetary_step',
            'text',
            [
                'name'     => 'monetary_step',
                'label'    => __('Y amount'),
                'title'    => __('Y amount'),
                'required' => true,
                'style'    => 'width: 28rem;',
                'note'     => __('<span style="font-size: 1.4rem; font-weight: bold">[' . $currencyCode . ']</span>'),
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('monetary_step')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Spend Min Points
         */
        $isElementDisabled = in_array('spend_min_points', $param);
        $field = $fieldset->addField(
            'spend_min_points',
            'text',
            [
                'name'     => 'spend_min_points',
                'label'    => __('Spend Minimum'),
                'title'    => __('Spend Minimum'),
                'required' => false,
                'style'    => 'width: 28rem;',
                'disabled' => $isElementDisabled
            ]
        );
        $renderer = $element->setField('spend_min_points')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Spend Max Points
         */
        $isElementDisabled = in_array('spend_max_points', $param);
        $field = $fieldset->addField(
            'spend_max_points',
            'text',
            [
                'name'     => 'spend_max_points',
                'label'    => __('Spend Maximum'),
                'title'    => __('Spend Maximum'),
                'required' => false,
                'style'    => 'width: 28rem;',
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

        if (!$model->getData('spend_min_points')) {
            $model->setData('spend_min_points', '');
        }

        if (!$model->getData('spend_max_points')) {
            $model->setData('spend_max_points', '');
        }


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
