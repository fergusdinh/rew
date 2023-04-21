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

namespace Lof\RewardPoints\Controller\Index;

use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

class EarningPoint extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $productFactory;

    protected $storeManager;

    protected $rewardsBalanceEarn;

    protected $helperData;

    /**
     * @param \Magento\Framework\App\Action\Context      $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn
     * @param \Lof\RewardPoints\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
        \Lof\RewardPoints\Helper\Data $helperData
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productFactory = $productFactory;
        $this->_storeManager     = $storeManager;
        $this->rewardsBalanceEarn  = $rewardsBalanceEarn;
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
		$data = $this->getRequest()->getPostValue();
        //Test Data: $data = ["product_id" => 699];
		if (!$data || ($data && !isset($data['product_id']))) {
			$this->_redirect($this->_redirect->getRefererUrl());
			return;
		}
        $json = [];
        $product_id = (int)$data['product_id'];
        if($product_id){
            $product = $this->productFactory->create()
                                ->setStoreId($this->_storeManager->getStore()->getId())
                                ->load($product_id);

            if($product) {
                $earningPoints = (float) $this->rewardsBalanceEarn->getProductPoints($product);
                if($earningPoints > 0){
                    $json["earningPoint"] = $earningPoints;
                    $json["earningPointLabel"] = $this->helperData->formatPoints($earningPoints);
                }
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($json)
            );
        return;
    }
}
