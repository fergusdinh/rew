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

use Lof\RewardPoints\Model\Config;
use Lof\RewardPoints\Model\Transaction;
use Lof\RewardPoints\Model\Email;

class Save extends \Lof\RewardPoints\Controller\Adminhtml\Transaction
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Lof\RewardPoints\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @param \Magento\Backend\App\Action\Context                              $context
     * @param \Magento\Framework\Registry                                      $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Magento\Backend\Model\Auth\Session                              $authSession
     * @param \Lof\RewardPoints\Model\TransactionFactory                       $transactionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \Lof\RewardPoints\Helper\Customer                                $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Data                                    $rewardsData
     * @param \Lof\RewardPoints\Helper\Mail                                    $rewardsMail
     * @param \Lof\RewardPoints\Model\Cron                                     $rewardsCron
     */
    public function __construct(
    	\Magento\Backend\App\Action\Context $context,
    	\Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Model\Cron $rewardsCron
    ) {
    	parent::__construct($context, $coreRegistry);
        $this->storeManager              = $storeManager;
        $this->authSession               = $authSession;
        $this->transactionFactory        = $transactionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->rewardsCustomer           = $rewardsCustomer;
        $this->rewardsData               = $rewardsData;
        $this->rewardsMail               = $rewardsMail;
        $this->rewardsCron               = $rewardsCron;
    }

    public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
        //$this->rewardsCron->execute();

        /**
         * CANCEL TRANSACTION
         */
        if (isset($data['cancel']) && isset($data['transaction_id'])) {
            $transaction = $this->transactionFactory->create()->load($data['transaction_id']);
            if (!$transaction->getId()) {
                $this->messageManager->addError(__('The transaction no longer exists.'));
                $this->_redirect('*/*/edit', [
                    'transaction_id' => $data['transaction_id']
                    ]);
                return;
            }
            try {
                $status = $transaction->getStatus();
                $amount = $transaction->getAmount();
                $customer        = $transaction->getRewardsCustomer();
                $availablePoints = $customer->getAvailablePoints();
                $totalPoints     = $customer->getTotalPoints();
                if ($transaction->getStatus() == Transaction::STATE_COMPLETE && $amount > 0 && $transaction->getAmount() > $availablePoints) {
                    $this->messageManager->addError(__('Account points is not enough points to cancel the transaction.'));
                    $this->_redirect('*/*/edit', [
                        'transaction_id' => $data['transaction_id']
                        ]);
                    return;
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

                $this->messageManager->addSuccess(__('Transaction has been canceled successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            $this->_redirect('*/*/edit', [
                'transaction_id' => $data['transaction_id']
                ]);
            return;
        }

        /**
         * ADD NEW TRANSACTION
         */
        if ($data && isset($data['customerids']) && $data['customerids']) {
            $amount = (float) str_replace(" ", "", trim($data['amount']));
            if (!$amount) {
                $this->messageManager->addError(__('Data is not valid. Cannot create transaction.'));
                $this->_redirect('*/*/new');
                return;
            }

            try {
                $collection = $this->customerCollectionFactory->create()->addFieldToFilter('customer_id', ['in' => $data['customerids']]);
                $customers = [];
                foreach ($collection as $_customer) {
                    $customers[$_customer->getCustomerId()] = (float) $_customer->refreshPoints()->getAvailablePoints();
                }

                $todayDate = new \DateTime($data['today_date']);


                /**
                 * Expires date
                 */
                if (isset($data['expires_at']) && $data['expires_at']) {
                    $expriesDate = new \DateTime($data['expires_at']);
                    if ($expriesDate < $todayDate) {
                     $this->messageManager->addError(__('Expires Date must follow Today Date.'));
                     $this->_redirect('*/*/new');
                     return;
                 }
                }


                /**
                 * Apply date
                 */
                if (isset($data['apply_at']) && $data['apply_at']) {
                    $applyDate = new \DateTime($data['apply_at']);
                    if ($applyDate < $todayDate) {
                        $this->messageManager->addError(__('Apply Date must follow Today Date.'));
                        $this->_redirect('*/*/new');
                        return;
                    }
                }


                /**
                 * Expires date vs Apply date
                 */
                if ((isset($data['expires_at']) && $data['expires_at']) && (isset($data['apply_at']) && $data['apply_at'])) {
                    if ($expriesDate < $applyDate) {
                        $this->messageManager->addError(__('Expires Date must follow Apply Date.'));
                        $this->_redirect('*/*/new');
                        return;
                    }
                }

                $faildCount = $successCount = 0;
                $customerIds = explode(',', $data['customerids']);
                foreach ($customerIds as $k => $customerId) {
                    $customer = $this->rewardsCustomer->getCustomer($customerId)->save();
                    if ( ($amount + $customer->getAvailablePoints()) < 0) {
                        $faildCount ++;
                        $this->messageManager->addError(__('[Customer ID: %1] Account points is not enough points to create a transaction.', $customerId));
                        continue;
                    }

                    // Save Transaction
                    $transaction = $this->_objectManager->create('Lof\RewardPoints\Model\Transaction');
                    $status = \Lof\RewardPoints\Model\Transaction::STATE_COMPLETE;
                    if (isset($data['apply_at']) && $data['apply_at'] && ($applyDate > $todayDate)) {
                        $status = \Lof\RewardPoints\Model\Transaction::STATE_PROCESSING;
                    }
                    $transaction->setData([
                        'customer_id'   => $customerId,
                        'amount'        => $amount,
                        'title'         => $data['title'],
                        'code'          => Transaction::ADMIN_ADD_TRANSACTION . '-' . $data['form_key'],
                        'email_message' => $data['email_message'],
                        'amount_used'   => 0,
                        'is_applied'    => 0,
                        'is_expired'    => 0,
                        'status'        => $status,
                        'action'        => Transaction::ADMIN_ADD_TRANSACTION,
                        'expires_at'    => $data['expires_at'],
                        'apply_at'      => $data['apply_at'],
                        'store'         => (int) $this->storeManager->getStore()->getId(),
                        'admin_user_id' => $this->authSession->getUser()->getId()
                        ]);

                    $isSendMail = false;
                        /**
                         * Send email if apply at is empty
                         */
                        if (!$data['apply_at'] || ($data['apply_at'] && ($applyDate == $todayDate))) {
                            $isSendMail = true;
                            $transaction->setIsApplied(1);
                        } else {
                            $transaction->setIsExpirationEmailSent(1);
                        }

                        $transaction->save();

                        /**
                         * Save Customer
                         */
                        $customer->refreshPoints()->save();

                        /**
                         * Send Mail
                         */
                        if ($isSendMail) {
                            $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $data['email_message']);
                        }

                        $successCount ++;
                    }

                    if ($successCount==1) {
                        $this->messageManager->addSuccess(__('The transaction was successfully saved.'));
                    }
                    if ($successCount>1) {
                        $this->messageManager->addSuccess(__('There were %1 transactions successfully saved.', $successCount));
                    }
                    if ($faildCount>1) {
                        $this->messageManager->addError(__('There were %1 customer balance were not enough to create a transaction.', $faildCount));
                    }
                } catch (\Exception $e) {
                   $this->messageManager->addError($e->getMessage());
               }
           } else {
              $this->messageManager->addError(__('Please select a customer for the transaction'));
          }
          $this->_redirect('*/*/new');
          return;
      }
      /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::transaction_save');
    }
  }
