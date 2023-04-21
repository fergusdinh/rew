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

namespace Lof\RewardPoints\Block\Adminhtml\Transaction\View\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Framework\Registry                $registry
     * @param \Magento\Framework\Data\FormFactory        $formFactory
     * @param \Magento\User\Model\UserFactory            $userFactory
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->registry     = $registry;
        $this->userFactory  = $userFactory;
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
        $transaction = $this->registry->registry('rewardpoints_transaction');

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

        $customer = $transaction->getCustomer();

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Transaction #%1', $transaction->getId())
        ]);

        $fieldset->addField(
            'title',
            'note',
                [
                    'name'     => 'title',
                    'label'    => __('Transaction Title'),
                    'title'    => __('Transaction Title'),
                    'text'     => $this->rewardsData->filter($transaction->getTitle())
                ]
            );

        $fieldset->addField(
            'customer_email',
            'note',
                [
                    'name'     => 'customer_email',
                    'label'    => __('Customer Email'),
                    'title'    => __('Customer Email'),
                    'text'     => __('<a href="%1">%2</a>', $this->_urlBuilder->getUrl('customer/*/edit',['id' => $transaction->getCustomerId()]) ,$customer->getEmail())
                ]
            );

        $fieldset->addField(
            'action',
            'note',
                [
                    'name'     => 'action',
                    'label'    => __('Action'),
                    'title'    => __('Action'),
                    'text'     => $transaction->getActionLabel()
                ]
            );

        $fieldset->addField(
            'status',
            'note',
                [
                    'name'     => 'status',
                    'label'    => __('Status'),
                    'title'    => __('Status'),
                    'text'     => '<span style="width: 125px;" class="lrw-status lrw-status-' . $transaction->getStatus() . '">' . $transaction->getStatusLabel() . '</span>'
                ]
            );

        $amount = $transaction->getAmount();
        if (is_numeric($amount) && $amount > 0) {
            $amount = '+' . $this->rewardsData->formatPoints($amount);
        }

        $fieldset->addField(
            'amount',
            'note',
                [
                    'name'     => 'amount',
                    'label'    => ucfirst($this->rewardsData->getUnit($transaction->getAmount())),
                    'title'    => ucfirst($this->rewardsData->getUnit($transaction->getAmount())),
                    'text'     => '<span class="lrw-status-' . $transaction->getStatus() . '">' . $amount . '</span>'
                ]
            );

        $fieldset->addField(
            'amount_used',
            'note',
                [
                    'name'     => 'amount_used',
                    'label'    => __('%1 Used', ucfirst($this->rewardsData->getUnit($transaction->getAmountUsed()))),
                    'title'    => __('%1 Used', ucfirst($this->rewardsData->getUnit($transaction->getAmountUsed()))),
                    'text'     => $this->rewardsData->formatPoints($transaction->getAmountUsed())
                ]
            );

        $fieldset->addField(
            'expires_at',
            'note',
                [
                    'name'     => 'expires_at',
                    'label'    => __('Expires At'),
                    'title'    => __('Expires At'),
                    'text'     => $transaction->getExpiresAt()?$transaction->getExpiresAt():'N\A'
                ]
            );

        $fieldset->addField(
            'created_at',
            'note',
                [
                    'name'     => 'created_at',
                    'label'    => __('Created At'),
                    'title'    => __('Created At'),
                    'text'     => $transaction->getCreatedAt()
                ]
            );

        $fieldset->addField(
            'updated_at',
            'note',
                [
                    'name'     => 'updated_at',
                    'label'    => __('Updated At'),
                    'title'    => __('Updated At'),
                    'text'     => $transaction->getUpdatedAt()
                ]
            );

        $fieldset->addField(
            'store_id',
            'note',
                [
                    'name'     => 'store_id',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'text'     => $this->_storeManager->getStore($transaction->getStoreId())->getName()
                ]
            );

        if ($adminUserId = $transaction->getAdminUserId()) {
            $admin = $this->userFactory->create()->load($adminUserId);
            $fieldset->addField(
                'admin_user_id',
                'note',
                    [
                        'name'     => 'admin_user_id',
                        'label'    => __('Admin User'),
                        'title'    => __('Admin User'),
                        'text'     => $admin->getName()
                    ]
                );
        }

        if ($transaction->getStatus() != \Lof\RewardPoints\Model\Transaction::STATE_CANCELED) {
            $fieldset->addField(
                'cancel',
                'hidden',
                    [
                        'name'     => 'cancel',
                        'value'    => 1
                    ]
            );

            $fieldset->addField(
                'transaction_id',
                'hidden',
                    [
                        'name'     => 'transaction_id',
                        'value'    => $transaction->getTransactionId()
                    ]
            );
        }

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
