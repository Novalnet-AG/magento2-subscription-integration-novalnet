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
namespace Novalnet\Subscription\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class SetAdditionalOptions implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param RequestInterface $request
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->productRepository = $productRepository;
    }

    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if ($product->getTypeId() == 'bundle') {
            return;
        }
        $productId = $this->_request->getParam('product');
        if (empty($productId)) {
            return;
        }
        $recurringOptions = $this->getRecurringOptions($product);
        if ($recurringOptions != false) {
            $additionalOptions = [];
            $additionalOptions = $this->setBillingPeriod($recurringOptions, $additionalOptions);
            $additionalOptions = $this->setTrialBillingPeriod($product, $additionalOptions);
            $observer->getProduct()->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        }
    }

    /**
     * Set billing period
     *
     * @param array $recurringOptions
     * @param array $additionalOptions
     * @return array
     */
    public function setBillingPeriod($recurringOptions, $additionalOptions)
    {
        $billingPeriodValue = PHP_EOL . __('Every %1', $recurringOptions['billing_frequency']) . ' ' .__($recurringOptions['period_unit']). ' ';
        if (!empty($recurringOptions['maximum_billing_frequency'])) {
            $billingPeriodValue .= PHP_EOL . __('Recurrs %1 time(s)', $recurringOptions['maximum_billing_frequency']);
        } else {
            $billingPeriodValue .= PHP_EOL . PHP_EOL . __('Repeats until cancelled');
        }
        $additionalOptions[] = [
            'label' => __('Billing Period'),
            'value' => $billingPeriodValue,
        ];
        return $additionalOptions;
    }

    /**
     * Set trial billing period
     *
     * @param object $product
     * @param array $additionalOptions
     * @return array
     */
    public function setTrialBillingPeriod($product, $additionalOptions)
    {
        if (!empty($product->getNovalnetSubTrialPeriodUnit()) && !empty($product->getNovalnetSubTrialPeriodFrequency())) {
            $trialBillingPeriodValue = PHP_EOL . __($product->getNovalnetSubTrialPeriodFrequency()).' '. __($product->getNovalnetSubTrialPeriodUnit()).' '. __('cycle');
            $additionalOptions[] = [
                'label' => __('Trial Period'),
                'value' => $trialBillingPeriodValue,
            ];
        }
        return $additionalOptions;
    }

    /**
     * Get Recurring options
     *
     * @param object $product
     * @return mixed
     */
    public function getRecurringOptions($product)
    {
        $recordeId = $this->_request->getParam('recurring_option');
        if ($recordeId != 'regular_payment') {
            $recurringOptions = json_decode($product->getNovalnetRecurringOptions(), true);
            return isset($recurringOptions[$recordeId]) ? $recurringOptions[$recordeId] : false;
        }
        return false;
    }
}
