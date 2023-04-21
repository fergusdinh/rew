<?php

namespace Lof\RewardPoints\Controller\Adminhtml\Redeem\Import;

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
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Lof_RewardPoints::redeemcode_import')
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'))
        ->addBreadcrumb(__('Reward Points'), __('Reward Points'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Code'));

        return $resultPage;
    }
}
