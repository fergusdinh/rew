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

namespace Lof\RewardPoints\Controller\Adminhtml\Spending\Product;

use Lof\RewardPoints\Model\Spending\Rule;
use Lof\RewardPoints\Model\Config;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Lof\RewardPoints\Helper\Balance\Earn
     */
    protected $rewardsBalanceEarn;

    /**
     * @param \Magento\Backend\App\Action\Context       $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Lof\RewardPoints\Helper\Balance\Earn     $rewardsBalanceEarn
     */
    public function __construct(
    	\Magento\Backend\App\Action\Context $context,
    	\Magento\Framework\App\ResourceConnection $resource,
        \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn
    	) {
    	parent::__construct($context);
        $this->resource           = $resource;
        $this->rewardsBalanceEarn = $rewardsBalanceEarn;
    }

    public function execute()
    {
    	$post = $this->getRequest()->getPostValue();
    	if($post) {
    		try{
                $connection = $this->resource->getConnection();
                $table      = $this->resource->getTableName('lof_rewardpoints_product_spending_points');
                $productIds = explode(",", $post['productids']);
    			$where      = ['store_id = (?)' => (int)$post['store_id'], 'product_id IN (?)' => $productIds];
            	$connection->delete($table, $where);
                $productCollection = $this->rewardsBalanceEarn->getProductCollection();
                $productCollection->addFieldToFilter('entity_id', ['in' => $productIds]);

                if (is_array($productIds) && (float)$post['points'] > 0) {
        			$data = [];
        			foreach ($productIds as $k => $productId) {
                        if (!$productId) continue;
                        $sku = '';
                        foreach ($productCollection as $_product) {
                            if ($_product->getId() == $productId) {
                                $sku = strtolower($_product->getSku());
                            }
                        }
        				$data[] = [
    	    				'product_id' => $productId,
    	    				'points'     => $post['points'],
    	    				'store_id'   => $post['store_id'],
                            'sku'        => $sku
        				];
        			}
        			$connection->insertMultiple($table, $data);
                }
    		} catch (\Exception $e) {
    			$this->messageManager->addException($e, __('Something went wrong while changing product points.'));
    		}
    	}
    	$this->_redirect(Config::ROUTES . '/spending_product/index/store/' . (int) $post['store_id'] );
    	return;
    }
}
