<?php
/**
 * Novalnet Subscription extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Novalnet End User License Agreement
 * that is bundled with this package in the file LICENSE.txt
 *
 * DISCLAIMER
 *
 * If you wish to customize Novalnet Subscription extension for your needs,
 * please contact technic@novalnet.de for more information.
 *
 * @category   Novalnet
 * @package    Novalnet_Subscription
 * @copyright  Copyright (c) Novalnet AG
 */
namespace Novalnet\Subscription\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class SubscriptionAttributes implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    private $eavTypeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    private $attributeGroupFactory;

    /**
     * @var \Magento\Eav\Model\AttributeManagement
     */
    private $attributeManagement;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $attributeGroupFactory
     * @param \Magento\Eav\Model\AttributeManagement $attributeManagement
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Entity\Attribute\SetFactory $attributeSetFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $attributeGroupFactory,
        \Magento\Eav\Model\AttributeManagement $attributeManagement,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->_categorySetupFactory = $categorySetupFactory;
        $this->_eavTypeFactory = $eavTypeFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_attributeGroupFactory = $attributeGroupFactory;
        $this->_attributeManagement = $attributeManagement;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $groupName = __('Subscription');

        $attributes = [
            'novalnet_sub_enabled' => [
                'type'                  => 'int',
                'label'                 => __('Enable subscription'),
                'input'                 => 'boolean',
                'source'                => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'sort_order'            => 100,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'novalnet_recurring_method' => [
                'type'                  => 'varchar',
                'label'                 => __('Product Purchase Option'),
                'input'                 => 'select',
                'sort_order'            => 110,
                'source'                => \Novalnet\Subscription\Model\Config\Source\RecurringMethod::class,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'novalnet_recurring_options' => [
                'type'                  => 'text',
                'label'                 => __('Manage Subscription Interval'),
                'input'                 => 'text',
                'sort_order'            => 120,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'novalnet_sub_initial_fee' => [
                'type'                  => 'decimal',
                'label'                 => __('Initial Fee'),
                'input'                 => 'text',
                'sort_order'            => 130,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'note'                    => __('Initial payment amount charged immediately upon purchase'),
                'required'              => false
            ],
            'novalnet_sub_trial_period_unit' => [
                'type'                  => 'varchar',
                'label'                 => __('Trial Billing Period Unit'),
                'input'                 => 'select',
                'sort_order'            => 140,
                'source'                => \Novalnet\Subscription\Model\Config\Source\PeriodUnit::class,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ],
            'novalnet_sub_trial_period_frequency' => [
                'type'                  => 'varchar',
                'label'                 => __('Trial Billing Frequency'),
                'input'                 => 'text',
                'sort_order'            => 150,
                'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'group'                 => $groupName,
                'is_used_in_grid'       => false,
                'is_visible_in_grid'    => false,
                'is_filterable_in_grid' => false,
                'used_for_promo_rules'  => true,
                'required'              => false
            ]
        ];

        $categorySetup = $this->_categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        foreach ($attributes as $code => $params) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $code, $params);
        }

        $this->sortGroup($groupName, 11);
    }

    /**
     * Sort Group
     *
     * @param string $groupName
     * @param object $order
     * @return boolean
     */
    private function sortGroup($groupName, $order)
    {
        $entityType = $this->_eavTypeFactory->create()->loadByCode('catalog_product');
        $setCollection = $this->_attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());
        foreach ($setCollection as $attributeSet) {
            $group = $this->_groupCollectionFactory->create()
                ->addFieldToFilter('attribute_set_id', $attributeSet->getId())
                ->addFieldToFilter('attribute_group_name', $groupName)
                ->getFirstItem()
                ->setSortOrder($order)
                ->save();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
