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
 * @package    Lof_RewardPointsRule
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsRule\Observer;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPointsRule\Model\Config;

class AddToCart implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

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
     * @var \Lof\RewardPointsRule\Model\Config
     */
    protected $rewardsRuleConfig;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager    
     * @param \Lof\RewardPoints\Helper\Purchase           $rewardsPurchase   
     * @param \Lof\RewardPoints\Helper\Customer           $rewardsCustomer   
     * @param \Lof\RewardPoints\Logger\Logger             $rewardsLogger     
     * @param \Lof\RewardPointsRule\Model\Config          $rewardsRuleConfig 
     */
	public function __construct(
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
		\Lof\RewardPoints\Helper\Customer $rewardsCustomer,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPointsRule\Model\Config $rewardsRuleConfig
	) {
        $this->messageManager    = $messageManager;
        $this->rewardsPurchase   = $rewardsPurchase;
        $this->rewardsCustomer   = $rewardsCustomer;
        $this->rewardsLogger     = $rewardsLogger;
        $this->rewardsRuleConfig = $rewardsRuleConfig;
	}

	/**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
	public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->rewardsRuleConfig->isEnable()) {
            try{
                $customer        = $observer->getCustomer();
                $availablePoints = $customer->getAvailablePoints();
                $customerPoints  = $customer->getAvailablePoints();
                $params          = $observer->getParams();
                $quote           = $observer->getQuote();
                $result          = $observer->getResult();
                $collection      = $observer->getCollection();
                $product         = $observer->getProduct();
                $ruleId          = $params['rule'];
                $purchase        = $this->rewardsPurchase->getByQuote($quote);
                $ruleParams      = $purchase->getParams();

                $spendingRules = [];
                if (isset($ruleParams[Config::SPENDING_CATALOG_RULE]['rules'])) {
                    $spendingRules = $ruleParams[Config::SPENDING_CATALOG_RULE];
                }
                $sku        = strtolower($result->getSku());
                $isProgress = false;

                foreach ($collection as $_item) {
                    if(strtolower($_item->getSku()) === $sku  && $product->getId() == $_item->getProductId()) {
                        $qty = $params['qty'];

                        if(($params['spendpoints'] * $qty) > $availablePoints) {
                            $qty = (int) ($availablePoints / $params['spendpoints']);
                            $isProgress = true;
                        }

                        if($qty) {
                            // Updated, Add new item in rule
                            $availablePoints -= ($params['spendpoints'] * $qty);
                            $result->setQty($result->getQty() - ($params['qty'] - $qty));

                            $newQty = $newSteps = 0;
                            if(isset($spendingRules['rules'][$ruleId]['items'][$sku])) {
                                $spend = $spendingRules['rules'][$ruleId]['items'][$sku];
                                $newQty      = $qty + $spend['qty'];
                                $newSteps    = (int) $spend['steps'] + (($params['spendpoints'] / $params['points']) * $params['qty']);
                            } else {
                                $newQty      = $qty;
                                $newSteps    = (int) (($params['spendpoints'] / $params['points']) * $qty);
                            }

                            $spendingRules['rules'][$ruleId]['items'][$sku] = [
                                'qty'      => $newQty,
                                'discount' => (int) $params['discount'],
                                'steps'    => (int) $newSteps,
                                'points'   => (int) $params['points']
                            ];
                        }
                        break;
                    }
                }

                // Not Enough points to buy a product
                if ($availablePoints == $customerPoints) {
                    $quote->setHasError(true);
                }

                if ($isProgress) {
                    // Reset Quote Item Qty
                    $result->setQty($result->getQty() - $params['qty']);
                    $this->messageManager->addError(__('Not enough points to buy %1 products', $params['qty']));
                }

                if ($availablePoints < $customerPoints) {
                    // Save Customer Available Points
                    $customer->refreshPoints()->save();
                    $ruleParams[Config::SPENDING_CATALOG_RULE] = $spendingRules; 
                    $purchase->setParams($ruleParams);
                    $purchase->refreshPoints();
                    $purchase->save();
                }
            } catch (\Exception $e) {
                $this->rewardsLogger->addError($e->getMessage());
            }
        }
	}
}