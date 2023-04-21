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

namespace Lof\RewardPoints\Controller\Adminhtml\Earning\Product;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
    	\Magento\Backend\App\Action\Context $context,
    	\Magento\Framework\App\ResourceConnection $resource
    	) {
    	parent::__construct($context);
    	$this->_resource = $resource;
    }

    public function execute()
    {
    	$post = $this->getRequest()->getPostValue();
        if($post) {
            try{
                $connection = $this->_resource->getConnection();
                $table      = $this->_resource->getTableName('lof_rewardpoints_product_earning_points');
                $productIds = explode(",", $post['productids']);
                $where      = ['store_id = (?)' => (int)$post['store_id'], 'product_id IN (?)' => $productIds];
                $connection->delete($table, $where);
                if (is_array($productIds) && (float)$post['points'] > 0) {
                    $data = [];
                    foreach ($productIds as $k => $v) {
                        if (!$v) continue;
                        $data[] = [
                            'product_id' => $v,
                            'points'     => $post['points'],
                            'store_id'   => $post['store_id']
                        ];
                    }
                    if ($data) {
                        $connection->insertMultiple($table, $data);
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while change product points.'));
            }
        }
        $this->_redirect('rewardpoints/earning_product/index/store/' . (int) $post['store_id'] );
        return;
    }
}
