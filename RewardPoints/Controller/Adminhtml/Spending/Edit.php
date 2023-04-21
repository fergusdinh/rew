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

namespace Lof\RewardPoints\Controller\Adminhtml\Spending;

use Lof\RewardPoints\Model\Spending;

class Edit extends \Lof\RewardPoints\Controller\Adminhtml\Spending
{

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsData;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

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
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->coreRegistry      = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardsData       = $rewardsData;
    }

    /**
     * Edit Spending Rule
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id      = $this->getRequest()->getParam('rule_id');
        $model   = $this->_objectManager->create('Lof\RewardPoints\Model\Spending');
        $storeId = $this->getRequest()->getParam('store');


        // 1. Get rule by object id
        if($id && $storeId) {
            $id = $this->rewardsData->setType(Spending::TYPE)->getRuleInAdmin($id, $storeId, false)->getId();
        }

        if(!$id) {
            $model->setType(Spending::TYPE);
        }

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This rate no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->coreRegistry->register('spending_rate', $model);


        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Rule') : __('New Rule'),
            $id ? __('Edit Rule') : __('New Rule')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('Spending Rate'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getName() : __('Spending Rate'));

        return $resultPage;
    }
}
