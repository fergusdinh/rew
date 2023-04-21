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

use Magento\Framework\Controller\ResultFactory;
use Lof\RewardPoints\Model\Earning;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context                             $context           
     * @param \Magento\Ui\Component\MassAction\Filter                         $filter            
     * @param \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory $collectionFactory       
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lof\RewardPointsRule\Model\ResourceModel\Earning\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter             = $filter;
        $this->collectionFactory  = $collectionFactory;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $type = "";
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $data = $this->getRequest()->getParams();
        $i = 0;
        if (isset($data['selected'])) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('rule_id', ['in' => $data['selected']]);
        }
        foreach ($collection as $rule) {
            if(!$type){
                $type = $rule->getType();
            }
            $rule->delete();
            $i++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $i));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
       
        return $resultRedirect->setPath('rewardpointsrule/earning/index/type/' . $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::earningrule_delete');
    }
}
