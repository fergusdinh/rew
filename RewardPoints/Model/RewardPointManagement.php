<?php
namespace Lof\RewardPoints\Model;


use Lof\RewardPoints\Api\RewardPointManagementInterface;
use Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory as SpendingCollection;
use Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Lof\RewardPoints\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Lof\RewardPoints\Api\Data\RewardPointsSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class RewardPointManagement implements RewardPointManagementInterface
{
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory
     */
    protected $purchaseCollectionFactory;
    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $transactionCollectionFactory;
    /**
     * @var SpendingCollection
     */
    protected $rewardpointsCollection;
    /**
     * @var SpendingCollection
     */
    protected $spendingCollectionFactory;
    /**
     * @var RewardPointsSearchResultsInterfaceFactory
     */
    protected $searchResults;
    /**
     * @var \Lof\RewardPoints\Helper\Data
     */
    private $rewardsData;
    /**
     * @var \Lof\RewardPoints\Helper\Purchase
     */
    private $rewardsPurchase;
    private $spending;
    /**
     * @var \Lof\RewardPoints\Helper\Customer
     */
    private $rewardsCustomer;
    /**
     * @var \Lof\RewardPoints\Helper\Checkout
     */
    protected $rewardsCheckout;
    public function __construct( \Lof\RewardPoints\Model\ResourceModel\Purchase\CollectionFactory $purchaseCollectionFactory,
                                 RewardPointsSearchResultsInterfaceFactory $searchResults,
                                 CollectionFactory $transactionCollectionFactory,
                                 SpendingCollection $spendingCollectionFactory,
                                 \Lof\RewardPoints\Helper\Data $rewardsData,
                                 \Lof\RewardPoints\Helper\Checkout $rewardsCheckout,
                                 \Lof\RewardPoints\Helper\Customer $rewardsCustomer,
                                 Spending $spending,
                                 \Lof\RewardPoints\Helper\Purchase $rewardsPurchase,
                                 CustomerCollection $rewardpointsCollection) {
        $this->purchaseCollectionFactory    = $purchaseCollectionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->rewardpointsCollection       = $rewardpointsCollection;
        $this->spendingCollectionFactory    = $spendingCollectionFactory;
        $this->searchResults                = $searchResults;
        $this->rewardsData                   = $rewardsData;
        $this->rewardsPurchase               = $rewardsPurchase;
        $this->spending                      = $spending;
        $this->rewardsCustomer               = $rewardsCustomer;
        $this->rewardsCheckout               = $rewardsCheckout;

    }
    /**
     * GET spending total point by customer
     * @param int $cartId
     * @return mixed
     */
    public function getTotalSpentPoint($cartId)
    {
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $cartId);
        foreach ($collection->getItems() as $spendpoints) {

            $result[]['spend_points'] = $spendpoints->getSpendPoints();
        }

        return $result;
    }
    public function getTransaction($customer_id)
    {
        $result = [];
        $collection = $this -> transactionCollectionFactory->create();
        $collection->addFieldToFilter('customer_id',$customer_id);
        foreach ($collection->getItems() as $transaction)
        {
            $result[]['data'] = $transaction->getData();
        }
        return $result;

    }
    /**
     * @inheritDoc
     */
    public function getTotalCustomerPoints($customerId)
    {
        $result = [];
        $connection = $this->rewardpointsCollection->create();
        $connection->addFieldToFilter('customer_id', $customerId);
        foreach ($connection->getItems() as $points) {
            $result[]['total_points'] = $points->getData()['total_points'];
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getOrderEarnPoints($order_id)
    {
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $order_id);
        foreach ($collection->getItems() as $earnpoints) {

            $result[]['earn_points'] = $earnpoints->getEarnPoints();
        }

        return $result;
    }
    /**
     * @inheritDoc
     */
    public function getOrderEarnSpentPoints($order_id)
    {
        return $this->getOrderEarnPoints($order_id);
    }
    /**
     * GET List spend rule in cart
     * @param int $cartId
     * @return mixed
     */
    public function getListSpendingRule($cartId)
    {
        $result = [];
        $collection = $this->purchaseCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $cartId);
        foreach ($collection->getItems() as $rule) {

            $result[]['list_rule_by_cart'] = $rule->getParams();
        }

        return $result;
    }
    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $Collection = $this->spendingCollectionFactory->create()
            ->setCurPage(1);
        $searchResults = $this->searchResults->create();
        $searchResults->setItems($Collection->getItems());
        $searchResults->setTotalCount($Collection->getSize());
        return $searchResults;
    }
    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applyPoint($cartId,$spendPoint,$spendingRuleId)
    {
        // TODO: Implement applyPoint() method.
        $quote    = $this->rewardsData->getQuote($cartId);
        $purchase = $this->rewardsPurchase->getPurchase($quote);
        $spending = $this->spending->load($spendingRuleId);
        $param[] = $purchase->getParams();
//        $spendingRate = $param[0]['spending_rate']['rules'][$spendingRuleId]['stepdiscount'];
        $spendingRate =(int) $spending->getData()['spend_points'];
        $monetaryStep = (int) $spending->getData()['monetary_step'];
        $rulemin = (int) $spending->getData()['spend_min_points'];
        $rulemax = (int) $spending->getData()['spend_max_points'];
        $discount = ($spendPoint * $monetaryStep)/$spendingRate;
        $customer = $this->rewardsCustomer->getCustomer(1);
        $avaiblePoints =(int) $customer->getTotalPoints();
        if($spendPoint<=$avaiblePoints && $avaiblePoints!=0 && $spendPoint!=0) {
            if($spendPoint<$spendingRate)
            {
                throw new NoSuchEntityException(__('You need to earn more points'));
            }
            $max = ($quote->getSubtotal()/ $monetaryStep) * $spendingRate;
            if($spendPoint>$max)
            {
                throw new NoSuchEntityException(__('Spend point is greater than Max Point in Cart'));
            }
            $array = [
                'isAjax' => 'true',
                'spendpoints' => $spendPoint,
                'discount' => $discount,
                'rule' => $spendingRuleId,
                'stepdiscount' => $spendingRate,
                'quote' => $cartId,
                'rulemin' => (int)$rulemin,
                'rulemax' => $rulemax,
            ];
            $this->rewardsCheckout->applyPoints($array);
            $result [] = [
                'spend_points' => $spendPoint,
                'grand_total' => $quote->getGrandTotal(),
                'subtotal' => $quote->getSubtotal(),
                'discount' => $discount,
                'earn_points' => (int)$purchase->getEarnPoints(),
            ];
        }else{
            throw new NoSuchEntityException(__('Spend point is greater than customer\'s available point or Spend Point can\'t have value 0'));
        }
        return $result;
    }
}
