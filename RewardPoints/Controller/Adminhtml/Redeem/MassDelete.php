<?php


namespace Lof\RewardPoints\Controller\Adminhtml\Redeem;

use Magento\Framework\Controller\ResultFactory;
use Lof\RewardPoints\Model\Redeem;

class MassDelete extends \Magento\Backend\App\Action
{

    protected $filter;

    protected $collectionFactory;

    protected $rewardsData;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Lof\RewardPoints\Model\ResourceModel\Redeem\CollectionFactory $collectionFactory,
        \Lof\RewardPoints\Helper\Data $rewardsData
    ) {
        parent::__construct($context);
        $this->filter             = $filter;
        $this->collectionFactory  = $collectionFactory;
        $this->rewardsData        = $rewardsData;
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $data = $this->getRequest()->getParams();

        if (isset($data['selected'])) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('code_id', ['in' => $data['selected']]);
        }elseif (isset($data["namespace"]) && $data["namespace"] == "rewardpoints_redeemcode_listing") {
            $collection = $this->collectionFactory->create();
        }
        foreach ($collection as $rule) {
            $rule->delete();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collection->count()));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/new');
    }
}
