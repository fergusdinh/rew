<?php

namespace Lof\RewardPoints\Block\Adminhtml\RedeemCode;


class Generate extends \Magento\Backend\Block\Widget\Container
{

    protected $_template = 'Lof_RewardPoints::redeem/grid/container.phtml';

    protected function _construct()
    {
        $this->_blockGroup = 'Lof_RewardPoints';
        // $this->_controller = 'adminhtml_generate';
        $this->_headerText = __('Redeem Code Generate');
        parent::_construct();
    }
    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/redeem/generate', ['_current' => true]);
    }
}
