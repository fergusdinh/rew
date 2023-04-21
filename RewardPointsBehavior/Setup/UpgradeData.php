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

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * attribute to define referrer code for reward points
     */
    const REFERRER_CODE = 'referrer_code';

    /**
     * attribute to define referrer code for reward points
     */
    const REFERRED_CODE = 'referred_code';


    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;


    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         *  Customer attributes
         */
        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, self::REFERRER_CODE);
            $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, self::REFERRED_CODE);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);


            /**
             * Create customer attribute referrer_code
             */
            $customerSetup->addAttribute(Customer::ENTITY, self::REFERRER_CODE,
                [
                    'type' => 'text',
                    'label' => 'Referrer Code',
                    'input' => 'text',
                    'required' => false,
                    'default' => '',
                    'visible' => false,
                    'user_defined' => true,
                    'sort_order' => 215,
                    'position' => 215,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ]);
            $referrer_code = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::REFERRER_CODE)
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);
            $referrer_code->save();

            /**
             * Create customer attribute referred_code
             */
            $customerSetup->addAttribute(Customer::ENTITY, self::REFERRED_CODE,
                [
                    'type' => 'text',
                    'label' => 'Referred Code',
                    'input' => 'text',
                    'required' => false,
                    'default' => '',
                    'visible' => false,
                    'user_defined' => true,
                    'sort_order' => 215,
                    'position' => 215,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                    'is_searchable_in_grid' => false,
                ]);
            $referred_code = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::REFERRED_CODE)
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => ['adminhtml_customer'],
                ]);
            $referred_code->save();
        }
        $setup->endSetup();
    }
}
