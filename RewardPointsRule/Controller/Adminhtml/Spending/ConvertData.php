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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Controller\Adminhtml\Earning;

class ConvertData extends \Lof\RewardPoints\Controller\Adminhtml\Earning
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context        $context           
     * @param \Magento\Framework\Registry                $coreRegistry      
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory 
     * @param \Lof\RewardPoints\Helper\Data              $rewardsData       
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\RewardPointsRule\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->coreRegistry      = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardsData       = $rewardsData;
    }

    /**
     * Edit Earning Rate
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $model   = $this->_objectManager->create('Lof\RewardPointsRule\Model\Spending');
        try {
            $model->convertSerializedDataToJson();
            $this->messageManager->addSuccess(__('You converted spending serialized condition rules data to JSON data completely.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                __('Something went wrong while convert spending serialzied condition rules data to JSON data. Please review the error log.')
                );
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/index');
            return;
        }
        
        return $resultRedirect->setPath('*/*/index');
    }
}
