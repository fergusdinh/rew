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
namespace Lof\RewardPointsRule\Setup;
use Magento\Framework\DB\DataConverter\SerializedToJson;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\DB\FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var \Magento\Framework\DB\Select\QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var \Magento\Framework\DB\Query\Generator
     */
    private $queryGenerator;

    /**
     * Constructor
     *
     * @param \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory
     * @param \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory
     * @param \Magento\Framework\DB\Query\Generator $queryGenerator
     */
    public function __construct(
        \Magento\Framework\DB\FieldDataConverterFactory $fieldDataConverterFactory,
        \Magento\Framework\DB\Select\QueryModifierFactory $queryModifierFactory,
        \Magento\Framework\DB\Query\Generator $queryGenerator
    ) {
          $this->fieldDataConverterFactory = $fieldDataConverterFactory;
          $this->queryModifierFactory = $queryModifierFactory;
          $this->queryGenerator = $queryGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
          if (version_compare($context->getVersion(), '1.0.1', '<')) {
              $this->convertSerializedDataToJson($setup);
          }
    }

    /**
     * Upgrade to version 2.0.1, convert data for the sales_order_item.product_options and quote_item_option.value
     * from serialized to JSON format
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @return void
     */
    private function convertSerializedDataToJson(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        $installer = $setup;
        $connection = $installer->getConnection();
        // Upgrade logic here
        $fieldDataConverter = $this->fieldDataConverterFactory->create(SerializedToJson::class);
        //convert conditions fields of table lof_rewardpoints_earning_rule
        $earning_rule_table = $installer->getTable('lof_rewardpoints_earning_rule');
        $fieldDataConverter->convert($connection, $earning_rule_table, "rule_id", "conditions_serialized");
        $fieldDataConverter->convert($connection, $earning_rule_table, "rule_id", "actions_serialized");
        //convert conditions fields of table lof_rewardpoints_spending_rule
        $spending_rule_table = $installer->getTable('lof_rewardpoints_spending_rule');
        $fieldDataConverter->convert($connection, $spending_rule_table, "rule_id", "conditions_serialized");
        $fieldDataConverter->convert($connection, $spending_rule_table, "rule_id", "actions_serialized");
    }
}