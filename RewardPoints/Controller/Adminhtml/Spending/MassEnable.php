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

use Magento\Framework\Controller\ResultFactory;

class MassDisable extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context                              $context
     * @param \Magento\Ui\Component\MassAction\Filter                          $filter
     * @param \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $collectionFactory
     * @param \Lof\RewardPoints\Helper\Balance\Earn                            $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Data                                    $rewardsData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $collectionFactory,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
        $this->rewardsBalanceEarn = $rewardsBalanceEarn;
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
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $data = $this->getRequest()->getParams();
        if (isset($data['selected'])) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('rule_id', ['in' => $data['selected']]);
        }

        foreach ($collection as $rule) {
            $rule->setIsActive(true);//is_active
            $rule->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collection->count()));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Lof_RewardPoints::spending_save');
    }
}
