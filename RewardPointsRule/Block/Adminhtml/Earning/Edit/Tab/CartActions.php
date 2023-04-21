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
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Block\Adminhtml\Earning\Edit\Tab;

use Lof\RewardPoints\Model\Earning;

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
     * @var \Lof\RewardPoints\Model\Earning
     */
    protected $rewardsEarning;

    /**
     * @var \Magento\Rule\Block\Actions
     */
    protected $_ruleActions;

    /**
     * @var \Lof\RewardPoints\Model\Config\Source\CartEaringRuleSimpleAction
     */
    protected $_cartEaringRuleSimpleAction;

    /**
     * @param \Magento\Backend\Block\Template\Context              $context          
     * @param \Magento\Framework\Registry                          $registry         
     * @param \Magento\Framework\Data\FormFactory                  $formFactory    
     * @param \Lof\RewardPoints\Model\Earning                      $rewardsEarning   
     * @param array                                                $data             
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Actions $ruleActions,
        \Lof\RewardPointsRule\Model\Config\Source\CartEaringRuleSimpleAction $cartEaringRuleSimpleAction,
        \Lof\RewardPoints\Model\Earning $rewardsEarning,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_rendererFieldset           = $rendererFieldset;
        $this->rewardsEarning              = $rewardsEarning;
        $this->_cartEaringRuleSimpleAction = $cartEaringRuleSimpleAction;
        $this->_ruleActions                = $ruleActions;
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
        $storeId    = $this->getRequest()->getParam('store');
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
            $fieldset->addField(
                'rule_id',
                'hidden',
                [
                    'name' => 'rule_id'
                ]
            );
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
                'options'  => $this->_cartEaringRuleSimpleAction->toArray(),
                'disabled' => $isElementDisabled,
                'after_element_html' => '
                    <script>
                        require(["jquery"], function(){
                            jQuery(document).ready(function($) {
                                $("#rule_action").on("change", function(){
                                    var val = $(this).val();
                                    if(val == "earning_action_amount_spent") {
                                        $("#attribute-rule_monetary_step-container").show();
                                    } else {
                                        $("#attribute-rule_monetary_step-container").hide();
                                    }

                                    if(val == "earning_action_by_cart_qty") {
                                        $("#attribute-rule_qty_step-container").show();
                                    } else {
                                        $("#attribute-rule_qty_step-container").hide();
                                    }
                                }).change();
                            });
                        });
                    </script>
                '
            ]
        );
        $renderer = $element->setField('action')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Earn Points
         */
        $isElementDisabled = in_array('earn_points', $param);
        $field = $fieldset->addField(
            'earn_points',
            'text',
            [
                'name'       => 'earn_points',
                'label'      => __('Points (X)'),
                'title'      => __('Points (X)'),
                'style'      => 'width: 28rem;',
                'class'      => 'validate-greater-than-zero',
                'guide'      => __('How many points customers will receive when the rule is performed.'),
                'required'   => true,
                'disabled'   => $isElementDisabled,
                'isnegative' => true
            ]
        );
        $renderer = $element->setField('earn_points')
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
                'name'       => 'monetary_step',
                'label'      => __('Money Step (Y)'),
                'title'      => __('Money Step (Y)'),
                'style'      => 'width: 28rem;',
                'guide'      => __('Sets the number of products represented by "X".'),
                'note'       => __('<span style="font-size: 1.4rem; font-weight: bold">[' . $currencyCode . ']</span>'),
                'disabled'   => $isElementDisabled,
                'isnegative' => true
            ]
        );
        $renderer = $element->setField('monetary_step')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Qty Step
         */
        $currencyCode = $this->_storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $isElementDisabled = in_array('monetary_step', $param);
        $field = $fieldset->addField(
            'qty_step',
            'text',
            [
                'name'       => 'qty_step',
                'label'      => __('Quantity (Y)'),
                'title'      => __('Quantity (Y)'),
                'style'      => 'width: 28rem;',
                'class'      => 'validate-greater-than-zero',
                'disabled'   => $isElementDisabled,
                'isnegative' => true
            ]
        );
        $renderer = $element->setField('qty_step')
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Maximum Points Earned
         */
        $isElementDisabled = in_array('points_limit', $param);
        $field = $fieldset->addField(
            'points_limit',
            'text',
            [
                'name'       => 'points_limit',
                'label'      => __('Maximum Points Earned'),
                'title'      => __('Maximum Points Earned'),
                'class'      => 'validate-greater-than-zero',
                'required'   => false,
                'style'      => 'width: 28rem;',
                'guide'      => __('Set the maximum number of points earned for each product. Leave empty to disable'),
                'disabled'   => $isElementDisabled,
                'isnegative' => true
            ]
        );
        $renderer = $element->setField('monetary_step')
        ->setData('points_limit', $isElementDisabled)
        ->setData('is_scope_website', true);
        $field->setRenderer($renderer);


        /**
         * Stop Further Rules Processing
         */
        $isElementDisabled = in_array('is_stop_processing', $param);
        $field = $fieldset->addField(
            'is_stop_processing',
            'select', 
            [
                'label'  => __('Stop Further Rules Processing'),
                'name'   => 'is_stop_processing',
                'style'  => 'max-width: 15rem;',
                'guide'  => __('Determines if additional rules can be applied to this purchase. To prevent multiple discounts from being applied to the same purchase, select "Yes."'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes')
                ],
                'disabled'    => $isElementDisabled
            ]
        );
        $renderer = $element->setField('monetary_step')
        ->setData('points_limit', $isElementDisabled)
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




        if (!$model->getData('points_limit')) {
            $model->setData('points_limit', '');
        }

        if (!$model->getData('monetary_step')) {
            $model->setData('monetary_step', '');
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

    /**
     * Check if rule is readonly
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isReadonly()
    {
        return $this->_isReadonly;
    }
}
