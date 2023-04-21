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
         * Table lof_rewardpoints_earning_rule
         */
        $table = $installer->getTable('lof_rewardpoints_earning_rule');
        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_earning_rule'),
            'conditions_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => '2M',
                'comment'  => 'Conditions Serialized'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_earning_rule'),
            'actions_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => '2M',
                'comment'  => 'Actions Serialized'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_earning_rule'),
            'qty_step',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => 255,
                'comment'  => 'Qty Step'
            ]
        );

        /**
         * Table lof_rewardpoints_spending_rule
         */
        $table = $installer->getTable('lof_rewardpoints_spending_rule');
        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_spending_rule'),
            'conditions_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => '2M',
                'comment'  => 'Conditions Serialized'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_spending_rule'),
            'actions_serialized',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => '2M',
                'comment'  => 'Actions Serialized'
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('lof_rewardpoints_spending_rule'),
            'qty_step',
            [
                'type'     => Table::TYPE_TEXT,
                'unsigned' => true,
                'nullable' => true,
                'length'   => 255,
                'comment'  => 'Qty Step'
            ]
        );

        $installer->endSetup();


    }
}