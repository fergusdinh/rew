<?php
/**
 * LandofCoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://venustheme.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2016 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Controller\Adminhtml\Import;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Lof\RewardPoints\Helper\Data $rewardsData
        ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->rewardsData       = $rewardsData;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Lof_RewardPoints::earning')
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'))
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Customer points'));

        return $resultPage;
    }
}
