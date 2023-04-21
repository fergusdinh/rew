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

namespace Lof\RewardPoints\Controller\RedeemCode;

use Lof\RewardPoints\Model\Email;
use Lof\RewardPoints\Model\Transaction;
use Magento\Store\Model\StoreManagerInterface;
class ApplyCode extends \Lof\RewardPoints\Controller\AbstractIndex
{
	/**
     * @var \Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory
     */
    protected $redeemCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    protected $storeManager;
    protected $codelogFactory;

    /**
     * @param \Magento\Framework\App\Action\Context                               $context
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $redeemCollectionFactory
     * @param \Lof\RewardPoints\Helper\Data                                       $rewardsData
     * @param \Lof\RewardPoints\Helper\Mail                                       $rewardsMail
     * @param \Lof\RewardPoints\Model\Config                                      $rewardsConfig
     * @param \Lof\RewardPoints\Model\CodelogFactory $codelogFactory
     * @param StoreManagerInterface $storeManager
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Lof\RewardPoints\Model\TransactionFactory $transactionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory $redeemCollectionFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Helper\Mail $rewardsMail,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Model\CodelogFactory $codelogFactory,
        StoreManagerInterface $storeManager,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Escaper $escaper
    ) {
        parent::__construct($context);
        $this->redeemCollectionFactory = $redeemCollectionFactory;
        $this->rewardsData                  = $rewardsData;
        $this->rewardsMail                  = $rewardsMail;
        $this->rewardsConfig                = $rewardsConfig;
        $this->codelogFactory               = $codelogFactory;
        $this->storeManager = $storeManager;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->orderFactory = $orderFactory;
        $this->escaper = $escaper;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();
        $flag = false;
        $escaper = $this->escaper;
        $code = isset($post['code'])? $escaper->escapeHtml(trim($post['code'])) : null;
        if ($code) {
            $redemCode = $this->redeemCollectionFactory->create();
            $redemCode->addFieldToFilter("code", $code);
            $redemCode->getSelect()->where("((active_to >= NOW() OR active_to IS NULL) AND (active_from < NOW()) OR active_from IS NULL ) AND code_used < uses_per_code");
            $customer = $this->rewardsData->getCustomer();
            $transaction = $this->transactionFactory->create();

            if ($redemCode->count() > 0) {
                $codeData = $redemCode->getLastItem();
                $checkUsed = $this->ValidateCode($codeData->getCodeId(), $customer->getId());
                if ($checkUsed) {
                    $message  = __('You used reward code "%1".', $code);
                    $storeId  = $this->storeManager->getStore()->getId();
                    $new_data = [
                        'customer_id'   => $customer->getId(),
                        'amount'        => $codeData->getEarnPoints(),
                        'title'         => $message,
                        'amount_used'   => 0,
                        'status'        => Transaction::STATE_COMPLETE,
                        'expires_at'    => $codeData->getActiveTo(),
                        'apply_at'      => $codeData->getActiveFrom(),
                        'store'         => (int) $storeId,
                    ];
                    $codeUsed = $codeData->getCodeUsed() + 1;
                    $codeLogModel = $this->codelogFactory->create();

                    if($transaction->setData($new_data)->save()){
                        $codeData->setData('code_used', $codeUsed)->save();
                        $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, '', Email::ACTION_APPLY_REWARD_CODE);

                        $codeLogModel->setCodeId($codeData->getCodeId());
                        $codeLogModel->setUserId($customer->getId());
                        $codeLogModel->setStoreId($storeId);
                        $codeLogModel->save();

                        $flag = true;
                    }
                }
            } else {
                $updateTransaction = $this->transactionFactory->create();
                $transaction = $this->transactionCollectionFactory->create()
                    ->addFieldToFilter('is_expired', [
                        ['eq' => NULL],
                        ['eq' => 0],
                    ])->addFieldToFilter('customer_id', [
                        ['eq' => NULL],
                        ['eq' => 0],
                    ])
                    ->addFieldToFilter('code', ['eq' => $code])
                    ->getFirstItem();

                if ($transaction->getId()) {
                    $order = $this->orderFactory->create()->load($transaction->getOrderId());
                    $status = $order->getStatus();
                    if (in_array($status, $this->rewardsConfig->getGeneralEarnInStatuses())) {
                        $message  = __('You used reward code "%1".', $code);
                        $transaction->setTitle($message)
                        ->setCustomerId($customer->getId())
                        ->setStatus(Transaction::STATE_COMPLETE)
                        ->save();
                        $updateTransaction->load($transaction->getId())->setData('code', '')->save();
                        $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, '', Email::ACTION_APPLY_REWARD_CODE);
                        $flag = true;
                    }
                }
            }
    	}
        if(!$flag){
            $this->messageManager->addError(__('The reward code "%1" is not valid.', $code));
        }else{
            $this->messageManager->addSuccess($message);
        }

        $this->_redirect('*/*/index');
        return;
    }
    public function ValidateCode($codeId='', $customerId = 0, $storeId = null)
    {
        $codeModel = $this->codelogFactory->create();
        $storeId = ($storeId!=null)?(int)$storeId:(int) $this->storeManager->getStore()->getId();
        return $codeModel->validateCode($codeId, $customerId, $storeId);
    }
}
