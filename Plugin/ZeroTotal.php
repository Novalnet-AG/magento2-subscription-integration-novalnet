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

use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class ZeroTotal
{
    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * @param \Novalnet\Subscription\Helper\Data $helper
     */
    public function __construct(
        \Novalnet\Subscription\Helper\Data $helper
    ) {
        $this->helper    = $helper;
    }

    /**
     * Check Availability for zero total
     *
     * @param object $subject
     * @param boolean $result
     * @param MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @return boolean
     */
    public function afterIsApplicable($subject, $result, MethodInterface $paymentMethod, Quote $quote)
    {
        $supportedPayments = $this->helper->getSubscriptionConfig('supported_payments');
        $supportedPayments = explode(',', $supportedPayments);
        if (in_array($paymentMethod->getCode(), $supportedPayments)) {
            if ($quote) {
                foreach ($quote->getAllItems() as $items) {
                    $additionalData = json_decode($items->getAdditionalData(), true);
                    if (!empty($additionalData['period_unit']) && !empty($additionalData['billing_frequency'])) {
                        return true;
                    }
                }
            }
        }
        return $result;
    }
}
