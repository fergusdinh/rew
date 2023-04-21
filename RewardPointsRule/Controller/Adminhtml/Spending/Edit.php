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

namespace Lof\RewardPointsRule\Controller\Adminhtml\Spending;

use Lof\RewardPointsRule\Model\Spending;

class Edit extends \Lof\RewardPoints\Controller\Adminhtml\Spending
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
     * Edit Spending Rate
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id      = $this->getRequest()->getParam('rule_id');
        $model   = $this->_objectManager->create('Lof\RewardPointsRule\Model\Spending');
        $storeId = $this->getRequest()->getParam('store');

        if(!$id) {
            $type = $this->getRequest()->getParam('type');
            $model->setType($type);
        }


        // 1. Get rule by object id
        if($id && $storeId) {
            $id = $this->rewardsData->setType($model->getType())->getRuleInAdmin($id, $storeId, false)->getId();
        }

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('spending_rate', $model);

        $model->getConditions()->setFormName('spendingrule_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );
        $model->getActions()->setFormName('spendingrule_form');
        $model->getActions()->setJsFormObject(
            $model->getActionsFieldSetId($model->getActions()->getFormName())
        );

        
        $type = $model->getType();
        $title = '';
        switch ($type) {
            case Spending::PRODUCT_RULE:
                $title = __('Catalog Rules');
                break;
            
            case Spending::CART_RULE:
                $title = __('Shopping Cart Rules');
                break;
        }

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Rule') : __('New Rule'),
            $id ? __('Edit Rule') : __('New Rule')
            );
        $resultPage->getConfig()->getTitle()->prepend($title);
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Rule'));
        return $resultPage;
    }
}
