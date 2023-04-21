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

namespace Lof\RewardPoints\Block\Adminhtml\Spending\Product\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /**
         * Checking if user have permission to save information
         */
        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
                [
                    'data'    => [
                    'id'      => 'edit_form',
                    'action'  => $this->getData('action'),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data'
                    ]
                ]
            );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => '']);
        $fieldset->addField(
            'points',
            'text',
                [
                    'name'     => 'points',
                    'label'    => __('Number Points'),
                    'title'    => __('Number Points'),
                    'class'    => 'validate-zero-or-greater',
                    'required' => true,
                    'disabled' => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'productids',
            'textarea',
                [
                    'name'     => 'productids',
                    'style'    => 'display:none;'
                ]
            );

        $fieldset->addField(
            'store_id',
            'hidden', [
                'name'  => 'store_id',
                'value' => (int)$this->getRequest()->getParam('store')
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Processing block html after rendering
     *
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $html .= $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Spending\Product\Grid')->setJsFormObject('edit_form')->toHtml();

        $html .= $this->getLayout()->createBlock('\Magento\Backend\Block\Template')->setTemplate('Lof_RewardPoints::grid/products/js.phtml')->toHtml();

        return $html;
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
