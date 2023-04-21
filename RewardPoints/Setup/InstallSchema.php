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

namespace Lof\RewardPoints\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;


/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table lof_rewardpoints_earning_rule
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_earning_rule'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_earning_rule')
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Rule ID'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )->addColumn(
            'active_from',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active From'
        )->addColumn(
            'active_to',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active To'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Rule Type'
        )->addColumn(
            'action',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Action'
        )->addColumn(
            'earn_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Earn Points'
        )->addColumn(
            'monetary_step',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Monetary Step'
        )->addColumn(
            'points_limit',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Points Limit'
        )->addColumn(
            'sort_order',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Piority'
        )->addColumn(
            'is_stop_processing',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false],
            'Is Stop Processing'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Rule Active'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('lof_rewardpoints_earning_rule'),
                ['name', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['name', 'description'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Reward Points Earning Rule Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table lof_rewardpoints_product_earning_points
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_product_earning_points'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_product_earning_points')
        )->addColumn(
            'id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sku'
        )->addColumn(
            'points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false],
            'Points'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addIndex(
                $installer->getIdxName('lof_rewardpoints_product_earning_points', ['product_id']),
                ['product_id']
        )->addIndex(
                $installer->getIdxName('lof_rewardpoints_product_earning_points', ['store_id']),
                ['store_id']
        )->addForeignKey(
            $installer->getFkName(
                'lof_rewardpoints_product_earning_points',
                'product_id',
                'catalog_product_entity',
                'entity_id'
                ),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table lof_rewardpoints_earning_rule_relationships
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_earning_rule_relationships'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_earning_rule_relationships')
        )->addColumn(
            'object_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Object ID'
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Rule ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'use_default',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Rule Relationships Params'
        )->addForeignKey(
            $installer->getFkName('lof_rewardpoints_earning_rule_relationships', 'rule_id', 'lof_rewardpoints_earning_rule', 'rule_id'),
            'rule_id',
            $installer->getTable('lof_rewardpoints_earning_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('lof_rewardpoints_earning_rule_relationships', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Reward Points Earning Rule Relationships'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table lof_rewardpoints_earning_rule_customer_group
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_earning_rule_customer_group'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_earning_rule_customer_group')
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'customer_group_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group ID'
        )->setComment(
            'Reward Points Earning Customer Group'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'lof_rewardpoints_purchase'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_purchase'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_purchase')
        )->addColumn(
            'purchase_id',
           Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Purchase Id'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Quote Id'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Order Id'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            'spend_amount',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Amount'
        )->addColumn(
            'discount',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Discount'
        )->addColumn(
            'spend_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Points'
        )->addColumn(
            'spend_cart_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Cart Points'
        )->addColumn(
            'spend_catalog_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Catalog Points'
        )->addColumn(
            'earn_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Earn Points'
        )->addColumn(
            'earn_catalog_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Earn Catalog Points'
        )->addColumn(
            'subtotal',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Sub Total'
        )->addColumn(
            'earn_cart_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Earn Cart Points'
        )->addColumn(
            'params',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Params'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addIndex(
            $installer->getIdxName('lof_rewardpoints_purchase', ['order_id']),
            ['order_id']
        )->addIndex(
                $installer->getIdxName('lof_rewardpoints_purchase', ['quote_id']),
            ['quote_id']
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('lof_rewardpoints_purchase'),
                ['customer_id']
            ),
            ['customer_id']
        )->setComment(
            'Reward Points Earning Rule Products'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'lof_rewardpoints_speding_rule'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_spending_rule'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_spending_rule')
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Description'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Rule Active'
        )->addColumn(
            'active_from',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active From'
        )->addColumn(
            'active_to',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Active To'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Rule Type'
        )->addColumn(
            'action',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Action'
        )->addColumn(
            'spend_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Points'
        )->addColumn(
            'monetary_step',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Monetary Step'
        )->addColumn(
            'spend_min_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Min Points'
        )->addColumn(
            'spend_max_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Spend Max Points'
        )->addColumn(
            'sort_order',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Piority'
        )->addColumn(
            'is_stop_processing',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Is Stop Processing'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('lof_rewardpoints_spending_rule'),
                ['name', 'description'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            ['name', 'description'],
            ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment(
            'Reward Points Spending Rule'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table lof_rewardpoints_spending_rule_customer_group
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_spending_rule_customer_group'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_spending_rule_customer_group')
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Rule ID'
        )->addColumn(
            'customer_group_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group ID'
        )->addIndex(
            $installer->getIdxName('lof_rewardpoints_spending_rule_customer_group', ['customer_group_id']),
            ['customer_group_id']
        )->setComment(
            'Reward Points Earning Customer Group'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table lof_rewardpoints_product_spending_points
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_product_spending_points'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_product_spending_points')
        )->addColumn(
            'id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(
            'sku',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Sku'
        )->addColumn(
            'points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => false],
            'Points'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addIndex(
                $installer->getIdxName('lof_rewardpoints_product_spending_points', ['product_id']),
                ['product_id']
        )->addIndex(
                $installer->getIdxName('lof_rewardpoints_product_spending_points', ['store_id']),
                ['store_id']
        )->addForeignKey(
            $installer->getFkName(
                'lof_rewardpoints_product_spending_points',
                'product_id',
                'catalog_product_entity',
                'entity_id'
                ),
        'product_id',
        $installer->getTable('catalog_product_entity'),
        'entity_id',
        Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table lof_rewardpoints_spending_rule_relationships
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_spending_rule_relationships'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_spending_rule_relationships')
        )->addColumn(
            'object_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Object ID'
        )->addColumn(
            'rule_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true,'nullable' => false, 'primary' => true],
            'Rule ID'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store ID'
        )->addColumn(
            'use_default',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Rule Relationships Param'
        )->addForeignKey(
            $installer->getFkName('lof_rewardpoints_spending_rule_relationships', 'rule_id', 'lof_rewardpoints_spending_rule', 'rule_id'),
            'rule_id',
            $installer->getTable('lof_rewardpoints_spending_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('lof_rewardpoints_spending_rule_relationships', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Reward Points Spending Rule Relationships'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'lof_rewardpoints_transaction'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_transaction'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_transaction')
        )->addColumn(
            'transaction_id',
            Table::TYPE_BIGINT,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Transaction ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            'quote_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Quote ID'
        )->addColumn(
            'amount',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Amount'
        )->addColumn(
            'amount_used',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => false, 'nullable' => true],
            'Amount Used'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Title'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Code'
        )->addColumn(
            'action',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Action'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Status'
        )->addColumn(
            'params',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Params'
        )->addColumn(
            'is_expiration_email_sent',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Expiration Email Sent'
        )->addColumn(
            'email_message',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Email Message'
        )->addColumn(
            'apply_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Apply At'
        )->addColumn(
            'is_applied',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Applied'
        )->addColumn(
            'is_expired',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false, 'default' => 0],
            'Is Expired'
        )->addColumn(
            'expires_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['unsigned' => false, 'nullable' => true],
            'Expires At'
        )->addColumn(
            'updated_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Updated At'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Store Id'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Order Id'
        )->addColumn(
            'admin_user_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Admin User Id'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('lof_rewardpoints_transaction'),
                ['customer_id']
            ),
            ['customer_id']
        )->setComment(
            'Reward Points Transaction'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'lof_rewardpoints_transaction'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_customer'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_customer')
        )->addColumn(
            'object_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Object ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Customer ID'
        )->addColumn(
            'hold_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => true],
            'Hold Points'
        )->addColumn(
            'available_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => true],
            'Available Points'
        )->addColumn(
            'total_points',
            Table::TYPE_DECIMAL,
            '10,4',
            ['unsigned' => true, 'nullable' => true],
            'Total Points'
        )->addColumn(
            'update_point_notification',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Update Point Notification'
        )->addColumn(
            'expire_point_notification',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Expire Point Notification'
        )->addColumn(
            'params',
            Table::TYPE_TEXT,
            '2M',
            ['nullable' => true],
            'Params'
        )->addIndex(
            $installer->getIdxName(
                $installer->getTable('lof_rewardpoints_customer'),
                ['customer_id']
            ),
            ['customer_id'],
            ['type' => 'unique']
        )->addForeignKey(
            $installer->getFkName('lof_rewardpoints_customer', 'customer_id', 'customer_entity', 'entity_id'),
            'customer_id',
            $installer->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Reward Points Customer'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'lof_rewardpoints_email'
         */
        $setup->getConnection()->dropTable($setup->getTable('lof_rewardpoints_email'));
        $table = $installer->getConnection()->newTable(
            $installer->getTable('lof_rewardpoints_email')
        )->addColumn(
            'email_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => false, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Email Id'
        )->addColumn(
            'transaction_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true],
            'Transaction Id'
        )->addColumn(
            'subject',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'subject'
        )->addColumn(
            'sender_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Sender Email'
        )->addColumn(
            'sender_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Sender Name'
        )->addColumn(
            'recipient_email',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Recipient Email'
        )->addColumn(
            'recipient_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Recipient Name'
        )->addColumn(
            'trigger',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Trigger'
        )->addColumn(
            'bug',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Bug'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            '64K',
            ['unsigned' => false, 'nullable' => true],
            'Message'
        )->addColumn(
            'sent_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Send At'
        )->addColumn(
            'status',
            Table::TYPE_TEXT,
            255,
            ['unsigned' => false, 'nullable' => false],
            'Status'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false],
            'Is Rule Active'
        )->setComment(
            'Reward Points Email Log'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
