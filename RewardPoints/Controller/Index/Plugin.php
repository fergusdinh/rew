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

namespace Lof\RewardPoints\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;

class Plugin
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Wishlist\Model\AuthenticationStateInterface
     */
    protected $authenticationState;

    /**
     * @param CustomerSession $customerSession
     * @param \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Wishlist\Model\AuthenticationStateInterface $authenticationState
    ) {
        $this->customerSession     = $customerSession;
        $this->authenticationState = $authenticationState;
    }

    /**
     * Perform customer authentication and wishlist feature state checks
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        if ($this->authenticationState->isEnabled() && !$this->customerSession->authenticate()) {
            $subject->getActionFlag()->set('', 'no-dispatch', true);
            $this->customerSession->setBeforeModuleName('rewardpoints');
            $this->customerSession->setBeforeControllerName('account');
            $this->customerSession->setBeforeAction('index');
        }
    }
}
