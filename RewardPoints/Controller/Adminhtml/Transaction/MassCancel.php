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

use Magento\Framework\Controller\ResultFactory;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Model\Email;

class MassCancel extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context                                 $context
     * @param \Magento\Ui\Component\MassAction\Filter                             $filter
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Helper\Data $rewardsData
        ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->rewardsLogger     = $rewardsLogger;
        $this->rewardsMail       = $rewardsMail;
        $this->rewardsData       = $rewardsData;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $totals = 0;
            $error_transactions = [];
            foreach ($collection as $transaction) {
                $status = $transaction->getStatus();
                $amount = $transaction->getAmount();
                $customer        = $transaction->getRewardsCustomer();
                $availablePoints = $customer->getAvailablePoints();
                $totalPoints     = $customer->getTotalPoints();
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE && $amount > 0 && $transaction->getAmount() > $availablePoints) {
                    $errtransaction = [];
                    $errtransaction['message'] = __('Account ID %1 points is not enough points to cancel the transaction ID #%1.', $customer->getCustomerId(), $transaction->getId());
                    $errtransaction['transaction'] = $transaction;
                    $error_transactions[] = $errtransaction;
                    continue;
                }

                $transaction->setStatus(\Lof\RewardPoints\Model\Transaction::STATE_CANCELED);
                $transaction->save();

                if ($status == Transaction::STATE_COMPLETE) {
                    $params['title'] = __('Admin cancel the transaction #%1', $transaction->getId());
                    $trigger = Email::ACTION_CANCEL_EARNED_POINTS;
                    if ($amount < 0) {
                        $trigger = Email::ACTION_CANCEL_SPENT_POINTS;
                    }
                    $amount = -$amount;
                    $params['transaction_amount'] = $this->rewardsData->formatPoints($amount);
                    $this->rewardsMail->setTrigger($trigger)
                                    ->setParams($params)
                                    ->sendNotificationBalanceUpdateEmail($transaction);
                }
                $customer->refreshPoints()->save();
                $totals++;
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been cancelled.', $totals));
            if($error_transactions){
                foreach($error_transactions as $error){
                    $this->messageManager->addError(__('Warning: %1', $error['message']));
                    $this->rewardsLogger->addError(__('Admin Mass Cancel Warning: %1', $error['message']));
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError(__('BUGS 1: %1', $e->getMessage()));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::transaction_save');
    }
}
