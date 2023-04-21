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

namespace Lof\RewardPoints\Helper;

use \Magento\Quote\Model\Quote;
use Lof\RewardPoints\Model\Config;
use Lof\RewardPoints\Model\Earning;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Lof\RewardPoints\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory
     */
    protected $earningRuleCollectionFactory;

    /**
     * @var \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory
     */
    protected $spendingRuleCollectionFactory;

    /**
     * @var String
     */
    protected $type;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $quote = null;

    /**
     * @var \Lof\RewardPoints\Logger\Logger
     */
    protected $rewardsLogger;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;
    /**
     * @var \Lof\RewardPoints\Model\Config
     */
    protected $rewardsConfig;

    /**
     * @var \Lof\RewardPoints\Helper\Trackcode
     */
    protected $_trackcode;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_coreSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param \Magento\Framework\App\Helper\Context                            $context
     * @param \Magento\Cms\Model\Template\FilterProvider                       $filterProvider
     * @param \Magento\Customer\Model\Session                                  $customerSession
     * @param \Lof\RewardPoints\Model\Session                                  $checkoutSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface                $customerRepository
     * @param \Magento\Framework\Registry                                      $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface                       $storeManager
     * @param \Magento\Framework\App\ResourceConnection                        $resource
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface             $localeDate
     * @param \Magento\Framework\ObjectManagerInterface                        $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                      $messageManager
     * @param \Magento\Quote\Api\CartRepositoryInterface                       $quoteRepository
     * @param \Magento\Framework\Filter\FilterManager                          $filterManager
     * @param \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory  $earningRuleCollectionFactory
     * @param \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory
     * @param \Lof\RewardPoints\Logger\Logger                                  $rewardsLogger
     * @param \Lof\RewardPoints\Model\Config                                   $rewardsConfig
     * @param \Lof\RewardPoints\Helper\Trackcode                               $trackcode
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\RewardPoints\Model\Session $checkoutSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Lof\RewardPoints\Model\ResourceModel\Earning\CollectionFactory $earningRuleCollectionFactory,
        \Lof\RewardPoints\Model\ResourceModel\Spending\CollectionFactory $spendingRuleCollectionFactory,
        \Lof\RewardPoints\Logger\Logger $rewardsLogger,
        \Lof\RewardPoints\Model\Config $rewardsConfig,
        \Lof\RewardPoints\Helper\Trackcode                               $trackcode,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        parent::__construct($context);
        $this->filterProvider                = $filterProvider;
        $this->customerSession               = $customerSession;
        $this->checkoutSession               = $checkoutSession;
        $this->customerRepository            = $customerRepository;
        $this->coreRegistry                  = $coreRegistry;
        $this->storeManager                  = $storeManager;
        $this->resource                      = $resource;
        $this->localeDate                    = $localeDate;
        $this->objectManager                 = $objectManager;
        $this->messageManager                = $messageManager;
        $this->quoteRepository               = $quoteRepository;
        $this->filterManager                 = $filterManager;
        $this->earningRuleCollectionFactory  = $earningRuleCollectionFactory;
        $this->spendingRuleCollectionFactory = $spendingRuleCollectionFactory;
        $this->rewardsLogger                 = $rewardsLogger;
        $this->rewardsConfig                 = $rewardsConfig;
        $this->_trackcode = $trackcode;
        $this->_coreSession = $coreSession;
        $this->currencyFactory = $currencyFactory;
        $this->_dateTime = $dateTime;
        $this->_timezoneInterface = $timezoneInterface;
    }

    public function getDateTime(){
        return $this->_dateTime;
    }

    public function getTimezoneDateTime($dateTime = "today"){
        if($dateTime === "today" || !$dateTime){
            $dateTime = $this->_dateTime->gmtDate();
        }

        $today = $this->_timezoneInterface
            ->date(
                new \DateTime($dateTime)
            )->format('Y-m-d H:i:s');
        return $today;
    }

    public function getTimezoneName(){
        return $this->_timezoneInterface->getConfigTimezone(\Magento\Store\Model\ScopeInterface::SCOPE_STORES);
    }

    public function convertPrice($amount = 0, $baseCurrency = null, $currentCurrency = null)
    {
        if(!$baseCurrency || !$currentCurrency){
            return $amount;
        }
        if ($currentCurrency != $baseCurrency) {
            $rate = $this->currencyFactory->create()->load($baseCurrency)->getAnyRate($currentCurrency);
            $amount = $amount * $rate;

        }
        return $amount;

    }

    public function getCoreSession(){
        return $this->_coreSession;
    }

    public function getRewardsUrl()
    {
        $url = $this->_urlBuilder->getUrl(Config::ROUTES);
        return $url;
    }

    public function formatPoints($points, $showImage = false, $showFull = false)
    {
        $unit = $this->getUnit($points);
        if (($pointImage = $this->getPointImage()) && $showImage) {
            if ($showFull) {
                $points = number_format((float)$points, 2, '.', '') . ' ' . $unit;
            } else {
                $points = number_format((float)$points, 2, '.', '');
            }
            $points .= $pointImage;
        } else {
            $points = number_format((float)$points, 2, '.', '') . ' ' . $unit;
        }
        return $points;
    }

    public function getPointImage()
    {
        $image = '';
        $pointImage = $this->getConfig()->getPointImage();
        if ($pointImage) {
            $imageSrc = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lof/rewardpoints/' . $pointImage;
            $image = __('<img src="%1" alt="Reward Points"/>', $imageSrc);
        }
        return $image;
    }

    public function getUnit($points)
    {
        switch ((float)$points) {
            case 1:
            case -1:
            case 0:
            $unit = $this->getConfig()->getPointLabel();
            break;

            default:
            $unit = $this->getConfig()->getPointsLabel();
            break;
        }
        return $unit;
    }

    public function getConfig()
    {
        return $this->rewardsConfig;
    }

    public function isProductPage()
    {
        if ($this->coreRegistry->registry('product')) {
            return true;
        }
        return false;
    }

    public function filter($str)
    {
        if (empty($str)) {
            return $str;
        }
        $html = $this->filterProvider->getPageFilter()->filter($str);
        return $html;
    }

    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getFormatEarningRuleNum($num) {
        $rounding_method = $this->getConfig()->getRoundingMethod();
        switch ($rounding_method) {
            case 'floor':
            $num = floor($num);
            break;

            case 'ceil':
            $num = ceil($num);
            break;

            default:
            $num = round($num);
            break;
        }
        return $num;
    }

    public function getCustomer($customerId = '')
    {
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
        } else {
            $customer = $this->customerSession->getCustomer();
        }
        return $customer;
    }

    /**
     * Set quote object associated with the cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @codeCoverageIgnore
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_currentStore;

    /**
     * Sets current store for translation.
     *
     * @param \Magento\Store\Model\Store $store
     *
     * @return void
     */
    public function setCurrentStore($store)
    {
        $this->_currentStore = $store;
    }

    /**
     * Returns current store.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        if (!$this->_currentStore) {
            $this->_currentStore = $this->storeManager->getStore();
        }

        return $this->_currentStore;
    }

    /**
     * Quote object getter
     *
     * @param int|null $quoteId
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote($quoteId = null)
    {
        if ($quoteId) {
            $this->quote = $this->quoteRepository->get($quoteId);
        } else {
            if (!$this->quote) {
                $this->quote = $this->checkoutSession->getQuote();
            }
        }
        return $this->quote;
    }

    /**
     * hasCheckoutSession
     *
     * @return bool
     */
    public function hasCheckoutSession()
    {
        if ($quote = $this->getQuote()) {
            $products = $quote->getAllItems();
            if($products && (count($products) > 0))
                return true;
        }
        return false;
    }

    /** ----------------------------- BACKEND ----------------------------- */
    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrive rule relationship by sepcific store id
     * @param  int  $ruleId
     * @param  int  $storeId
     * @param  boolean $showAllStoreView Set "Yes" retrive global rule
     * @return Lof\RewardPoints\Model\Earning
     */
    public function getRuleInAdmin($ruleId, $storeId, $showAllStoreView = true)
    {
        if ($this->getType() == 'earning') {
            $ruleCollection = $this->earningRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_earning_rule_relationships');
        } else {
            $ruleCollection = $this->spendingRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_spending_rule_relationships');
        }

        $ruleCollection->addFieldToFilter('store_id', $storeId);
        $ruleCollection->getSelect()->joinLeft(
            [
            'rs' => $tableName
            ],
            'rs.rule_id = main_table.rule_id'
            )
        ->where('rs.object_id = (?)', $ruleId)
        ->where('rs.store_id = (?)', $storeId);
        $rule = $ruleCollection->getFirstItem();

        if(!$rule->getId() && $showAllStoreView){
            $rule = $this->getGlobalRule($ruleId);
        }

        if ($this->getType() == 'earning') {
            $model = $this->objectManager->create('Lof\RewardPoints\Model\Earning');
        } else {
            $model = $this->objectManager->create('Lof\RewardPoints\Model\Spending');
        }
        $model->load($rule->getId());
        return $model;
    }

    /**
     * Retrive object id by rule
     * @param  int $ruleId
     * @return int
     */
    public function getRuleObjectId($ruleId)
    {
        if ($this->getType() == 'earning') {
            $collection = $this->earningRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_earning_rule_relationships');
        } else {
            $collection = $this->spendingRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_spending_rule_relationships');
        }


        $collection->getSelect()->joinLeft(
            [
            'rs' => $tableName
            ],
            'rs.object_id = main_table.rule_id'
            )
        ->where('rs.rule_id = (?)', $ruleId);
        $objectId = $collection->getFirstItem()->getObjectId();
        return $objectId;
    }

    /**
     * Retrive rule global settings
     * @param  int $objectId
     * @return Lof\RewardPoints\Model\Earning
     */
    public function getGlobalRule($ruleId) {
        if ($this->getType() == Earning::TYPE) {
            $ruleCollection = $this->earningRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_earning_rule_relationships');
        } else {
            $ruleCollection = $this->spendingRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_spending_rule_relationships');
        }

        $ruleCollection->getSelect()->joinLeft(
            [
                'rs' => $tableName
            ],
                'rs.object_id = main_table.rule_id'
            )
        ->where('rs.rule_id = (?)', $ruleId);
        $rule = $ruleCollection->getFirstItem();
        if ($this->getType() == 'earning') {
            $model = $this->objectManager->create('Lof\RewardPoints\Model\Earning');
        } else {
            $model = $this->objectManager->create('Lof\RewardPoints\Model\Spending');
        }
        $model->load($rule->getObjectId());
        return $model;
    }


    /**
     * Update Rule Relationship in all store view
     * @param  Lof\RewardPoints\Model\Earning
     * @return Lof\RewardPoints\Model\ResourceModel\Earning\Collection
     */
    public function updateRuleRelationShip($rule, $useDefault='')
    {
        try {
            $stores = $this->storeManager->getStores();
            foreach ($stores as $_store) {
                try{
                    $relationRule = '';
                    $relationRule = $this->getRuleInAdmin($rule->getId(), $_store->getId(), false);
                    if(!$relationRule->getId()){
                        if ($this->getType() == 'earning') {
                            $model = $this->objectManager->create('Lof\RewardPoints\Model\Earning');
                        } else {
                            $model = $this->objectManager->create('Lof\RewardPoints\Model\Spending');
                        }
                        $ruleData = $rule->getData();
                        unset($ruleData['rule_id']);
                        unset($ruleData['form_key']);
                        $ruleData['store_id'] = $_store->getId();
                        $ruleData['object_id'] = $rule->getId();
                        $use_default = [];
                        foreach ($ruleData as $k => $v) {
                            $use_default[] = $k;
                        }
                        $ruleData['use_default'] = $use_default;
                        $model->setData($ruleData);
                        $model->save();
                    } else{
                        $params = unserialize($relationRule->getUseDefault());
                        if(is_array($params)){
                            foreach ($params as $k => $v) {
                                $relationRule->setData($v, $rule->getData($v));
                            }
                            $relationRule->setData('store_id', $_store->getId());
                            $relationRule->setData('object_id', $rule->getId());
                            $relationRule->save();
                        }
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->rewardsLogger->addError($e->getMessage());
        }
        return $this;
    }


    /**
     * Retrive all rules relate by rule id
     * @param  int $ruleId
     * @return Lof\RewardPoints\Model\ResourceModel\Earning\Collection
     */
    public function getAllRule($ruleId)
    {
        if ($this->getType() == 'earning') {
            $ruleCollection = $this->earningRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_earning_rule_relationships');
        } else {
            $ruleCollection = $this->spendingRuleCollectionFactory->create();
            $tableName = $this->resource->getTableName('lof_rewardpoints_spending_rule_relationships');
        }

        $ruleCollection->getSelect()->joinLeft(
            [
            'rs' => $tableName
            ],
            'rs.rule_id = main_table.rule_id'
            )
        ->where('rs.object_id = (?)', $ruleId);
        return $ruleCollection;
    }

    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null
        ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
            );
    }

    public function generateRandomString($length = 10) {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function generateCouponCode($prefix = '', $block = 5, $from = 4, $to = 4){
        $licenses = [];
        for ($i=0; $i < $block; $i++) {
            $licenses[] = $this->generateRandomString(rand($from, $to));
        }
        $licenses = $prefix . implode('-',$licenses);
        return $licenses;
    }

    public function getCurrentProduct()
    {
        if ($this->coreRegistry->registry('product')) {
            return $this->coreRegistry->registry('product');
        }
        return false;
    }

    public function getCurrentCategory()
    {
        if ($this->coreRegistry->registry('current_category')) {
            return $this->coreRegistry->registry('current_category');
        }
        return false;
    }

    public function formatCustomVariables($str, $product = '', $earnPoints = '')
    {
        $customer = $this->getCustomer();
        $quote    = $this->getQuote();
        $category = $this->getCurrentCategory();
        $store    = $this->storeManager->getStore();
        if ($product == '') {
            $product  = $this->getCurrentProduct();
        }

        $data = [
            "customer"    => $customer,
            "quote"       => $quote,
            "product"     => $product,
            "category"    => $category,
            "store"       => $store,
            "earn_points" => $earnPoints
        ];
        $result = $this->filterManager->template($str, ['variables' => $data]);
        return $result;
    }

    /**
     * Generate Referer Code
     *
     * @param string $prefix
     * @param string $suffix
     * @return string
     */
    public function generateReferercode($prefix = "", $suffix = ""){
        if(!isset($this->chunks)){
            $this->chunks = $this->getStoreConfig("behaviorplugin/code_chunks",1);
        }
        if(!isset($this->letters)){
            $this->letters = $this->getStoreConfig("behaviorplugin/code_letters",9);
        }
        if($this->separate_text){
            $this->separate_text = $this->getStoreConfig("behaviorplugin/code_separate_text","-");
        }

        $this->_trackcode->numberChunks = (int)$this->chunks;
        $this->_trackcode->numberLettersPerChunk = (int)$this->letters;
        $this->_trackcode->separateChunkText = $this->separate_text;

        $serial_number = $this->_trackcode->generate();

        return $prefix.$serial_number.$suffix;
    }

    /**
     * Return reward points behavior config value by key and store
     *
     * @param string $key
     * @param \Magento\Store\Model\Store|int|string $store
     * @return string|null
     */
    public function getStoreConfig($key, $default="", $group = "lofrewardpoints", $store = null)
    {
       $result = $this->scopeConfig->getValue(
           $group.'/'.$key,
           \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
           $store);
       if($result == "") {
           $result = $default;
       }
       return $result;
   }
}
