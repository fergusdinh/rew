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

namespace Lof\RewardPointsRule\Model\ResourceModel;
use Magento\Framework\DB\DataConverter\SerializedToJson;

class Earning extends \Lof\RewardPoints\Model\ResourceModel\Earning
{
    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;
    /**
     * @return void
     */
    protected function _construct()
    {
    	$this->_init('lof_rewardpoints_earning_rule', 'rule_id');
    }

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
        ) {
        parent::__construct($context,$storeManager, $connectionName);
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    public function convertSerializedDataToJson(){
        $connection = $this->getConnection();
        // Upgrade logic here
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        //convert conditions fields of table lof_rewardpoints_earning_rule
        $earning_rule_table = $this->getTable('lof_rewardpoints_earning_rule');
        $fieldDataConverter->convert($connection, $earning_rule_table, "rule_id", "conditions_serialized");
        $fieldDataConverter->convert($connection, $earning_rule_table, "rule_id", "actions_serialized");
    }
}
