<?php
namespace Lof\RewardPoints\Plugin;
use Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Paypal\Model\Api\Nvp;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateDiscountForOrder
 * @package Lof\RewardPoints\Model\Plugin
 */
class UpdateDiscountForOrder
{

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var CollectionFactory
     */
    private $purchaseCollectionFactory;

    /**
     * UpdateDiscountForOrder constructor.
     * @param Quote $quote
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Registry $registry
     * @param CollectionFactory $purchaseCollectionFactory
     */
    public function __construct(
        \Magento\Quote\Model\Quote $quote,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $registry,
        \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory
    ) {
        $this->quote = $quote;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->_registry = $registry;
        $this->purchaseCollectionFactory = $purchaseCollectionFactory;
    }

    /**
     * Set subtotal if reward point is applied
     *
     * @param Nvp $object
     * @param string $methodName
     * @param array $request
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeCall(\Magento\Paypal\Model\Api\Nvp $object , string $methodName, array $request)
    {
        if (!isset($request['AMT'])) {
            return [$methodName, $request];
        }
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        $quoteItemCount = $quote->getItemsCount();

        $paypalMethodList = ['payflowpro','payflow_link','payflow_advanced','braintree_paypal','paypal_express_bml','payflow_express_bml','payflow_express','paypal_express'];
        if(in_array($paymentMethod,$paypalMethodList)){
             $purchase = $this->purchaseCollectionFactory->create()
                 ->addFieldToFilter('quote_id', $quote->getId());
             if ($purchase->getSize()) {
                 $discount = $purchase->getFirstItem()->getData('discount');
                 $isDiscount = false;
                 foreach ($request as $key => $value) {
                     if ($value == 'Discount') {
                         $isDiscount = true;
                     }
                 }
                 if (($request['AMT'] != $quote->getGrandTotal())){
                     $request['AMT'] -= $discount;
                 }
                 $request['ITEMAMT'] -= $discount;
                 if ($isDiscount) {
                     $request['L_AMT'.$quoteItemCount] -= $discount;
                 } else {
                     $request['L_NUMBER'.$quoteItemCount] = null;
                     $request['L_NAME'.$quoteItemCount] = 'Discount';
                     $request['L_QTY'.$quoteItemCount] = 1;
                     $request['L_AMT'.$quoteItemCount] = '-'.(float)$discount;
                 }
             }
        }
        return [$methodName, $request];
    }

    /**
     * @param $cart
     */
    /*public function beforeGetAllItems($cart)
    {
        $quote = $this->_checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();

        $paypalMehodList = ['payflowpro','payflow_link','payflow_advanced','braintree_paypal','paypal_express_bml','payflow_express_bml','payflow_express','paypal_express'];
        if($quote->getDiscountAmount() && in_array($paymentMethod,$paypalMehodList)){
            if(method_exists($cart , 'addCustomItem' ))
            {
                $cart->addCustomItem(__("Reward Point Discount"), 1 ,  -1.00 * $quote->getDiscountAmount());
                $quote->setRewardpointsTotal(true);
            }
        }
    }*/
}
