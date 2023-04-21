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

namespace Lof\RewardPoints\Ui\Component;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{

    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        $store = $this->request->getParam('store');
        if (isset($this->data['config']['update_url']) && $store) {
            $this->data['config']['update_url'] = $this->data['config']['update_url'] . 'store/' . $store;
        }
        $type = $this->request->getParam('type');
        if (isset($this->data['config']['update_url']) && $type) {
            if ($store) {
                $this->data['config']['update_url'] = $this->data['config']['update_url'] . '/';
            }
            $this->data['config']['update_url'] = $this->data['config']['update_url'] . 'type/' . $type;
        }

        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
        }
    }
}
