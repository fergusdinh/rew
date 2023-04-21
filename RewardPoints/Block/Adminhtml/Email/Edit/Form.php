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

namespace Lof\RewardPoints\Block\Adminhtml\Email\Edit;

use Lof\RewardPoints\Model\Config;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Lof\RewardPoints\Helper\Data           $rewardsData
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->registry     = $registry;
        $this->rewardsData  = $rewardsData;
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
        $email = $this->registry->registry('rewardpoints_email');

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
            );

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Mail Log #%1', $email->getId())
        ]);

        $fieldset->addField(
            'email_id',
            'hidden',
                [
                    'name'     => 'email_id',
                    'value'    => $email->getId()
                ]
            );


        if ($email->getTriggerLabel()) {
            $fieldset->addField(
                'trigger',
                'note',
                    [
                        'name'  => 'trigger',
                        'label' => __('Trigger'),
                        'title' => __('Trigger'),
                        'text'  => $email->getTriggerLabel()
                    ]
                );
        }

        if ($email->getTransactionId()) {
            $fieldset->addField(
            'transaction_id',
            'note',
                [
                    'name'  => 'transaction_id',
                    'label' => __('Transaction ID'),
                    'title' => __('Transaction ID'),
                    'text'  => '<a href="' . $this->getUrl(Config::ROUTES . '/transaction/edit', ['transaction_id' => $email->getTransactionId()]) . '">' . $email->getTransactionId() . '</a>'
                ]
            );
        }

        $fieldset->addField(
            'sender_name',
            'note',
                [
                    'name'  => 'sender_name',
                    'label' => __('Sender Name'),
                    'title' => __('Sender Name'),
                    'text'  => $email->getSenderName()
                ]
            );

        $fieldset->addField(
            'sender_email',
            'note',
                [
                    'name'  => 'sender_email',
                    'label' => __('Sender Email'),
                    'title' => __('Sender Email'),
                    'text'  => '<a href="mailto:' . $email->getSenderEmail() . '">' . $email->getSenderEmail() . '</a>'
                ]
            );

        $fieldset->addField(
            'recipient_name',
            'note',
                [
                    'name'  => 'recipient_name',
                    'label' => __('Recipient Name'),
                    'title' => __('Recipient Name'),
                    'text'  => $email->getRecipientName()
                ]
            );

        $fieldset->addField(
            'recipient_email',
            'note',
                [
                    'name'  => 'recipient_email',
                    'label' => __('Recipient Email'),
                    'title' => __('Recipient Email'),
                    'text'  => '<a href="mailto:' . $email->getRecipientEmail() . '">' . $email->getRecipientEmail() . '</a>'
                ]
            );

        $fieldset->addField(
            'subject',
            'note',
                [
                    'name'  => 'subject',
                    'label' => __('Subject'),
                    'title' => __('Subject'),
                    'text'  => $email->getSubject()
                ]
            );


        $start   = strpos($email->getMessage(), '<body', 0);
        $end     = strpos($email->getMessage(), '</body>', 0);
        $message = substr($email->getMessage(), $start, ($end - $start + 7));
        $fieldset->addField(
            'message',
            'note',
                [
                    'name'  => 'message',
                    'label' => __('Message'),
                    'title' => __('Message'),
                    'text'  => '<div class="lrw-email-message">' . $message . '</div>'
                ]
            );

        if ($bug = $email->getBug()) {
            $fieldset->addField(
                'bug',
                'note',
                    [
                        'name'  => 'bug',
                        'label' => __('Bug'),
                        'title' => __('Bug'),
                        'text'  => $bug
                    ]
                );
        }

        $fieldset->addField(
            'status',
            'note',
                [
                    'name'     => 'status',
                    'label'    => __('Status'),
                    'title'    => __('Status'),
                    'text'     => '<span style="width: 125px;" class="lrw-status lrw-status-' . $email->getStatus() . '">' . ucfirst($email->getStatus()) . '</span>'
                ]
            );

        $fieldset->addField(
            'sent_at',
            'note',
                [
                    'name'     => 'sent_at',
                    'label'    => __('Sent At'),
                    'title'    => __('Sent At'),
                    'text'     => $this->rewardsData->formatDate($email->getSentAt(), \IntlDateFormatter::LONG)
                ]
            );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
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
