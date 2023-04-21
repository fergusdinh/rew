<?php

namespace Lof\RewardPoints\Controller\Adminhtml\Redeem\Import;
use Magento\Framework\Json\EncoderInterface;
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
        \Lof\RewardPoints\Model\Cron $rewardsCron
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
    }

    public function execute()
    {
        try{
            $datas = $this->ReadCSVFile(); 
            if(count($datas) > 0){
                $todayDate = new \DateTime();
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $CustomerModel = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
                $redeemFactory = $objectManager->get('Lof\RewardPoints\Model\RedeemFactory')->create();
                $redeemCollectionFactory = $this->_objectManager->get('Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory');
                $faildCount = $successCount = 0;
                foreach ($datas as $key => $data) {
                    if (isset($data['active_from']) && $data['active_from'] != '') {
                        if(isset($data['active_to']) && $data['active_to'] != ''){
                            $expriesDate = new \DateTime($data['active_to']);
                            $applyDate = new \DateTime($data['active_from']);
                            if($expriesDate < $applyDate){
                                $faildCount += 1;
                                continue;
                            }
                        }else{
                            $data['active_to'] = '';
                        }
                    }
                    $data["code"] = $data["code_prefix"] . $data["code"];
                    $redemCode = $redeemCollectionFactory->create();
                    $codeData = $redemCode->addFieldToFilter("code", $data["code"])->getLastItem();
                    if($codeData){
                        $data["code_id"] = $codeData->getCodeId();
                        if($codeData->setData($data)->save()){
                            $successCount += 1;
                        }else{
                            $faildCount += 1;
                        }

                    }else{

                        if($redeemFactory->setData($data)->save()){
                            $successCount += 1;
                        }else{
                            $faildCount += 1;
                        }

                    }
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) have been imported.', $successCount));
                if($faildCount > 0){
                    $this->messageManager->addError(__("A total of %1 record(s) can't import.", $faildCount));
                }
            }else{
                $this->messageManager->addSuccess(__('Data already updated.'));
            }

        }catch(\Exception $e){
            $this->messageManager->addError(__("Can't import data<br/> %1", $e->getMessage()));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    public function ReadCSVFile(){
        $filePath = $fileContent = '';
        try {
            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'data_import_file']
            );

            $fileContent = '';
            if($uploader) {
                $tmpDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::TMP);
                $savePath     = $tmpDirectory->getAbsolutePath('lof/import');
                $uploader->setAllowRenameFiles(true);
                $result       = $uploader->save($savePath);
                $filePath = $tmpDirectory->getAbsolutePath('lof/import/' . $result['file']);
                $fileContent  = file_get_contents($tmpDirectory->getAbsolutePath('lof/import/' . $result['file']));
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

        if (!in_array("code", $arr_key) || !in_array("earn_points", $arr_key)) {
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
        $datas = $this->CheckCode($datas); 
        return $datas;
    }

    public function CheckCode($arrCode =[]){
        $redeemCollectionFactory = $this->_objectManager->get('Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory');
        foreach ($arrCode as $key => $val) {
            if(isset($val["code"]) && $val["code"] != ''){
                $redemCode = $redeemCollectionFactory->create();
                $flag = $redemCode->addFieldToFilter("code", $val["code_prefix"] . $val["code"])
                                ->addFieldToFilter("earn_points", $val["earn_points"])->getLastItem();
                if($flag->getCodeId()){
                    unset($arrCode[$key]);
                }
            }
        }
        return $arrCode;
    }
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_PointCode::import_point');
    }
}
