<?php
/**
 * LandofCoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   LandofCoder
 * @package    Lof_CouponCode
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\RewardPoints\Block\Adminhtml\Import;

/**
 * Brand edit block
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

	protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct(){
        parent::_construct();
        
    	$this->_blockGroup = 'Lof_RewardPoints';
    	$this->_controller = 'adminhtml_import';
    	$this->updateButton('save', 'label', __('Import CSV file'));

        $this->buttonList->remove('delete');
    }

    public function getHeaderText()
    {
        return __('Import Customer Points Via CSV');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
   
}
