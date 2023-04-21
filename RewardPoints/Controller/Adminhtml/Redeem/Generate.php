<?php

namespace Lof\RewardPoints\Controller\Adminhtml\Redeem;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Action\Action;

class Generate extends \Lof\RewardPoints\Controller\Adminhtml\Redeem
{

    protected $_storeManager;

	protected $helper;

	protected $request;

	protected $redeem;

	protected $collectionFactory;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		StoreManagerInterface $storeManager,
		\Lof\RewardPoints\Helper\Data $helper,
		\Lof\RewardPoints\Model\RedeemFactory $redeemFactory,
		\Magento\Framework\App\Request\Http $request,
		\Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory $collectionFactory
		){

		parent::__construct($context);
		$this->_storeManager       	= $storeManager;
		$this->helper       	   	= $helper;
		$this->request 				= $request;
		$this->redeem 				= $redeemFactory;
		$this->collectionFactory	= $collectionFactory;
	}

	/*
	* Example Url:
	*/
	public function execute()
	{
		$rewardsData = $this->helper;
		$redirect 	 = $this->resultRedirectFactory->create();
		$data 		 = $this->getRequest();
		$prefix 	 = htmlentities($data->getPost('code_prefix'));
		if($prefix) 
			$prefix 	 = str_replace(" ", "_", $prefix);
		
		$usesPerCode = $data->getPost('uses_per_code');
		$earnPoints  = $data->getPost('earn_points');
		$activeFrom  = $data->getPost('active_from');
		$activeTo 	 = $data->getPost('active_to');
		if(!$data->getPost('auto_generate')){
			$code 	 = htmlentities($data->getPost('code'));
			$code 	 = $prefix . $code;
			$redemCode = $this->collectionFactory->create();
            $checkCode = $redemCode->addFieldToFilter("code", $code)->getLastItem();
			if($checkCode->getCodeId()){
				$this->messageManager->addSuccess(__('Code already exist!'));
				return $redirect->setPath('*/*/new');
			}
		}else{
			$code 	 = strtoupper($rewardsData->generateCouponCode($prefix, 2, 3, 3));
		}
		$data = [
			"code_prefix"	=> $prefix,
			"code"			=> $code,
			"earn_points"	=> $earnPoints,
			"uses_per_code"	=> $usesPerCode,
			"store_id"		=> (int) $this->_storeManager->getStore()->getId(),
			"active_from"	=> $activeFrom,
			"active_to"		=> $activeTo
		];
		$redeem = $this->redeem->create(); 
		try {
			$redeem->setData($data)->save();
			$this->messageManager->addSuccess(__('Generate redeem code success!'));
		} catch(\Exception $e){
            $this->messageManager->addError(__("%1", $e->getMessage()));
        }

        return $redirect->setPath('*/*/new');
    }
}