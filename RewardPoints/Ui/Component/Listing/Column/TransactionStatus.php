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

namespace Lof\RewardPoints\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder;
use Magento\Framework\UrlInterface;


class TransactionStatus extends Column
{
    /**
     * @var \Lof\RewardPoints\Model\Transaction
     */
    protected $rewardsTransaction;

    /**
     * @param ContextInterface                    $context            
     * @param UiComponentFactory                  $uiComponentFactory 
     * @param \Lof\RewardPoints\Model\Transaction $rewardsTransaction 
     * @param array                               $components         
     * @param array                               $data               
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Lof\RewardPoints\Model\Transaction $rewardsTransaction,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->rewardsTransaction = $rewardsTransaction;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $fieldName = $this->getData('name');
                if (isset($item['transaction_id'])) {
                    $status = $this->rewardsTransaction->getStatusLabel($item['status']);
                    $item[$fieldName . '_html'] = '<span class="lrw-status lrw-status-' . $item['status'] . '">' .$status . '</span>';
                }
            }
        }
        return $dataSource;
    }
}
