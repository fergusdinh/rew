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

namespace Lof\RewardPoints\Observer;

use Magento\Framework\Event\ObserverInterface;
use Lof\RewardPoints\Model\Config;

class CustomerLogin implements ObserverInterface
{
	/**
	 * @var \Magento\Quote\Api\CartRepositoryInterface
	 */
	protected $quoteRepository;

	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

	/**
	 * @var \Lof\RewardPoints\Helper\Customer
	 */
	protected $rewardsCustomer;

	/**
	 * @var \Lof\RewardPoints\Helper\Balance
	 */
	protected $rewardsBalance;

	/**
	 * @var \Lof\RewardPoints\Logger\Logger
	 */
	protected $rewardsLogger;

	/**
	 * @var \Lof\RewardPoints\Model\Config
	 */
	protected $rewardsConfig;

	/**
	 * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
	 * @param \Lof\RewardPoints\Helper\Purchase          $rewardsPurchase
	 * @param \Lof\RewardPoints\Helper\Customer          $rewardsCustomer
	 * @param \Lof\RewardPoints\Helper\Balance           $rewardsBalance
	 * @param \Lof\RewardPoints\Logger\Logger            $rewardsLogger
	 * @param \Lof\RewardPoints\Model\Config             $rewardsConfig
	 */
	public function __construct(
		\Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
		\Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
		\Lof\RewardPoints\Helper\Customer $rewardsCustomer,
		\Lof\RewardPoints\Helper\Balance $rewardsBalance,
		\Lof\RewardPoints\Logger\Logger $rewardsLogger,
		\Lof\RewardPoints\Model\Config $rewardsConfig
	) {
		$this->quoteRepository = $quoteRepository;
		$this->rewardsPurchase = $rewardsPurchase;
		$this->rewardsCustomer = $rewardsCustomer;
		$this->rewardsBalance  = $rewardsBalance;
		$this->rewardsLogger   = $rewardsLogger;
		$this->rewardsConfig   = $rewardsConfig;
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
		if ($customerId = $observer->getCustomer()->getId() && $this->rewardsConfig->isEnable()){
			try {
				$this->rewardsBalance->proccessTransaction();
				$quote    = $this->quoteRepository->getForCustomer($customerId);
				$purchase = $this->rewardsPurchase->getByQuote($quote);
				$customer = $this->rewardsCustomer->getCustomer($customerId);
				$this->rewardsCustomer->refreshPurchaseAvailable($purchase->getId(), $customerId);

				$params = $customer->getParams();
				if (is_array($params)) {
					foreach ($params as $quoteId => $points) {
						if ($quoteId != $quote->getId()) {
							unset($params[$quoteId]);
						}
					}
				}
				$customer->setParams($params)->refreshPoints()->save();
			} catch (\Exception $e) {
				$this->rewardsLogger->addError($e->getMessage());
			}
		}
	}
}
