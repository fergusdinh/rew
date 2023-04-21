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

namespace Lof\RewardPoints\Block\Adminhtml\Spending;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
        ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId   = 'rule_id';
        $this->_blockGroup = 'Lof_RewardPoints';
        $this->_controller = 'adminhtml_spending';

        parent::_construct();

        if ($this->_isAllowedAction('Lof_RewardPoints::spending')) {

            if($this->_coreRegistry->registry('spending_rate')->getId() && !$this->getRequest()->getParam('store')){
                $this->buttonList->add(
                    'duplicate',
                    [
                        'label' => __('Save and Duplicate'),
                        'class' => 'save'
                    ],
                    -50
                    );
            }

            $this->buttonList->update('save', 'label', __('Save Rate'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label'          => __('Save and Continue Edit'),
                    'class'          => 'save',
                    'data_attribute' => [
                    'mage-init'      => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ],
                    ],
                ]
                ],
                -100
                );

        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Lof_RewardPoints::spending') && !$this->getRequest()->getParam('store')) {
            $this->buttonList->update('delete', 'label', __('Delete'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        $rule = $this->_coreRegistry->registry('spending_rate');
        return $this->getUrl('*/*/delete', [$this->_objectId => $rule->getId()]);
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('spending_rate')->getId()) {
            return __("Edit Rule '%1'", $this->escapeHtml($this->_coreRegistry->registry('spending_rate')->getTitle()));
        } else {
            return __('New Rule');
        }
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
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('rewardpoints/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
        require([
        'jquery',
        'mage/backend/form'
        ], function(){
            jQuery('#duplicate').click(function(){
                var actionUrl = jQuery('#edit_form').attr('action') + 'duplicate/1';
                jQuery('#edit_form').attr('action', actionUrl);
                jQuery('#edit_form').submit();
            });

            function toggleEditor() {
                if (tinyMCE.getInstanceById('before_form_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'before_form_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'before_form_content');
                }
            };
        });";
        return parent::_prepareLayout();
    }
}
