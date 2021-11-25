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

use Magento\Framework\App\RequestInterface;

class ReorderProcess implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
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
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $observer->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $productId = !empty($this->_request->getParam('product')) ? $this->_request->getParam('product') : $product->getId();
            $product = $this->productRepository->getById($productId);
            $recurringOptions = $this->getRecurringOptions($item, $product);
            if ($recurringOptions != false) {
                $additionalOptions = [];
                $additionalOptions = $this->setBillingPeriod($recurringOptions, $additionalOptions);
                $additionalOptions = $this->setTrialBillingPeriod($product, $additionalOptions);
                $infoBuyRequest = [];

                $option = $item->getOptionByCode('info_buyRequest');
                $infoBuyRequest = json_decode($option->getValue(), true);
                $infoBuyRequest['recurring'] = $recurringOptions;
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'additional_options',
                    'value' => $this->serializer->serialize($additionalOptions)
                ]);
                $item->addOption([
                    'product_id' => $item->getProductId(),
                    'code' => 'info_buyRequest',
                    'value' => $this->serializer->serialize($infoBuyRequest)
                ]);
                $item->setAdditionalData(json_encode($recurringOptions));
                $price = $this->calculateBillingAmount($product);
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                $item->getProduct()->setIsSuperMode(true);
            }
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
        
        if (!empty($recurringOptions['maximum_billing_frequency'])) {
            $billingPeriodValue = __('Recurrs %1 time(s)', $recurringOptions['maximum_billing_frequency']).' ';
            $billingPeriodValue .=  __('Every %1', $recurringOptions['billing_frequency']) . ' ' .__($recurringOptions['period_unit']);
        } else {
            $billingPeriodValue =  __('Every %1', $recurringOptions['billing_frequency']) . ' ' .__($recurringOptions['period_unit']). ' ';
            $billingPeriodValue .=  __('Repeats until cancelled');
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
            $trialBillingPeriodValue = __($product->getNovalnetSubTrialPeriodFrequency()).' '. __($product->getNovalnetSubTrialPeriodUnit()).' '. __('cycle');
            $additionalOptions[] = [
                'label' => __('Trial Period'),
                'value' => $trialBillingPeriodValue,
            ];
        }
        return $additionalOptions;
    }

    /**
     * Set billing period
     *
     * @param object $product
     * @return int
     */
    public function calculateBillingAmount($product)
    {
        $price = $product->getFinalPrice();
        if (!empty($product->getNovalnetSubTrialPeriodUnit()) && !empty($product->getNovalnetSubTrialPeriodFrequency())) {
            $price = 0;
        }
        return $price;
    }

    /**
     * Get Recurring options
     *
     * @param object $item
     * @param object $product
     * @return mixed
     */
    public function getRecurringOptions($item, $product)
    {
        $recordeId = $this->_request->getParam('recurring_option');
        $option = $item->getOptionByCode('info_buyRequest');
        $additionalData = json_decode($option->getValue(), true);
        $recurringData = isset($additionalData['recurring']) ? $additionalData['recurring'] : [];
        if (!empty($recurringData['period_unit']) && !empty($recurringData['billing_frequency'])) {
            return $recurringData;
        } elseif ($recordeId != 'regular_payment') {
            $recurringOptions = json_decode($product->getNovalnetRecurringOptions(), true);
            return isset($recurringOptions[$recordeId]) ? $recurringOptions[$recordeId] : false;
        }
        return false;
    }
}
