<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Block\Adminhtml\Import;

class Import extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_coreRegistry = null;

    protected $_urlBuilder;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Url $url,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_urlBuilder = $url;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'object_id';
        $this->_blockGroup = 'Lof_RewardPoints';
        $this->_controller = 'adminhtml_import';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Coupon Code'));
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }

    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('lofrewardpoints_points')->getId()) {
            return __("Edit Form '%1'", $this->escapeHtml($this->_coreRegistry->registry('lofrewardpoints_points')->getName()));
        } else {
            return __('New Form');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

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
