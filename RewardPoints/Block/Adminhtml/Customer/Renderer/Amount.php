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

namespace Lof\RewardPoints\Block\Adminhtml\Customer\Renderer;

class Amount extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Context                                      $context
     * @param \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory $transactionCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * Renders item product name and its configuration
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface|\Magento\Framework\DataObject $item
     * @return string
     */
    public function render(\Magento\Framework\DataObject $item)
    {
    	$transactions = $this->transactionCollectionFactory->create()
        ->addFieldToFilter('customer_id', $item->getId())
        ->addFieldToFilter('status', \Lof\RewardPoints\Model\Transaction::STATE_COMPLETE);
    	$total = 0;
    	foreach ($transactions as $transaction) {
    		$total += $transaction->getAmount();
    	}
        $total = '<span class="lrw-available-points">' . $total . '</span>';
    	return $total;
    }
}
