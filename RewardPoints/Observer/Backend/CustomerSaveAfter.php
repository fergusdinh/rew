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

namespace Lof\RewardPoints\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Lof\RewardPoints\Model\Config;
use Lof\RewardPoints\Model\Transaction;

class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Lof\RewardPoints\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Lof\RewardPoints\Model\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Magento\Backend\Model\Auth\Session         $authSession
     * @param \Lof\RewardPoints\Model\CustomerFactory     $customerFactory
     * @param \Lof\RewardPoints\Model\TransactionFactory  $transactionFactory
     * @param \Lof\RewardPoints\Helper\Customer           $rewardsCustomer
     * @param \Lof\RewardPoints\Helper\Mail               $rewardsMail
     * @param \Lof\RewardPoints\Model\Config              $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Lof\RewardPoints\Model\CustomerFactory $customerFactory,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->messageManager     = $messageManager;
        $this->storeManager       = $storeManager;
        $this->authSession        = $authSession;
        $this->customerFactory    = $customerFactory;
        $this->transactionFactory = $transactionFactory;
        $this->rewardsCustomer    = $rewardsCustomer;
        $this->rewardsMail        = $rewardsMail;
        $this->rewardsConfig      = $rewardsConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsConfig->isEnable()) {
            $request         = $observer->getEvent()->getRequest();
            $customer        = $observer->getEvent()->getCustomer();
            $data            = $request->getPostValue();
            $customerId      = $customer->getId();
            $transactionData = isset($data['rewardpoints'])?$data['rewardpoints']:false;
            $rewardsCustomer = $this->rewardsCustomer->getCustomer($customerId)->save();
            if ($transactionData) {
                $amount = (int) str_replace(" ", "", trim($transactionData['amount']));
                if (!$amount) {
                    //$this->messageManager->addError(__('Data is not valid. Cannot create transaction.'));
                    return;
                }

                $todayDate = new \DateTime($transactionData['today_date']);

                /**
                 * Expires date
                 */
                if (isset($transactionData['expires_at']) && $transactionData['expires_at']) {
                    $expriesDate = new \DateTime($transactionData['expires_at']);
                    if ($expriesDate < $todayDate) {
                        $this->messageManager->addError(__('Expires Date must follow Today Date.'));
                        return;
                    }
                }


                /**
                 * Apply date
                 */
                if (isset($transactionData['apply_at']) && $transactionData['apply_at']) {
                    $applyDate = new \DateTime($transactionData['apply_at']);
                    if ($applyDate < $todayDate) {
                        $this->messageManager->addError(__('Apply Date must follow Today Date.'));
                        return;
                    }
                }


                /**
                 * Expires date vs Apply date
                 */
                if ((isset($transactionData['expires_at']) && $transactionData['expires_at']) && (isset($transactionData['apply_at']) && $transactionData['apply_at'])) {
                    if ($expriesDate < $applyDate) {
                        $this->messageManager->addError(__('Expires Date must follow Apply Date.'));
                        return;
                    }
                }


                /**
                 * Reward Customer
                 */
                $totalPoints     = $rewardsCustomer->getTotalPoints();
                $availablePoints = $rewardsCustomer->getAvailablePoints();

                if (($transactionData['amount'] < 0) && (($transactionData['amount'] + $availablePoints) < 0)) {
                    $this->messageManager->addError(__('Account points is not enough to create a transaction.', $customerId));
                    return;
                }

                // Save Customer
                if ((!$transactionData['apply_at'] || ($transactionData['apply_at'] && ($applyDate == $todayDate))) && (isset($customers[$customerId]) || $amount >0)) {
                    $totalPoints     += $amount;
                    $availablePoints += $amount;
                    $rewardsCustomer->setAvailablePoints($availablePoints);
                    $rewardsCustomer->setTotalPoints($totalPoints);
                    $rewardsCustomer->setUpdatePointNotification($transactionData['update_point_notification']);
                    $rewardsCustomer->setExpirePointNotification($transactionData['expire_point_notification']);
                    $rewardsCustomer->save();
                }


                /**
                 * Save Transaction
                 */
                $transaction = $this->transactionFactory->create();
                $status = \Lof\RewardPoints\Model\Transaction::STATE_COMPLETE;
                if (isset($transactionData['apply_at']) && $transactionData['apply_at'] && ($applyDate > $todayDate)) {
                    $status = \Lof\RewardPoints\Model\Transaction::STATE_PROCESSING;
                }
                $saveTransactionData = [
                    'customer_id'   => $customerId,
                    'amount'        => $amount,
                    'title'         => $transactionData['title'],
                    'code'          => Transaction::ADMIN_ADD_TRANSACTION . '-' . $data['form_key'],
                    'email_message' => $transactionData['email_message'],
                    'amount_used'   => 0,
                    'status'        => $status,
                    'action'        => Transaction::ADMIN_ADD_TRANSACTION,
                    'expires_at'    => $transactionData['expires_at'],
                    'store_id'      => (int) $this->storeManager->getStore()->getId(),
                    'admin_user_id' => $this->authSession->getUser()->getId()
                ];
                if ($transactionData['apply_at']) {
                    $saveTransactionData['apply_at'] = $transactionData['apply_at'];
                }
                $transaction->setData($saveTransactionData);
                $transaction->save();

                /**
                 * Send email if apply at is empty
                 *
                 */
                if ((!$transactionData['apply_at'] || ($transactionData['apply_at'] && ($applyDate == $todayDate)))) {
                    $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, $transactionData['email_message']);
                }

                $this->messageManager->addSuccess(__('Transaction was successfully saved'));
            }
        }
    }
}
