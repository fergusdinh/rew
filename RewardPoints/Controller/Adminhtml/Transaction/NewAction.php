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

namespace Lof\RewardPoints\Controller\Adminhtml\Transaction;

class NewAction extends \Lof\RewardPoints\Controller\Adminhtml\Transaction
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context         $context
     * @param \Magento\Framework\Registry                 $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory  $resultPageFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Lof\RewardPoints\Helper\Data               $rewardsData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->dateTime          = $dateTime;
        $this->rewardsData       = $rewardsData;
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Add New Transaction'));
        $localDate = $this->rewardsData->formatDate($this->dateTime->gmtDate(), \IntlDateFormatter::LONG);
        $currentTime = $this->dateTime->gmtDate('h:m:s A');
        $this->messageManager->addNotice(__('Local time: %1 at %2', $localDate, $currentTime));

        return $resultPage;
    }

    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Lof_RewardPoints::transactions')
        ->addBreadcrumb(__('Add New Transaction'), __('Add New Transaction'))
        ->addBreadcrumb(__('Add New Transaction'), __('Add New Transaction'));
        return $resultPage;
    }
}
