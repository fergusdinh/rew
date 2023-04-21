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

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;


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
         * Table lof_rewardpoints_earning_rule
         */
        $table = $installer->getTable('lof_rewardpoints_earning_rule');
        $installer->getConnection()->addColumn(
            $table,
            'points_limit_month',
            [
                'type'     => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment'  => 'Points Limit Month'
            ]
        );

        $installer->getConnection()->addColumn(
            $table,
            'points_limit_year',
            [
                'type'     => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment'  => 'Points Limit Year'
            ]
        );

        $installer->getConnection()->addColumn(
            $table,
            'history_message',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'comment'  => 'History Message'
            ]
        );

        $installer->getConnection()->addColumn(
            $table,
            'email_message',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => '64M',
                'comment'  => 'Email Message'
            ]
        );

        $installer->endSetup();


    }
}