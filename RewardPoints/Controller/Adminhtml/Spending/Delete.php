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

class Delete extends \Lof\RewardPoints\Controller\Adminhtml\Spending
{
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Lof\RewardPoints\Helper\Data       $rewardsData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->rewardsData = $rewardsData;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('rule_id');
        $ruleCollection = $this->rewardsData->setType(Spending::TYPE)->getAllRule($id);

        if ($id) {
            try {
                foreach ($ruleCollection as $_rule) {
                    $_rule->delete();
                }
                $model = $this->_objectManager->create('Lof\RewardPoints\Model\Spending');
                $model->load($id);
                $model->delete();

                // display success message
                $this->messageManager->addSuccess(__('You deleted the rate.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['rule_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a rate to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
