<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the landofcoder.com license that is
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

namespace Lof\RewardPoints\Block\Adminhtml\Widget\Form\Element;

class Dependence extends \Magento\Backend\Block\Widget\Form\Element\Dependence
{
    /**
     * HTML output getter
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_depends) {
            return '';
        }
        return '<script>
            require(["mage/adminhtml/form"], function(){
        new FormElementDependenceController(' .
            $this->_getDependsJson() .
            ($this->_configOptions ? ', ' .
            $this->_jsonEncoder->encode(
                $this->_configOptions
            ) : '') . '); });</script>';
    }
}
