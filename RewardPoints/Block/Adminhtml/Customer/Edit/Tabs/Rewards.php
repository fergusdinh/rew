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

namespace Lof\RewardPoints\Block\Adminhtml\Customer\Edit\Tabs;

class Rewards extends \Magento\Backend\Block\Widget\Form
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @param \Magento\Backend\Block\Widget\Context       $context
     * @param \Magento\Framework\Data\FormFactory         $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Lof\RewardPoints\Helper\Customer           $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Data               $rewardsData
     * @param \Lof\RewardPoints\Model\Config              $rewardsConfig
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Helper\Balance $rewardsBalance,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formFactory     = $formFactory;
        $this->dateTime        = $dateTime;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsData     = $rewardsData;
        $this->rewardsConfig   = $rewardsConfig;
        $this->rewardsBalance  = $rewardsBalance;
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        // Update Balance status
        $this->rewardsBalance->proccessTransaction();

        /**
         * Checking if user have permission to save information
         */
        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->formFactory->create(
                [
                    'data'    => [
                    'id'      => 'edit_form',
                    'action'  => $this->getData('action'),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data'
                    ]
                ]
            );
        $customer = $this->rewardsCustomer->getCustomer($this->getRequest()->getParam('id'));
        $fieldset = $form->addFieldset('points', ['legend' => __('Reward Points Information')]);

        $fieldset->addField(
            'available_points',
            'note',
                [
                    'name'  => 'available_points',
                    'label' => __('Available Points'),
                    'title' => __('Available Points'),
                    'class' => 'validate-number',
                    'text'  => '<strong>' . $this->rewardsData->formatPoints($customer->getTotalPoints()) . '</strong>'
                ]
            );

        $spentPoints = (float) $customer->getSpentPoints();
        $fieldset->addField(
            'spent_points',
            'note',
                [
                    'name'  => 'spent_points',
                    'label' => __('Spent Points'),
                    'title' => __('Spent Points'),
                    'text'  => '<strong>' . $this->rewardsData->formatPoints($spentPoints) . '</strong>'
                ]
            );

        $fieldset->addField(
            'update_point_notification',
            'checkbox',
                [
                    'name'           => 'rewardpoints[update_point_notification]',
                    'label'          => __('Update Points Notification'),
                    'title'          => __('Update Points Notification'),
                    'data-form-part' => 'customer_form',
                    'checked'        => $customer->getUpdatePointNotification()
                ]
            );

        $fieldset->addField(
            'expire_point_notification',
            'checkbox',
                [
                    'name'           => 'rewardpoints[expire_point_notification]',
                    'label'          => __('Expire Points Notification'),
                    'title'          => __('Expire Points Notification'),
                    'data-form-part' => 'customer_form',
                    'checked'        => $customer->getExpirePointNotification()
                ]
            );


        $fieldset = $form->addFieldset('transactions', ['legend' => __('Transaction History')]);
        $fieldset->addField(
            'lrw_amount',
            'text',
                [
                    'name'           => 'rewardpoints[amount]',
                    'label'          => __('Points'),
                    'title'          => __('Points'),
                    'note'           => __('Add or subtract customer\'s balance. For ex: 50 or -50 points.'),
                    'data-form-part' => 'customer_form',
                    'disabled'       => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'lrw_title',
            'textarea',
                [
                    'name'           => 'rewardpoints[title]',
                    'label'          => __('Transaction Title'),
                    'title'          => __('Transaction Title'),
                    'data-form-part' => 'customer_form',
                    'disabled'       => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'today_date',
            'hidden',
                [
                    'name'           => 'rewardpoints[today_date]',
                    'value'          => $this->dateTime->gmtDate('m/d/Y h:m:s'),
                    'data-form-part' => 'customer_form'
                ]
            );

        $dateFormat = 'M/d/yy';
        $timeFormat = 'h:mm:ss';
        $fieldset->addField(
            'lrw_apply_at',
            '\Lof\RewardPoints\Block\Adminhtml\Form\Element\Date',
                [
                    'name'           => 'rewardpoints[apply_at]',
                    'label'          => __('Scheduled At'),
                    'title'          => __('Scheduled At'),
                    'data-form-part' => 'customer_form',
                    'description'    => __('Select a date time to apply this transaction. The Scheduled At must equal or greater than today date time.'),
                    'date_format'    => $dateFormat,
                    'time_format'    => $timeFormat,
                    'disabled'       => $isElementDisabled
                ]
            );

        $expireDate = $this->rewardsConfig->getEarningExpireDate();
        $fieldset->addField(
            'lrw_expires_at',
            '\Lof\RewardPoints\Block\Adminhtml\Form\Element\Date',
                [
                     'name'           => 'rewardpoints[expires_at]',
                     'label'          => __('Expires At'),
                     'title'          => __('Expires At'),
                     'description'    => __('If empty or zero, there is no limitation. The Expires At must greater than Scheduled At.'),
                     'data-form-part' => 'customer_form',
                     'date_format'    => $dateFormat,
                     'time_format'    => $timeFormat,
                     'value'          => $expireDate,
                     'disabled'       => $isElementDisabled
                ]
            );

        $fieldset->addField(
            'lrw_email_message',
            'textarea',
                [
                    'name'           => 'rewardpoints[email_message]',
                    'label'          => __('Message for customer notification email'),
                    'title'          => __('Message for customer notification email'),
                    'style'          => 'height: 300px;',
                    'data-form-part' => 'customer_form',
                    'required'       => false,
                    'disabled'       => $isElementDisabled,
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
        $customerId = (int) $this->_request->getParam('id');
        $html .= $this->getLayout()->createBlock('Lof\RewardPoints\Block\Adminhtml\Transaction\Grid')->setCustomerId($customerId)->setJsFormObject('edit_form')->toHtml();
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

    /**
     * @return string
     */
    public function getAfter()
    {
        return 'wishlist';
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Reward Points');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Reward Points');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getRequest()->getParam('id')?true:false;
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
