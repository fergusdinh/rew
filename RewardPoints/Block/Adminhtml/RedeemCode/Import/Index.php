<?php

namespace Lof\RewardPoints\Block\Adminhtml\RedeemCode\Import;

class Index extends \Magento\Backend\Block\Widget\Form\Container
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
    	$this->_controller = 'adminhtml_redeemCode_import';
    	$this->updateButton('save', 'label', __('Import CSV file'));

        $this->buttonList->remove('delete');
    }

    public function getHeaderText()
    {
        return __('Import Code Via CSV');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
   
}
