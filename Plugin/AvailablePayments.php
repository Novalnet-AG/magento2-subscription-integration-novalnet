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
namespace Novalnet\Subscription\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Backend\Model\Auth\Session as BackendSession;

class AvailablePayments
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $salesOrderModel;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @param CustomerSession $customerSession
     * @param BackendSession $backendSession
     * @param \Magento\Sales\Model\Order $salesOrderModel
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     */
    public function __construct(
        CustomerSession $customerSession,
        BackendSession $backendSession,
        \Magento\Sales\Model\Order $salesOrderModel,
        \Magento\Catalog\Model\ProductFactory $product,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper
    ) {
        $this->product = $product;
        $this->customerSession = $customerSession;
        $this->backendSession = $backendSession;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Check payment method is available for subscription order
     *
     * @param \Magento\Payment\Model\Method\AbstractMethod $subject
     * @param boolean $result
     * @param object|null $quote
     * @return bool
     */
    public function afterIsAvailable(\Magento\Payment\Model\Method\AbstractMethod $subject, $result, $quote = null)
    {
        $paymentMethodCode = $subject->getCode();
        $subscription = false;
        $supportedPayments = $this->subscriptionHelper->getSubscriptionConfig('supported_payments');
        $supportedPayments = explode(',', $supportedPayments);
        if (!empty($quote) && $result) {
            foreach ($quote->getAllItems() as $items) {
                $additionalData = json_decode($items->getAdditionalData(), true);
                $product = $this->product->create()->load($items->getProduct()->getId());
                if (!empty($additionalData['period_unit']) && !empty($additionalData['billing_frequency'])) {
                    $subscription = true;
                    break;
                }
            }
            if ($subscription) {
                if (in_array($paymentMethodCode, $supportedPayments)) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }
}
