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

namespace Lof\RewardPoints\Controller\Checkout;

class Cancelpoints extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Cart          $cart
     * @param \Lof\RewardPoints\Helper\Purchase     $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Customer     $rewardsCustomer
     * @param \Lof\RewardPoints\Logger\Logger       $rewardsLogger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger
    ) {
        parent::__construct($context);
        $this->cart            = $cart;
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsCustomer = $rewardsCustomer;
        $this->rewardsLogger   = $rewardsLogger;
    }

    public function execute()
    {
    	if($post = $this->getRequest()->getParams()){
    		if(isset($post['item_id'])) {
                try{
                    $purchase   = $this->rewardsPurchase->getPurchase();
                    $params     = $purchase->getParams();
                    $this->cart->removeItem($post['item_id'])->save();
                    if (isset($params[$post['type']])) {
                        $params[$post['type']] = [];
                    }
                    $purchase->setParams($params);
                    $purchase->save();

                    /**
                     * Customer Points
                     */
                    $this->rewardsCustomer->getCustomer();

                    $this->messageManager->addSuccess(__('The rule has been successfully removed.'));
                    $this->_redirect($this->_redirect->getRefererUrl());
                    return;

                } catch (\Exception $e) {
                    $this->rewardsLogger->addError($e->getMessage());
                }
            }


        }
    }
}
