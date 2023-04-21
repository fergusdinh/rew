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

namespace Lof\RewardPoints\Controller\Adminhtml\Transaction\Customer;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportTransactionXml extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->_fileFactory = $fileFactory;
    }

    /**
     * Export abandoned carts report to Excel XML format
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $fileName = "lof_rewardpoints_customer{$id}_transaction_history.xml";
        $content = $this->_view->getLayout()->createBlock(
            'Lof\RewardPoints\Block\Adminhtml\Customer\Grid'
        )->setCustomerId($id)
        ->getExcelFile(
            $fileName
        );
        return $this->_fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
