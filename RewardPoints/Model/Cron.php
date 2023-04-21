<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
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

namespace Lof\RewardPoints\Model;

class Cron
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance
     */
    protected $rewardsBalance;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                         $date
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Lof\RewardPoints\Helper\Balance                                    $rewardsBalance
     * @param \Lof\RewardPoints\Helper\Purchase                                   $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Mail                                       $rewardsMail
     * @param \Lof\RewardPoints\Logger\Logger                                     $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                                      $rewardsConfig
     */
    public function __construct(
      \Magento\Framework\Stdlib\DateTime\DateTime $date,
      \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
      \Lof\RewardPoints\Helper\Balance $rewardsBalance,
      \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
      \Lof\RewardPoints\Helper\Mail $rewardsMail,
      \Lof\RewardPoints\Logger\Logger $rewardsLogger,
      \Lof\RewardPoints\Model\Config $rewardsConfig
    ) {
        $this->date                         = $date;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardsBalance               = $rewardsBalance;
        $this->rewardsPurchase              = $rewardsPurchase;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardsLogger                = $rewardsLogger;
        $this->rewardsConfig                = $rewardsConfig;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->rewardsPurchase->resetRewardsPurchase();

    	// Update Balance status
        $this->rewardsBalance->proccessTransaction();

    	// Send points expire email
        $this->sendPointsExpireEmail();
    }

    /**
     * @param bool|string $now
     * @return void
     */
    public function sendPointsExpireEmail()
    {
        try {
            $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            $earingExpireDate = $this->rewardsConfig->getEarningExpireDate('Y-m-d h:m:s');
            $transactions = $this->transactionCollectionFactory->create()
            ->addFieldToFilter('is_expired', [
                ['eq'=>0],
                ['null' => true]
            ])
            ->addFieldToFilter('is_expiration_email_sent', [
                ['eq'=>0],
                ['null' => true]
            ])
            ->addFieldToFilter('status', \Lof\RewardPoints\Model\Transaction::STATE_COMPLETE);
            $transactions->getSelect()->where('expires_at > "'.$now.'"')
            ->where('expires_at < "'.$earingExpireDate.'"')
            ->where('amount > amount_used OR amount_used IS NULL');
            $transactions->getSelect()->where('amount > amount_used OR amount_used IS NULL');

            foreach ($transactions as $transaction) {
                $amount   = $transaction->getAmount() - $transaction->getAmountUsed();
                $customer = $transaction->getRewardCustomer();
                if($customer) {
                    if ($amount > $customer->getAvailablePoints()) {
                        $amount = $customer->getAvailablePoints();
                    }
                }
                if (!$amount) {
                    continue;
                }
                $transaction->setAmount($amount);
                $this->rewardsMail->sendNotificationPointsExpireEmail($transaction);
                $transaction->setIsExpirationEmailSent(1)->save();
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
    }

}
