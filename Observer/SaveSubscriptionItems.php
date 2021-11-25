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

class SaveSubscriptionItems implements ObserverInterface
{
    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->coreSession     = $coreSession;
        $this->subscriptionHelper = $subscriptionHelper;
    }

    /**
     * Save Subscription items
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return none
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!in_array($order->getStatus(), ['closed', 'canceled'])) {
            $this->subscriptionHelper->saveSubscriptionItems($order);
        }
    }
}
