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
use Novalnet\Payment\Model\Ui\ConfigProvider;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * Process capture Action
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        if (in_array($product->getTypeId(), ['grouped', 'bundle'])) {
            if ($product->getTypeId() == 'grouped') {
                $children = $product->getTypeInstance(true)->getAssociatedProducts($product);
            } else {
                $children = $product->getTypeInstance(true)
                    ->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);
            }
            
            if ($product->getNovalnetSubEnabled()) {
                $subStatus = 1;
                $subscriptionMode = $product->getNovalnetRecurringMethod();
                $triodPeriodUnit = $product->getNovalnetSubTrialPeriodUnit();
                $triodPeriodFrequency = $product->getNovalnetSubTrialPeriodFrequency();
                $initialFee = $product->getNovalnetSubInitialFee();
                $recurringOptions = $product->getNovalnetRecurringOptions();
            } else {
                $subStatus = 0;
                $subscriptionMode = $product->getNovalnetRecurringMethod();
                $triodPeriodUnit = $product->getNovalnetSubTrialPeriodUnit();
                $triodPeriodFrequency = $product->getNovalnetSubTrialPeriodFrequency();
                $initialFee = $product->getNovalnetSubInitialFee();
                $recurringOptions = $product->getNovalnetRecurringOptions();
            }
            
            foreach ($children as $childProduct) {
                $childProduct->setNovalnetSubEnabled($subStatus)
                    ->setNovalnetRecurringMethod($subscriptionMode)
                    ->setNovalnetSubTrialPeriodUnit($triodPeriodUnit)
                    ->setNovalnetSubTrialPeriodFrequency($triodPeriodFrequency)
                    ->setNovalnetSubInitialFee($initialFee)
                    ->setNovalnetRecurringOptions($recurringOptions)
                    ->save();
            }
        }

        return $this;
    }
}
