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
 * @copyright  Copyright (c) 2020 Landofcoder (https://landofcoder.com/)
 * @license    https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPoints\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            /**
             * Create table 'lof_rewardpoints_redeem'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_rewardpoints_redeem')
            )->addColumn(
                'code_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'code id'
            )->addColumn(
                'code_prefix',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                30,
                ['nullable' => true],
                'Code Prefix'
            )->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'redeem code'
            )->addColumn(
                'earn_points',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '10,4',
                ['unsigned' => false, 'nullable' => true],
                'Earn Points'
            )->addColumn(
                'uses_per_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => true],
                'uses per code'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'active_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Active From'
            )->addColumn(
                'active_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Active To'
            )->addColumn(
                'code_used',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => false, 'nullable' => true, 'default' => 0],
                'code used'
            )->addIndex(
                $setup->getIdxName('lof_rewardpoints_redeem', ['code_id']),
                ['code_id']
            );
            $installer->getConnection()->createTable($table);

            /**
             * Create table 'lof_rewardpoints_codelog'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_rewardpoints_codelog')
            )->addColumn(
                'log_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'log id'
            )->addColumn(
                'code_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false],
                'code id'
            )->addColumn(
                'user_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => false],
                'user id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Store Id'
            )->addIndex(
                $setup->getIdxName('lof_rewardpoints_log', ['log_id']),
                ['log_id']
            );
            $installer->getConnection()->createTable($table);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            //change column
            $setup->getConnection()->changeColumn(
                $setup->getTable('lof_rewardpoints_spending_rule_relationships'),
                'rule_id',
                'rule_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'identity' => false,
                    'nullable' => false,
                    'auto_increment' => false
                ]
            );
            $setup->getConnection()->changeColumn(
                $setup->getTable('lof_rewardpoints_earning_rule_relationships'),
                'rule_id',
                'rule_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'identity' => false,
                    'nullable' => false,
                    'auto_increment' => false
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            //Create new column percent max points for spending rule
            $installer->getConnection()->addColumn(
                $installer->getTable('lof_rewardpoints_spending_rule'),
                'percentage_max_points',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'unsigned' => false,
                    'comment' => 'Percentage Max Points'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('lof_rewardpoints_purchase'),
                'base_discount',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'unsigned' => false,
                    'comment' => 'Base Discount Value'
                ]
            );

        }
        $installer->endSetup();
    }
}