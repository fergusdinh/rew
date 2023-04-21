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
 * @package    Lof_RewardPointsBehavior
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\RewardPointsBehavior\Setup;

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
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /**
             * Table lof_rewardpoints_earning_rule
             */
            $table = $installer->getTable('lof_rewardpoints_earning_rule');
            $installer->getConnection()->addColumn(
                $table,
                'referred_points',
                [
                    'type' => Table::TYPE_FLOAT,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Referred Earning Points / Ratio'
                ]
            );

            $installer->getConnection()->addColumn(
                $table,
                'advanced_referral_points',
                [
                    'type' => Table::TYPE_TEXT,
                    'lenght' => '2M',
                    'nullable' => true,
                    'comment' => 'Advanced referral points configuration: min qty orders, max qty orders, referrer points, referrer calc type referred points, from, until'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            /**
             * Table lof_rewardpoints_earning_rule
             */
            $table = $installer->getTable('lof_rewardpoints_earning_rule');
            $installer->getConnection()->addColumn(
                $table,
                'apply_type',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Earn points will apply for referred customer place first valid order or customer register new account or both. Value: 0 - use for register new account, 1 - use for place first valid order, 2 - for both'
                ]
            );

        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            /**
             * Create table 'lof_rewardpoints_customer_referred'
             */
            $table = $installer->getConnection()->newTable(
                $installer->getTable('lof_rewardpoints_customer_referred')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'id'
            )->addColumn(
                'refered_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                30,
                ['nullable' => true],
                'Referred Email'
            )->addColumn(
                'referred_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Referred name'
            )->addColumn(
                'customer_refer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                ['unsigned' => false, 'nullable' => true],
                'Customer Refer Id'
            )->addColumn(
                'first_order',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => false, 'nullable' => true],
                'First Order'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addIndex(
                $setup->getIdxName('lof_rewardpoints_customer_referred', ['id']),
                ['id']
            );
            $installer->getConnection()->createTable($table);

        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            /**
             * Table lof_rewardpoints_customer_referred
             */
            $table = $installer->getTable('lof_rewardpoints_customer_referred');
            $installer->getConnection()->addColumn(
                $table,
                'number_orders',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Number of Orders which purchased by referered customer'
                ]
            );
            $installer->getConnection()->addColumn(
                $table,
                'total_orders',
                [
                    'type' => Table::TYPE_FLOAT,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0.00',
                    'comment' => 'Total of Orders'
                ]
            );
            /**
             * Table lof_rewardpoints_earning_rule
             */
            $table = $installer->getTable('lof_rewardpoints_earning_rule');
            $installer->getConnection()->addColumn(
                $table,
                'min_qty_orders',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Min qty orders'
                ]
            );

            $installer->getConnection()->addColumn(
                $table,
                'max_qty_orders',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'nullable' => true,
                    'default' => '0',
                    'comment' => 'Max qty orders'
                ]
            );
        }
        $installer->endSetup();
    }
}