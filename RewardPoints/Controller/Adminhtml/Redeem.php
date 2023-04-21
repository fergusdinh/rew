<?php

namespace Lof\RewardPoints\Controller\Adminhtml;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;


abstract class Redeem extends \Magento\Backend\App\Action
{ 
    const ADMIN_RESOURCE = 'Lof_RewardPoints::redeemcode';

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Lof_RewardPoints::redeemcode')
        ->addBreadcrumb(__('Redeem Code'), __('Redeem Code'))
        ->addBreadcrumb(__('Redeem Code'), __('Redeem Code'));
        return $resultPage;
    }
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::redeemcode');
    }

}
