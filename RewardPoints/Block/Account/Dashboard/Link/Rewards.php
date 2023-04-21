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

namespace Lof\RewardPoints\Block\Account\Dashboard\Link;

class Rewards extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context         
     * @param \Magento\Framework\App\DefaultPathInterface      $defaultPath     
     * @param \Lof\RewardPoints\Helper\Customer                $rewardsCustomer 
     * @param \Lof\RewardPoints\Helper\Data                    $rewardsData     
     * @param array                                            $data            
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        array $data = []
        ) {
        parent::__construct($context, $defaultPath);
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsData     = $rewardsData;
    }


    /**
     * Retrieve the Customer Data using the customer Id from the customer session.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        $customer = $this->rewardsCustomer->getCustomer();
        return $customer;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (false != $this->getTemplate()) {
            return parent::_toHtml();
        }

        $html        = '';
        $customer    = $this->getCustomer();

        if (!$customer) {
            return;
        }

        $totalPoints = '<span>(</span>' . $this->rewardsData->formatPoints($customer->getTotalPoints(), true, true) . '<span>)</span>';
        $highlight   = '';

        if ($this->getIsHighlighted()) {
            $highlight = ' current';
        }

        if ($this->isCurrent()) {
            $html = '<li class="nav item current lrw-nav-item">';
            $html .= '<strong>'
            . '<span>' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel())) . '</span>';
            $html .= ' ' . $totalPoints;
            $html .= '</strong>';
            $html .= '</li>';
        } else {
            $html = '<li class="nav item' . $highlight . ' lrw-nav-item"><a href="' . $this->escapeHtml($this->getHref()) . '"';
            $html .= $this->getTitle()
            ? ' title="' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getTitle())) . '"'
            : '';
            $html .= $this->getAttributesHtml() . '>';

            if ($this->getIsHighlighted()) {
                $html .= '<strong>';
            }

            $html .= '<span>' . $this->escapeHtml((string)new \Magento\Framework\Phrase($this->getLabel())) . '</span>';

            if ($this->getIsHighlighted()) {
                $html .= '</strong>';
            }
            $html .= ' ' . $totalPoints;
            $html .= '</a></li>';
        }

        return $html;
    }
}