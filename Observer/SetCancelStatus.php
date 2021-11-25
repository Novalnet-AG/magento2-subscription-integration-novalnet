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
use Magento\Sales\Model\Order;

class SetCancelStatus implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
    ) {
        $this->date = $date;
        $this->subscriptionItems = $subscriptionItems;
        $this->subscriptionDetails = $subscriptionDetails;
    }

    /**
     * Set Canceled/Void status for Novalnet payments
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return none
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        if (preg_match('/novalnet/i', $payment->getMethodInstance()->getCode())) {
            $collection = $this->subscriptionDetails->getCollection();
                $collection->getSelect()->where("JSON_CONTAINS(related_order, '[\"".$order->getIncrementId()."\"]')");
            foreach ($collection as $item) {
                $item->setState('CANCELED')->save();
                $itemCollection = $this->subscriptionItems->getCollection()
                    ->addFieldToFilter('parent_id', ['eq' => $item->getId()]);
                foreach ($itemCollection as $child) {
                    $nextSchedule = $this->date->gmtDate('Y-m-d\TH:i:s\Z', strtotime('+'.$child->getPeriodFrequency().' '.$child->getPeriodUnit()));
                    $child->setNextShedule($nextSchedule);
                    $child->setState('CANCELED')->save();
                }
            }
        }
    }
}
