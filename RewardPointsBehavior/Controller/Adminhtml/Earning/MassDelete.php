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
     * @var \Lof\RewardPointsBehavior\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context                             $context           
     * @param \Magento\Ui\Component\MassAction\Filter                         $filter            
     * @param \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory $collectionFactory 
     * @param \Lof\RewardPointsBehavior\Helper\Data                                   $rewardsData       
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lof\RewardPointsBehavior\Model\ResourceModel\Earning\CollectionFactory $collectionFactory,
        \Lof\RewardPointsBehavior\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->filter             = $filter;
        $this->collectionFactory  = $collectionFactory;
        $this->rewardsData        = $rewardsData;
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    { 
        $type = $type = \Lof\RewardPointsBehavior\Model\Earning::BEHAVIOR;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $i = 0;
        if (isset($data['selected'])) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('rule_id', ['in' => $data['selected']]);
        }

        foreach ($collection as $rule) {
            if($type == $rule->getType()){
                $rule->delete();
                $i++;
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $i));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');

        return $resultRedirect->setPath('rewardpointsbehavior/earning/index/type/' . $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPointsBehavior::earningrule_delete');
    }
}
