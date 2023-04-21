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

namespace Lof\RewardPoints\Block\Checkout\Cart;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

class Message extends \Magento\Framework\View\Element\Messages
{
	/**
	 * @var \Lof\RewardPoints\Helper\Purchase
	 */
	protected $rewardsPurchase;

    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    protected $rewardsData;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                        $context
     * @param \Magento\Framework\Message\Factory                                      $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory                            $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface                             $messageManager
     * @param \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy
     * @param \Lof\RewardPoints\Helper\Purchase                                       $rewardsPurchase
     * @param \Lof\RewardPoints\Helper\Data                                           $rewardsData
     * @param array                                                                   $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Element\Message\InterpretationStrategyInterface $interpretationStrategy,
        \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
        \Lof\RewardPoints\Helper\Data $rewardsData,
        array $data = []
    ) {
        parent::__construct($context, $messageFactory, $collectionFactory, $messageManager, $interpretationStrategy, $data);
        $this->rewardsPurchase = $rewardsPurchase;
        $this->rewardsData     = $rewardsData;
    }

    protected function _prepareLayout()
    {
        $points = 0;
        $purchase = $this->rewardsPurchase->getPurchase();
        if($purchase) {
            $points = (float) $purchase->getEarnPoints();
            if ($points) {
                $this->messageManager->addSuccess(__('Checkout now and earn <strong>%1</strong> for this order', $this->rewardsData->formatPoints($points)));
            }
        }
        return parent::_prepareLayout();
    }
}
