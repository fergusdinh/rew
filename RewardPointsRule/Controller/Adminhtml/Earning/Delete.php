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

use Lof\RewardPoints\Model\Earning;

class Delete extends \Lof\RewardPoints\Controller\Adminhtml\Earning
{

    /**
     * @param \Magento\Backend\App\Action\Context $context     
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = '';
        $resultRedirect = $this->resultRedirectFactory->create();
        $id             = $this->getRequest()->getParam('rule_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Lof\RewardPointsRule\Model\Earning');
                $model->load($id);
                $type = $model->getType();
                $model->delete();

                // display success message
                $this->messageManager->addSuccess(__('You deleted the rule.'));
                // go to grid
                 return $resultRedirect->setPath('rewardpointsrule/earning/index/type/' . $type);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a rule to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
