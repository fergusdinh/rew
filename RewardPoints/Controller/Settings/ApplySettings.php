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

namespace Lof\RewardPoints\Controller\Settings;

class Applysettings extends \Lof\RewardPoints\Controller\AbstractIndex
{
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    protected $rewardsCustomer;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Lof\RewardPoints\Helper\Customer     $rewardsCustomer
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Lof\RewardPoints\Helper\Customer $rewardsCustomer
    ) {
        parent::__construct($context);
        $this->rewardsCustomer = $rewardsCustomer;
    }

    public function execute()
    {
        $post     = $this->getRequest()->getPost();
        $customer = $this->rewardsCustomer->getCustomer();
        if ($customer && $customer->getId() && (isset($post['update_point_notification']) || $post['expire_point_notification'])) {
            if ($post['update_point_notification']) {
                $customer->setData('update_point_notification', 1);
            } else {
                $customer->setData('update_point_notification', 0);
            }
            if ($post['expire_point_notification']) {
                $customer->setData('expire_point_notification', 1);
            } else {
                $customer->setData('expire_point_notification', 0);
            }
            $customer->save();
            $this->messageManager->addSuccess(__('Your settings has been updated successfully.'));
        }

        $this->_redirect('*/settings');
        return;
    }
}
