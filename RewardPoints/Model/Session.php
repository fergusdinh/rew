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

namespace Lof\RewardPoints\Model;

use Magento\Checkout\Model\Session as SessionQuote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;

class Session extends SessionQuote
{
    /**
     * A flag to track when the quote is being loaded and attached to the session object.
     *
     * Used in trigger_recollect infinite loop detection.
     *
     * @var bool
     */
    private $isLoading = false;

    /**
     * Get checkout current quote instance by current session
     *
     * @return Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getCurrentQuote()
    {
        if ($this->_quote) {
            return $this->_quote;
        }
        $this->_eventManager->dispatch('custom_quote_process', ['checkout_session' => $this]);

        if ($this->_quote === null) {
            if ($this->isLoading) {
                throw new \LogicException("Infinite loop detected, review the trace for the looping path");
            }
            $this->isLoading = true;
            $quote = $this->quoteFactory->create();
            if ($this->getQuoteId()) {
                try {
                    if ($this->_loadInactive) {
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    } else {
                        $quote = $this->quoteRepository->getActive($this->getQuoteId());
                    }

                    $customerId = $this->_customer
                        ? $this->_customer->getId()
                        : $this->_customerSession->getCustomerId();

                    if ($quote->getData('customer_id') && (int)$quote->getData('customer_id') !== (int)$customerId) {
                        $quote = $this->quoteFactory->create();
                        $this->setQuoteId(null);
                    }

                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != $this->_storeManager->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore($this->_storeManager->getStore());
                        $this->quoteRepository->save($quote->collectTotals());
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = $this->quoteRepository->get($this->getQuoteId());
                    }
                    /**
                     * By commenting $quote->collectTotals();
                     * or whole if condition it will not go in infinite and code will work properly.
                     * Actually there is no need of $quote->collectTotals(); in this file.
                     */
//                    if ($quote->getTotalsCollectedFlag() === false) {
//                        $quote->collectTotals();
//                    }
                } catch (NoSuchEntityException $e) {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $quoteByCustomer = $this->getQuoteByCustomer();
                    if ($quoteByCustomer !== null) {
                        $this->setQuoteId($quoteByCustomer->getId());
                        $quote = $quoteByCustomer;
                    }
                } else {
                    $quote->setIsCheckoutCart(true);
                    $quote->setCustomerIsGuest(1);
                    $this->_eventManager->dispatch('checkout_quote_init', ['quote' => $quote]);
                }
            }

            if ($this->_customer) {
                $quote->setCustomer($this->_customer);
            } elseif ($this->_customerSession->isLoggedIn()) {
                $quote->setCustomer($this->customerRepository->getById($this->_customerSession->getCustomerId()));
            }

            $quote->setStore($this->_storeManager->getStore());
            $this->_quote = $quote;
            $this->isLoading = false;
        }

        if (!$this->isQuoteMasked() && !$this->_customerSession->isLoggedIn() && $this->getQuoteId()) {
            $quoteId = $this->getQuoteId();
            /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
            if ($quoteIdMask->getMaskedId() === null) {
                $quoteIdMask->setQuoteId($quoteId)->save();
            }
            $this->setIsQuoteMasked(true);
        }

        $remoteAddress = $this->_remoteAddress->getRemoteAddress();
        if ($remoteAddress) {
            $this->_quote->setRemoteIp($remoteAddress);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }

        return $this->_quote;
    }
}
