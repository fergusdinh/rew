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

namespace Lof\RewardPoints\Block\Adminhtml\Order\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Totals extends \Magento\Sales\Block\Adminhtml\Order\Create\Totals
{

    /**
     * @param \Magento\Backend\Block\Template\Context $context         
     * @param \Magento\Backend\Model\Session\Quote    $sessionQuote    
     * @param \Magento\Sales\Model\AdminOrder\Create  $orderCreate     
     * @param PriceCurrencyInterface                  $priceCurrency   
     * @param \Magento\Sales\Helper\Data              $salesData       
     * @param \Magento\Sales\Model\Config             $salesConfig     
     * @param \Lof\RewardPoints\Helper\Customer       $rewardsCustomer 
     * @param array                                   $data            
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Helper\Data $salesData,
        \Magento\Sales\Model\Config $salesConfig,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        array $data = []
    ) {
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig);
        $this->rewardsCustomer = $rewardsCustomer;
    }

	public function _toHtml()
    {
    	$quote = $this->getQuote();
    	$usePointsBlock = $this->getLayout()->createBlock('\Lof\RewardPoints\Block\Adminhtml\Sales\Order\Usepoints')->setQuote($quote);
    	$html = $usePointsBlock->setQuote($quote)->toHtml();
    	$html .= parent::_toHtml();
    	return $html;
    }
}