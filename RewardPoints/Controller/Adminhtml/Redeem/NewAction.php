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

namespace Lof\RewardPoints\Controller\Adminhtml\Redeem;

class NewAction extends \Lof\RewardPoints\Controller\Adminhtml\Redeem
{

    protected $resultPageFactory;

    protected $dateTime;

    protected $rewardsData;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        // \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->dateTime          = $dateTime;
        $this->rewardsData       = $rewardsData;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('Add New Redeem Code'));
        $localDate = $this->rewardsData->formatDate($this->dateTime->gmtDate(), \IntlDateFormatter::LONG);
        $currentTime = $this->dateTime->gmtDate('h:m:s A');
        $this->messageManager->addNotice(__('Local time: %1 at %2', $localDate, $currentTime));

        return $resultPage;
    }

    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Lof_RewardPoints::redeemcode')
        ->addBreadcrumb(__('Add New Redeem Code'), __('Add New Redeem Code'))
        ->addBreadcrumb(__('Add New Redeem Code'), __('Add New Redeem Code'));
        return $resultPage;
    }
}
