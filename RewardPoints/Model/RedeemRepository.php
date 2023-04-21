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
 * @package    Lof_Gallery
 * @copyright  Copyright (c) 2020 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Model;

use Lof\RewardPoints\Api\RedeemRepositoryInterface;
use Lof\RewardPoints\Api\Data\RedeemInterfaceFactory;
use Lof\RewardPoints\Api\Data\RedeemSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Lof\RewardPoints\Model\RedeemFactory;
use Lof\RewardPoints\Model\ResourceModel\Redeem as ResourceRedeem;
use Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory as RedeemCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Lof\RewardPoints\Model\Email;
use Lof\RewardPoints\Model\Transaction;

class RedeemRepository implements RedeemRepositoryInterface
{

    protected $resource;

    protected $redeemFactory;

    protected $redeemCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataRedeemFactory;

    protected $storeManager;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    protected $helperData;

    protected $transactionFactory;

    protected $codelogFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Mail
     */
    protected $rewardsMail;

    protected $transactionCollectionFactory;

    protected $orderFactory;

    protected $escaper;

    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @param ResourceRedeem $resource
     * @param RedeemFactory $redeemFactory
     * @param RedeemInterfaceFactory $dataRedeemFactory
     * @param RedeemCollectionFactory $redeemCollectionFactory
     * @param RedeemSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $config
     * @param \Lof\RewardPoints\Helper\Data $helperData
     * @param \Lof\RewardPoints\Model\TransactionFactory $transactionFactory
     * @param \Lof\RewardPoints\Model\CodelogFactory $codelogFactory
     * @param \Lof\RewardPoints\Helper\Mail                                       $rewardsMail
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Lof\RewardPoints\Model\Config                                      $rewardsConfig
     */
    public function __construct(
        ResourceRedeem $resource,
        RedeemFactory $redeemFactory,
        RedeemInterfaceFactory $dataRedeemFactory,
        RedeemCollectionFactory $redeemCollectionFactory,
        RedeemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $config,
        \Lof\RewardPoints\Helper\Data $helperData,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Lof\RewardPoints\Model\CodelogFactory $codelogFactory,
        \Lof\RewardPoints\Helper\Mail                                       $rewardsMail,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Escaper $escaper,
        \Lof\RewardPoints\Model\Config                                      $rewardsConfig
    ) {
        $this->resource = $resource;
        $this->redeemFactory = $redeemFactory;
        $this->redeemCollectionFactory = $redeemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRedeemFactory = $dataRedeemFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->_config = $config;
        $this->helperData = $helperData;
        $this->transactionFactory = $transactionFactory;
        $this->codelogFactory = $codelogFactory;
        $this->rewardsMail = $rewardsMail;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->escaper = $escaper;
        $this->rewardsConfig                = $rewardsConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\RewardPoints\Api\Data\RedeemInterface $redeem
    ) {
        try {
            $rewardsData = $this->helper;
            $prefix 	 = htmlentities($redeem->getCodePrefix());
            if($prefix) 
                $prefix 	 = str_replace(" ", "_", $prefix);
            
            $code = $redeem->getCode();
            if(!$code){
                $code = strtoupper($rewardsData->generateCouponCode($prefix, 2, 3, 3));
            }else {
                $code 	 = $prefix . $code;
                $redemCode = $this->redeemCollectionFactory->create();
                $checkCode = $redemCode->addFieldToFilter("code", $code)->getLastItem();
                $codeId = $redeem->getCodeId();
                if($codeId != $checkCode->getCodeId()){
                    throw new CouldNotSaveException(__(
                        'Could not save the redeem: Because the code %1 is exists.',
                        $code
                    ));
                    return $redeem;
                }
            }
            $redeem->setCode($code);
            $redeem->setCodePrefix($prefix);
            $this->resource->save($redeem);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the redeem: %1',
                $exception->getMessage()
            ));
        }
        return $redeem;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($redeemId)
    {
        $redeem = $this->redeemFactory->create();
        $this->resource->load($redeem, $redeemId);
        if (!$redeem->getId()) {
            throw new NoSuchEntityException(__('Redeem with id "%1" does not exist.', $redeemId));
        }
        return $redeem;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->redeemCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\RewardPoints\Api\Data\RedeemInterface $redeem
    ) {
        try {
            $this->resource->delete($redeem);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the redeem: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($redeemId)
    {
        return $this->delete($this->getById($redeemId));
    }
    /**
     * {@inheritdoc}
     */
    public function applyCode($customerId, $code, $storeId){
        if(!$customerId)
            throw new NoSuchEntityException(__('Please logged in your account.'));
        $code = trim($code);
        $code = $this->escaper->escapeHtml($code);
        if(!$code)
            throw new NoSuchEntityException(__('Please input valid redeem code.'));

        $flag = false;
        $redemCode = $this->redeemCollectionFactory->create();
        $redemCode->addFieldToFilter("code", $code);
        $redemCode->getSelect()->where("((active_to >= NOW() OR active_to IS NULL) AND (active_from < NOW()) OR active_from IS NULL ) AND code_used < uses_per_code");
        $transaction = $this->transactionFactory->create();

        if ($redemCode->count() > 0) {
            $codeData = $redemCode->getLastItem();
            $checkUsed = $this->validateCode($codeData->getCodeId(), $customerId, $storeId);
            if ($checkUsed) {
                $message  = __('You used reward code "%1".', $code);
                $new_data = [
                    'customer_id'   => $customerId,
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
                    $codeLogModel->setUserId($customerId);
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
                    ->setCustomerId($customerId)
                    ->setStatus(Transaction::STATE_COMPLETE)
                    ->save();
                    $updateTransaction->load($transaction->getId())->setData('code', '')->save();
                    $this->rewardsMail->sendNotificationBalanceUpdateEmail($transaction, '', Email::ACTION_APPLY_REWARD_CODE);
                    $flag = true;
                }
            }
        }

        if(!$flag){
            throw new NoSuchEntityException(__('The reward code "%1" is not valid.', $code));
            return false;
        }
        return true;
    }

    public function validateCode($codeId='', $customerId = 0, $storeId = null)
    {
        $codeModel = $this->codelogFactory->create();
        $storeId = ($storeId!=null)?(int)$storeId:(int) $this->storeManager->getStore()->getId();
        return $codeModel->validateCode($codeId, $customerId, $storeId);
    }
}
