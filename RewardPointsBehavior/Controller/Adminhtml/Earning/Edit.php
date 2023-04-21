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
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Controller\Adminhtml\Earning;

use Lof\RewardPointsBehavior\Model\Earning;

class Edit extends \Lof\RewardPoints\Controller\Adminhtml\Earning
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
     * @param \Lof\RewardPointsBehavior\Helper\Data              $rewardsData       
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData
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
        // 1. Get ID and create model
        $id      = $this->getRequest()->getParam('rule_id');
        $model   = $this->_objectManager->create('Lof\RewardPointsBehavior\Model\Earning');
        $storeId = $this->getRequest()->getParam('store');


        // 1. Get rate by object id
        if($id && $storeId) {
            $id = $this->rewardsData->setType('earning')->getRuleInAdmin($id, $storeId, false)->getId();
        }

        if(!$id) {
            $model->setType(Earning::BEHAVIOR);
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

        $this->coreRegistry->register('earning_rate', $model);

        $model->getConditions()->setFormName('earning_rule_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );
        $model->getActions()->setFormName('earning_rule_form');
        $model->getActions()->setJsFormObject(
            $model->getActionsFieldSetId($model->getActions()->getFormName())
        );

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Rule') : __('New Rule'),
            $id ? __('Edit Rule') : __('New Rule')
            );
        $resultPage->getConfig()->getTitle()->prepend('Behavior Rules');
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('New Rule'));
        return $resultPage;
    }
}
