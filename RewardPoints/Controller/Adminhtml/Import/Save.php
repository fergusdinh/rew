<?php
/**
 * LandofCoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   LandofCoder
 * @package    Lof_PointCode
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Controller\Adminhtml\Import;
use Lof\RewardPoints\Model\Transaction;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Backend\App\Action
{

    protected $_scopeConfig;

    protected $_storeManager;

    protected $csvProcessor;

    protected $authSession;

    protected $transactionFactory;

    protected $customerCollectionFactory;

    protected $rewardsCustomer;

    protected $customerRepositoryInterface;

    protected $fileSystem;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Lof\RewardPoints\Model\TransactionFactory $transactionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        \Lof\RewardPoints\Model\Cron $rewardsCron,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->_storeManager     = $storeManager;
        $this->_scopeConfig      = $scopeConfig;
        $this->_resource         = $resource;
        $this->csvProcessor      = $csvProcessor;
        $this->authSession               = $authSession;
        $this->transactionFactory        = $transactionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->rewardsCustomer           = $rewardsCustomer;
        $this->rewardsData               = $rewardsData;
        $this->rewardsCron               = $rewardsCron;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->fileSystem          = $fileSystem;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $filePath = '';
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'data_import_file']
            );

            if($uploader) {
                $tmpDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::TMP);
                $savePath     = $tmpDirectory->getAbsolutePath('lof/import');
                $uploader->setAllowRenameFiles(true);
                $result       = $uploader->save($savePath);
                $filePath = $tmpDirectory->getAbsolutePath('lof/import/' . $result['file']);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }
        $delimiter = $this->getRequest()->getParam('split_symbol');
        if($delimiter) {
            $importData = $this->csvProcessor->setDelimiter($delimiter)->getData($filePath);
        } else {
            $importData = $this->csvProcessor->getData($filePath);
        }
        $arr_key = $importData[0];
        unset($importData[0]);

        if (!in_array("amount", $arr_key)) {
            $this->messageManager->addError(__('Data is not valid. Cannot import.'));
            $this->_redirect('*/*/index');
            return;
        }
        $datas = [];
        foreach ($importData as $keys => $value) {
            foreach ($value as $key => $val) {
                $datas[$keys][$arr_key[$key]] = $val;
            }
        }

        try{

            if($datas){
                $todayDate = new \DateTime();
                $customerModel = $this->customerRepositoryInterface;

                $faildCount = $successCount = 0;
                foreach ($datas as $key => $data) {
                    $is_expired = 0;
                    $is_applied = 0;
                    if (isset($data['expires_at']) && isset($data['apply_at'])) {
                        $expriesDate = new \DateTime($data['expires_at']);
                        $applyDate = new \DateTime($data['apply_at']);
                        if($applyDate < $todayDate || $expriesDate < $todayDate || $expriesDate < $applyDate){
                            $is_expired = 1;
                            $is_applied = 1;
                        }
                    }

                    // $customerData = $customerModel->loadByEmail($data['customer_email']);
                    if(null !== $data['customer_email']){

                        $customerData = $customerModel->get($data['customer_email'], $websiteId = null);
                        if( null !== $customerData->getId()){
                            $customerId = $customerData->getId();
                            //$customer = $this->rewardsCustomer->getCustomer($customerId);

                            // Save Transaction
                            $transaction = $this->transactionFactory->create();
                            $status = Transaction::STATE_COMPLETE;
                            if (isset($data['apply_at']) && $data['apply_at'] && ($applyDate > $todayDate)) {
                                $status = Transaction::STATE_PROCESSING;
                            }
                            $new_data = [
                                'customer_id'   => $customerId,
                                'amount'        => (float)$data['amount'],
                                'title'         => isset($data['title'])? $data['title'] : '',
                                'amount_used'   => 0,
                                'is_applied'    => (null !== $is_applied)? $is_applied : 0,
                                'is_expired'    => (null !== $is_expired)? $is_expired : 0,
                                'status'        => $status,
                                'action'        => Transaction::ADMIN_ADD_TRANSACTION,
                                'store'         => (int) $this->_storeManager->getStore()->getId(),
                                'admin_user_id' => $this->authSession->getUser()->getId()
                                ];
                            if(isset($data['expires_at']) && $data['expires_at']) {
                                $new_data['expires_at']   = $data['expires_at'];

                            }
                            if(isset($data['apply_at']) && $data['apply_at']){
                                $new_data['apply_at']     = $data['apply_at'];
                            }
                            if(isset($data['amount_used']) && $data['amount_used']){
                                $new_data['amount_used']     = (float)$data['amount_used'];
                            }
                            $transaction->setData($new_data);
                            if($transaction->save()){
                                //$customer->refreshPoints();
                                $successCount += 1;
                            }else{
                                $faildCount += 1;
                            }
                        }
                    }
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) have been imported.', $successCount));
                if($faildCount > 0){
                    $this->messageManager->addError(__("A total of %1 record(s) can't import.", $faildCount));
                }
            }

        }catch(\Exception $e){
            $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_PointCode::import_point');
    }
}
