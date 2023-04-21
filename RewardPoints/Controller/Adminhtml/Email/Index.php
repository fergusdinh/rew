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
 * @package    Lof_RewardPoints
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Controller\Adminhtml\Email;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Backend\App\Action\Context         $context
     * @param PageFactory  $resultPageFactory
     * @param DateTime $dateTime
     * @param \Lof\RewardPoints\Helper\Data               $rewardsData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        DateTime $dateTime,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
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
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Lof_RewardPoints::earning')
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'))
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'));
        $resultPage->getConfig()->getTitle()->prepend(__('Mail Logs'));

        $localDate = $this->rewardsData->formatDate($this->dateTime->gmtDate(), \IntlDateFormatter::LONG);
        $currentTime = $this->dateTime->gmtDate('h:m:s A');
        $this->messageManager->addNotice(__('Local time: %1 at %2', $localDate, $currentTime));

        return $resultPage;
    }
}
