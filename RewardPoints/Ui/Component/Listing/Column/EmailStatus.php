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

class EmailStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Lof\RewardPoints\Helper\Balance\Spend
     */
    protected $rewardsBalanceSpend;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context             
     * @param \Magento\Framework\View\Element\UiComponentFactory           $uiComponentFactory  
     * @param \Lof\RewardPoints\Helper\Balance\Spend                       $rewardsBalanceSpend 
     * @param array                                                        $components          
     * @param array                                                        $data                
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->rewardsBalanceSpend = $rewardsBalanceSpend;
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
                if (isset($item['email_id'])) {
                    $item[$fieldName . '_html'] = '<span class="lrw-status lrw-status-' . $item['status'] . '">' . ucfirst($item['status']) . '</span>';
                }
            }
        }
        return $dataSource;
    }
}
