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

namespace Lof\RewardPoints\Helper;

use Lof\RewardPoints\Model\Email as RewardsEmail;
use Lof\RewardPoints\Model\ResourceModel\Email\CollectionFactory;
use Lof\RewardPoints\Model\Transaction;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Lof\RewardPoints\Model\TransportBuilder
     */
    protected $rewardsTransportBuilder;

    /**
     * @var \Lof\RewardPoints\Model\EmailFactory
     */
    protected $rewardsEmail;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var string
     */
    protected $trigger;

    /**
     * @var string
     */
    protected $order;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $params;
    protected  $rewardEmailCollection;

    /**
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Magento\Framework\Filter\FilterManager            $filterManager
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $dateTime
     * @param \Magento\Framework\Message\ManagerInterface        $messageManager
     * @param \Lof\RewardPoints\Model\TransportBuilder           $rewardsTransportBuilder
     * @param \Lof\RewardPoints\Model\EmailFactory               $rewardsEmail
     * @param \Lof\RewardPoints\Helper\Data                      $rewardsData
     * @param \Lof\RewardPoints\Logger\Logger                    $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                     $rewardsConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Lof\RewardPoints\Model\TransportBuilder $rewardsTransportBuilder,
        \Lof\RewardPoints\Model\EmailFactory $rewardsEmail,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        CollectionFactory $rewardEmailCollection
    ) {
        parent::__construct($context);
        $this->context                 = $context;
        $this->filterManager           = $filterManager;
        $this->inlineTranslation       = $inlineTranslation;
        $this->dateTime                = $dateTime;
        $this->messageManager          = $messageManager;
        $this->rewardsTransportBuilder = $rewardsTransportBuilder;
        $this->rewardsEmail            = $rewardsEmail;
        $this->rewardsData             = $rewardsData;
        $this->rewardsLogger           = $rewardsLogger;
        $this->rewardsConfig           = $rewardsConfig;
        $this->rewardEmailCollection   = $rewardEmailCollection;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function getTrigger()
    {
        return $this->trigger;
    }

    public function setTrigger($trigger) {
        $this->trigger = $trigger;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams($params) {
        $this->params = $params;
        return $this;
    }

    public function getConfig()
    {
        return $this->rewardsConfig;
    }

    public function send( $templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId, $trigger = '')
    {
        $email = $this->rewardsEmail->create();
//        $email = $this->rewardEmailCollection->create();
        if (!$senderName) {
            $this->rewardsLogger->addEror('Sender name is empty');
            return false;
        }

        if (!$templateName) {
            $this->rewardsLogger->addEror('Email template is empty');
            return false;
        }

        $transactionId = 0;
        if (isset($variables['transaction'])) {
            $transactionId = $variables['transaction']->getId();
        }

        $this->inlineTranslation->suspend();
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->rewardsTransportBuilder
                ->setTemplateIdentifier($templateName)
                ->setTemplateOptions([
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($variables)
                ->setFrom([
                    'name'  => $senderName,
                    'email' => $senderEmail
                ])
                ->addTo($recipientEmail, $recipientName)
                ->setReplyTo($senderEmail)
                ->getTransport();
            $transport->sendMessage();
            try {
               /**
                * Save Email log
                */
                $email->setSender_email($senderEmail)
                    ->setSender_name($senderName)
                    ->setSubject($this->rewardsTransportBuilder->getMessageSubject())
                    ->setRecipient_email($recipientEmail)
                    ->setRecipient_name($recipientName)
                    ->setMessage($this->rewardsTransportBuilder->getMessageContent())
                    ->setTransaction_id($transactionId)
                    ->setTrigger($trigger)
                    ->setStore_id($storeId)
                    ->setSent_at();
                $email->setStatus(RewardsEmail::STATE_SENT);
                $email->save();
            }catch(\Exception $e){
                $this->rewardsLogger->addError(('Email cant not sent'));
            }

        } catch (\Exception $e) {
            $email->setStatus(RewardsEmail::STATE_FAIELD);
            $email->setBug($e->getMessage());
            $this->inlineTranslation->resume();
            $this->rewardsLogger->addError(('Email cant not sent'));
        }
        return true;
    }

    public function parseVariables(Transaction $transaction, $message)
    {
        $customer = $transaction->getCustomer();
        $variables = [
            "customer"               => $customer,
            "store"                  => $customer?$customer->getStore():0,
            "transaction"            => $transaction,
            'title'                  => $transaction->getTitle(),
            "transaction_days_left"  => 0,
            'transaction_title'      => $transaction->getTitle(),
            "transaction_created_at" => $transaction->getCreatedAt(),
            "transaction_amount"     => $this->rewardsData->formatPoints($transaction->getAmount()),
            "balance_total"          => $this->rewardsData->formatPoints($customer->getTotalPoints())
        ];
        $message = $this->filterManager->template($message, ['variables' => $variables]);
        return $message;
    }

    /**
     * @param Transaction $transaction
     * @param string $params
     * @return bool
     */
    public function sendNotificationBalanceUpdateEmail(Transaction $transaction, $params = '')
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderData = $objectManager->get("Magento\Sales\Api\OrderRepositoryInterface");
        $customer = $transaction->getCustomer();
        if (!$customer || !$customer->getUpdatePointNotification()) {
            return false;
        }
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $storeId = $storeManager->getStore()->getStoreId();

        $templateName = $this->getConfig()->getBalanceUpdateEmailTemplate($storeId);

        if (!$templateName) {
            return false;
        }


        if ($emailMessage = $this->getMessage()) {
            $emailMessage = $this->parseVariables($transaction, $emailMessage);
        }

        $recipientEmail = $customer->getEmail();
        $recipientName  = $customer->getName();
        $storeId        = $customer->getStore()->getId();
        $senderId       = $this->getConfig()->getSenderEmail();


        $senderName     = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
        $senderEmail    = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

        $variables = [
            "customer"           => $customer,
            "store"              => $customer->getStore(),
            "transaction"        => $transaction,
            "transaction_amount" => $this->rewardsData->formatPoints($transaction->getAmount()),
            "balance_total"      => $this->rewardsData->formatPoints($customer->getTotalPoints()),
            'message'            => $this->rewardsData->filter($emailMessage),
            'title'              => $transaction->getTitle(),
            'no_message'         => $emailMessage == false || $emailMessage == '',
        ];

        if ($params = $this->getParams()) {
            $variables = array_merge($variables, $params);
        }

        $trigger = $this->getTrigger();
        if (!$trigger) {
            $trigger = RewardsEmail::ACTION_BALANCE_UPDATE;
        }

        $order = $this->getOrder();
        if($order && $order->getId() && !$order->getCustomerIsGuest()){
            if($order->getShippingAddress()){
                $recipientEmail = $order->getShippingAddress()->getEmail();
                $recipientName  = $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname();
            }
        }

        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId, $trigger);

        return true;
    }


    /**
     * @param \Lof\Rewards\Model\Transaction  $transaction
     * @param bool|false                      $emailMessage
     * @return bool
     */
    public function sendNotificationBalanceExpiredEmail($transaction, $emailMessage = false)
    {
        $customer = $transaction->getCustomer();
        if (!$customer->getExpirePointNotification()) {
            return false;
        }

        $templateName = $this->getConfig()->getPointsExpiredEmailTemplate();
        if (!$templateName) {
            return false;
        }

        if ($emailMessage) {
            $emailMessage = $this->parseVariables($transaction, $emailMessage);
        }

        $recipientEmail = $customer->getEmail();
        $recipientName  = $customer->getName();
        $storeId        = $customer->getStore()->getId();
        $senderId       = $this->getConfig()->getSenderEmail();
        $senderName     = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
        $senderEmail    = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");

        $variables = [
            "customer"                  => $customer,
            "store"                     => $customer->getStore(),
            "transaction"               => $transaction,
            "transaction_amount"        => $this->rewardsData->formatPoints($transaction->getAmount()),
            "transaction_amountused"    => $this->rewardsData->formatPoints($transaction->getAmountUsed()),
            "transaction_amountexpired" => $this->rewardsData->formatPoints(($transaction->getAmount() - $transaction->getAmountUsed())),
            "balance_total"             => $this->rewardsData->formatPoints($customer->getTotalPoints()),
            'message'                   => $this->rewardsData->filter($emailMessage),
            'title'                     => $transaction->getTitle(),
            'no_message'                => $emailMessage == false || $emailMessage == '',
        ];
        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,  RewardsEmail::ACTION_POINTS_EXPIRED);
        return true;
    }

    public function setRewardCodeEmail($transaction, $order, $params = '')
    {
        // $templateName   = 'lofrewardpoints_notification_send_couponcode_email_template';
        $templateName   = $this->getConfig()->getBalanceUpdateEmailTemplate();
        $storeId        = $order->getStore()->getId();
        $senderId       = $this->getConfig()->getSenderEmail();
        $senderName     = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
        $senderEmail    = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");
        $recipientEmail = $order->getShippingAddress()->getEmail();
        $recipientName  = $order->getShippingAddress()->getFirstname() . ' ' . $order->getShippingAddress()->getLastname();
        $orderId        = '#' . $order->getIncrementId();
        $couponCode     = $transaction->getCode();

        $variables = [
            'store'                     => $order->getStore(),
            'transaction'               => $transaction,
            'coupon_code'               => $couponCode,
            'order_id'                  => $orderId,
            'transaction_amount'        => $this->rewardsData->formatPoints($transaction->getAmount()),
            "transaction_amountused"    => $this->rewardsData->formatPoints($transaction->getAmountUsed()),
            "transaction_amountexpired" => $this->rewardsData->formatPoints(($transaction->getAmount() - $transaction->getAmountUsed())),
            'transaction_comment'       => $transaction->getTitle(),
            'no_message'                => true,
        ];

        if ($params = $this->getParams()) {
            $variables = array_merge($variables, $params);
        }

        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,  RewardsEmail::ACTION_APPLY_REWARD_CODE);
    }

    /**
     * @param \Lof\Rewards\Model\Transaction  $transaction
     * @param bool|false                      $emailMessage
     * @return bool
     */
    public function sendNotificationPointsExpireEmail($transaction, $emailMessage = false)
    {
        $customer = $transaction->getCustomer();
        if (!$customer->getExpirePointNotification()) {
            return false;
        }

        $templateName = $this->getConfig()->getPointsExpireEmailTemplate();

        if (!$templateName) {
            return false;
        }

        if ($emailMessage) {
            $emailMessage = $this->parseVariables($transaction, $emailMessage);
        }

        $recipientEmail = $customer->getEmail();
        $recipientName  = $customer->getName();
        $storeId        = $customer->getStore()->getId();
        $senderId       = $this->getConfig()->getSenderEmail();
        $senderName     = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/name");
        $senderEmail    = $this->context->getScopeConfig()->getValue("trans_email/ident_" . $senderId . "/email");
        $this->rewardsData->setCurrentStore($customer->getStore());
        $variables = [
            'customer'                  => $customer,
            'store'                     => $customer->getStore(),
            'transaction'               => $transaction,
            'transaction_days_left'     => $transaction->getDaysLeft(),
            'transaction_amount'        => $this->rewardsData->formatPoints($transaction->getAmount()),
            "transaction_amountused"    => $this->rewardsData->formatPoints($transaction->getAmountUsed()),
            "transaction_amountexpired" => $this->rewardsData->formatPoints(($transaction->getAmount() - $transaction->getAmountUsed())),
            'transaction_comment'       => $transaction->getTitle(),
            'balance_total'             => $this->rewardsData->formatPoints($customer->getTotalPoints()),
            'message'                   => $this->rewardsData->filter($emailMessage),
            'no_message'                => $emailMessage == false || $emailMessage == '',
        ];

        $this->send($templateName, $senderName, $senderEmail, $recipientEmail, $recipientName, $variables, $storeId,  RewardsEmail::ACTION_POINTS_EXPIRE);

        return true;
    }
}
