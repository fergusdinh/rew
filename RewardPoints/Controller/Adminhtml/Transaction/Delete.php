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

namespace Lof\RewardPoints\Controller\Adminhtml\Transaction;

use Lof\RewardPoints\Model\Transaction;

class Delete extends \Lof\RewardPoints\Controller\Adminhtml\Transaction
{
    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param \Lof\RewardPoints\Helper\Mail       $rewardsMail
     * @param \Lof\RewardPoints\Helper\Data       $rewardsData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context, $coreRegistry);
        $this->rewardsMail = $rewardsMail;
        $this->rewardsData = $rewardsData;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('transaction_id');
        if ($id) {
            try {
                // init model and delete
                $transaction = $this->_objectManager->create('Lof\RewardPoints\Model\Transaction');
                $transaction->load($id);

                $amount          = $transaction->getAmount() - $transaction->getAmountUsed();
                $customer        = $transaction->getRewardsCustomer();
                $availablePoints = $customer->getAvailablePoints();
                if ($amount > 0 && $amount > $availablePoints && $transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    $this->messageManager->addError(__('Account points is not enough points to delete the transaction.'));
                    $this->_redirect('*/*/edit', [
                        'transaction_id' => $id
                        ]);
                    return;
                }

                $transaction->setTitle(__('Admin delete the transaction #%1', $transaction->getId()));
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE) {
                    $transaction->setAmount(-$amount);
                }
                $tmpTransaction = $transaction;
                $transaction->delete();

                if ($tmpTransaction->getStatus() == Transaction::STATE_COMPLETE || $tmpTransaction->getStatus() == Transaction::STATE_CANCELED) {
                    $customer = $transaction->getRewardsCustomer();
                    $customer->refreshPoints()->save();
                    if ($tmpTransaction->getStatus() == Transaction::STATE_CANCELED && $amount > 0) {
                        if ($amount<0) {
                            $amount = -$amount;
                        }
                        $tmpTransaction->setAmount($amount);
                    }
                    $params['title'] = __('Admin cancel the transaction #%1', $tmpTransaction->getId());
                    $params['transaction_amount'] = $this->rewardsData->formatPoints($amount);

                    $this->rewardsMail->setParams($params)->sendNotificationBalanceUpdateEmail($tmpTransaction);
                }
                // display success message
                $this->messageManager->addSuccess(__('You deleted the transaction.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/index');
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a transaction to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::transaction_delete');
    }
}
