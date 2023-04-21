<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\RewardPoints\Block\Adminhtml\Transaction\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context     $context
     * @param \Magento\Framework\Registry                 $registry
     * @param \Magento\Framework\Data\FormFactory         $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Lof\RewardPoints\Helper\Data               $rewardsData
     * @param \Lof\RewardPoints\Model\Config              $rewardsConfig
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->rewardsData   = $rewardsData;
        $this->rewardsConfig = $rewardsConfig;
        $this->dateTime      = $dateTime;
    }


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
            'amount',
            'text',
                [
                    'name'        => 'amount',
                    'label'       => __('Points'),
                    'title'       => __('Points'),
                    'description' => __('For ex: 10 or -10 points.'),
                    'required'    => true,
                    'disabled'    => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'title',
            'textarea',
                [
                    'name'     => 'title',
                    'label'    => __('Transaction Title'),
                    'title'    => __('Transaction Title'),
                    'disabled' => $isElementDisabled
                ]
            );

        $todayDate = $this->dateTime->gmtDate('m/d/Y h:m:s');

        $fieldset->addField(
            'today_date',
            'hidden',
                [
                    'name'     => 'today_date',
                    'value'    => $todayDate
                ]
            );

        $dateFormat = 'M/d/yy';
        $timeFormat = 'h:mm:ss';
        $fieldset->addField(
            'apply_at',
            'date',
                [
                    'name'        => 'apply_at',
                    'label'       => __('Scheduled At'),
                    'title'       => __('Scheduled At'),
                    'description' => __('Select a date time to apply this transaction. The Scheduled At must equal or greater than today date time.'),
                    'style'       => 'width: 185px;',
                    'class'       => 'admin__control-text',
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                    'disabled'    => $isElementDisabled
                ]
            );

        $expireDate = $this->rewardsConfig->getEarningExpireDate();

        $fieldset->addField(
            'expires_at',
            'date',
                [
                    'name'        => 'expires_at',
                    'label'       => __('Expires At'),
                    'title'       => __('Expires At'),
                    'description' => __('If empty or zero, there is no limitation. The Expires At must greater than Scheduled At.'),
                    'style'       => 'width: 185px;',
                    'class'       => 'admin__control-text',
                    'date_format' => $dateFormat,
                    'time_format' => $timeFormat,
                    'value'       => $expireDate,
                    'disabled'    => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'email_message',
            'textarea',
                [
                    'name'      => 'email_message',
                    'label'     => __('Message for customer notification email'),
                    'title'     => __('Message for customer notification email'),
                    'style'     => 'height: 300px;',
                    'required'  => false,
                    'disabled'  => $isElementDisabled,
                    'note'      => __('<strong>You can use the following variables:</strong><br/>
                    {{var customer.name}} - customer name<br/>
                    {{var customer.totalpoints}} - customer total points<br/>
                    {{store url =""}} - store URL<br/>
                    {{var store.getFrontendName()}} - store name<br/>
                    {{var transaction_amount}} - formatted amount of current transaction (e.g 10 Rewards Points)<br/>
                    {{var balance_total}} - formatted balance of customer account (e.g. 100 Rewards Points)<br/>
                    Leave empty to use default notification email.<br/><br/><strong>Example:</strong><br/>
                    Dear {{var customer.name}},<br>
                    <p>Your account balance has been updated at <a href="{{store url=""}}">{{var store.getFrontendName()}}</a>. </p>
                    Balance Update: <b>{{var transaction_amount}}</b><br>
                    Message: {{var transaction_comment}}<br>
                    Balance Total: <b>{{var balance_total}}</b><br>
                    <br>
                    Thank you,<br>
                    <strong>{{var store.getFrontendName()}}</strong>')
                ]
            );

        $fieldset->addField(
            'customerids',
            'hidden',
                [
                    'name'     => 'customerids'
                ]
            );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _afterToHtml($html)
    {
        $form = $this->getForm();
        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $html .= $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Customer\Grid')->setJsFormObject('edit_form')->toHtml();

        $html .= $this->getLayout()->createBlock('\Magento\Backend\Block\Template')->setTemplate('Lof_RewardPoints::transaction/customer/grid/js.phtml')->toHtml();

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
