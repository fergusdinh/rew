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

use Magento\Framework\Controller\ResultFactory;

class Applypoints extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $rewardsCheckout;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $cart;

    /**
     * @param \Magento\Framework\App\Action\Context              $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator     $formKeyValidator
     * @param \Magento\Quote\Api\CartRepositoryInterface         $quoteRepository
     * @param \Magento\Checkout\Model\Cart                       $cart
     * @param \Lof\RewardPoints\Helper\Checkout                  $rewardsCheckout
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Lof\RewardPoints\Helper\Checkout $rewardsCheckout
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
        $this->rewardsCheckout     = $rewardsCheckout;
        $this->quoteRepository     = $quoteRepository;
        $this->cart                = $cart;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($post && ($response = $this->rewardsCheckout->applyPoints($post))) {
            if (isset($response['ajax'])) {
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($response['ajax'])
                    );
                return;
            }

            /*if (isset($response['status']) && isset($response['message'])) {
                $this->messageManager->addSuccess($response['message']);
                $cartQuote = $this->cart->getQuote();
                $itemsCount = $cartQuote->getItemsCount();
                if ($itemsCount) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $cartQuote->collectTotals();
                    $this->quoteRepository->save($cartQuote);
                }
            }*/
        }
        return $this->_goBack();
    }
}
