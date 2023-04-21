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

class EarningPointLabel extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param ContextInterface                          $context
     * @param UiComponentFactory                        $uiComponentFactory
     * @param array                                     $components
     * @param array                                     $data
     * @param \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->rewardsBalanceEarn = $rewardsBalanceEarn;
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
                if (isset($item['rule_id'])) {
                    $store_id = isset($item['store_id'])?(int)$item['store_id']:0;
                    $item[$fieldName . '_html'] = $this->rewardsBalanceEarn->getPointLabel($item, $store_id);
                }
            }
        }
        return $dataSource;
    }
}
