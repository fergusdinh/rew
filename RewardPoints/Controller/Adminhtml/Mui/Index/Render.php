<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
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

namespace Lof\RewardPoints\Controller\Adminhtml\Mui\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Model\UiComponentTypeResolver;
use Psr\Log\LoggerInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Controller\Result\JsonFactory;

class Render extends \Magento\Ui\Controller\Adminhtml\Index\Render
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param UiComponentTypeResolver $contentTypeResolver
     * @param JsonFactory|null $resultJsonFactory
     * @param Escaper|null $escaper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
    	Context $context,
    	UiComponentFactory $factory,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        UiComponentTypeResolver $contentTypeResolver,
        JsonFactory $resultJsonFactory = null,
        Escaper $escaper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($context, $factory, $contentTypeResolver, $resultJsonFactory, $escaper, $logger );
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
    	$store = $this->getRequest()->getParam('store');
    	$this->coreRegistry->register('current_store', $store);
        $type = $this->getRequest()->getParam('type');
        $this->coreRegistry->register('current_type', $type);
    	parent::execute();
    }
}
