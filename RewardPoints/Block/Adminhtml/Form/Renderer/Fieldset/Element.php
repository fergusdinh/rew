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

namespace Lof\RewardPoints\Block\Adminhtml\Form\Renderer\Fieldset;

class Element extends \Magento\Catalog\Block\Adminhtml\Form\Renderer\Fieldset\Element
{
    /**
     * Retrieve label of attribute scope
     *
     * GLOBAL | WEBSITE | STORE
     *
     * @return string
     */
    public function getScopeLabel()
    {
        $html = __('[STORE VIEW]');
        if ($this->getData('is_scope_global')) {
            $html = __('[GLOBAL]');
        } elseif ($this->getData('is_scope_website')) {
            $html = __('[WEBSITE]');
        }
        return $html;
    }

    public function canDisplayUseDefault(){
        $store = $this->getRequest()->getParam('store');
        if(!$store){
            return false;
        }
        return true;
    }
}
