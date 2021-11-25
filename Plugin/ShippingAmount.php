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

class ShippingAmount
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->coreSession        = $coreSession;
    }

    /**
     * Set shipping carrier's method price
     *
     * @param object $subject
     * @param string|float|int $price
     * @return $this
     */
    public function beforeSetPrice($subject, $price)
    {
        if ($this->coreSession->getRecurringProcess() && $this->coreSession->getCanCalculateShipping() == false) {
            return 0;
        }

        return $price;
    }
}
