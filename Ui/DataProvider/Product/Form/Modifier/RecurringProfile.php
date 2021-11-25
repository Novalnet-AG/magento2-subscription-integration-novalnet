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
namespace Novalnet\Subscription\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Modal;

class RecurringProfile extends AbstractModifier
{
    const NOVALNET_RECURRING_OPTIONS = 'novalnet_recurring_options';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param string $scopeName
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        $scopeName = ''
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->scopeName = $scopeName;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $fieldCode = self::NOVALNET_RECURRING_OPTIONS;
        $model = $this->locator->getProduct();
        $modelId = $model->getId();
        $optionsData = $model->getNovalnetRecurringOptions();

        if ($optionsData) {
            $optionsData = json_decode($optionsData, true);
            $optionsData = $this->validateOptionsData($optionsData);
            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/'. self::NOVALNET_RECURRING_OPTIONS;
            $data = $this->arrayManager->set($path, $data, $optionsData);
        }
        return $data;
    }

    /**
     * Validate options data
     *
     * @param array $optionsData
     * @return array $options
     */
    public function validateOptionsData($optionsData)
    {
        $i = 0;
        $options = [];
        foreach ($optionsData as $option) {
            $option['record_id'] = $i;
            $options[] = $option;
            $i++;
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $this->initNovalnetRecurringOptions();
        return $this->meta;
    }

    /**
     * Customize Novalnet Recurring Option field
     *
     * @return $this
     */
    protected function initNovalnetRecurringOptions()
    {
        $optionsPath = $this->arrayManager->findPath(
            self::NOVALNET_RECURRING_OPTIONS,
            $this->meta,
            null,
            'children'
        );

        if ($optionsPath) {
            $this->meta = $this->arrayManager->merge(
                $optionsPath,
                $this->meta,
                $this->initNovalnetRecurringOptionsStructure($optionsPath)
            );
            $this->meta = $this->arrayManager->set(
                $this->arrayManager->slicePath($optionsPath, 0, -3)
                . '/' . self::NOVALNET_RECURRING_OPTIONS,
                $this->meta,
                $this->arrayManager->get($optionsPath, $this->meta)
            );
            $this->meta = $this->arrayManager->remove(
                $this->arrayManager->slicePath($optionsPath, 0, -2),
                $this->meta
            );
        }

        return $this;
    }

    /**
     * Get novalnet recurring options dynamic rows structure
     *
     * @param string $optionsPath
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function initNovalnetRecurringOptionsStructure($optionsPath)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'label' => __('Manage Subscription Interval'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'required' => '0',
                        'sortOrder' =>
                            $this->arrayManager->get($optionsPath . '/arguments/data/config/sortOrder', $this->meta),
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'title' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Billing Period Unit'),
                                        'dataScope' => 'period_unit',
                                        'required' => '1',
                                        'options' => [
                                            ['value' => '', 'label' => __('--  Select  --')],
                                            ['value' => 'days', 'label' => __('days')],
                                            ['value' => 'weeks', 'label' => __('weeks')],
                                            ['value' => 'months', 'label' => __('months')],
                                            ['value' => 'years', 'label' => __('years')]
                                        ]
                                    ],
                                ],
                            ],
                        ],

                        'description' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Billing Frequency'),
                                        'dataScope' => 'billing_frequency',
                                        'required' => '1',
                                    ],
                                ],
                            ],
                        ],

                        'icon' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'componentType' => Field::NAME,
                                        'dataType' => Text::NAME,
                                        'label' => __('Maximum Billing Cycles'),
                                        'dataScope' => 'maximum_billing_frequency',
                                    ],
                                ],
                            ],
                        ],

                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
