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

namespace Lof\RewardPoints\Block\Account\Dashboard;

use \Lof\RewardPoints\Model\Config;
use Lof\RewardPointsBehavior\Model\ResourceModel\Refer\CollectionFactory as ReferCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class Refer extends Summary
{
    protected $collectionFactory;
    protected $_storeManager;
   public function __construct(\Magento\Catalog\Block\Product\Context $context,
                               \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
                               \Lof\RewardPoints\Model\Config $rewardsConfig,
                               \Lof\RewardPoints\Helper\Balance\Earn $rewardsBalanceEarn,
                               \Lof\RewardPoints\Helper\Balance\Spend $rewardsBalanceSpend,
                               \Magento\Customer\Model\Session $customerSession,
                               ReferCollectionFactory $collectionFactory,
                               StoreManagerInterface $storeManager,
                               \Magento\Framework\Data\Form\FormKey $formKey,
                               array $data = [])
   {
       parent::__construct($context, $rewardsCustomer, $rewardsConfig, $rewardsBalanceEarn, $rewardsBalanceSpend, $customerSession, $data);
       $this->collectionFactory = $collectionFactory;
       $this->_storeManager = $storeManager;
       $this->formKey = $formKey;
   }
   public function getListReferredCustomer()
   {
        $customerId = $this->customerSession->getId();
        return $refer = $this->collectionFactory->create()->addFieldToFilter('customer_refer_id',$customerId);

   }
   public function getShareLinkUrl()
   {
       return $this->getUrl('rewardpoints/refer/sharelink');
   }
   public function getFormKey()
   {
       return $this->formKey->getFormKey();
   }
}