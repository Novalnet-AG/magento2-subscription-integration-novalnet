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

class ProcessCaptureAction implements ObserverInterface
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
     * Process capture Action
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $storeId = $order->getStoreId();
        $paymentMethodCode = $order->getPayment()->getMethod();

        $collection = $this->subscriptionDetails->getCollection();
        $collection->getSelect()->where("JSON_CONTAINS(related_order, '[\"".$order->getIncrementId()."\"]')");
        foreach ($collection as $item) {
            $item->setState('ACTIVE')->save();
            $itemCollection = $this->subscriptionItems->getCollection()
                ->addFieldToFilter('parent_id', ['eq' => $item->getId()]);
            foreach ($itemCollection as $child) {
                if (!empty($child->getTrialPeriodUnit()) && !empty($child->getTrialPeriodFrequency())) {
                    $nextSchedule = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+'.$child->getTrialPeriodFrequency().' '.$child->getTrialPeriodUnit()));
                } else {
                    $nextSchedule = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+'.$child->getPeriodFrequency().' '.$child->getPeriodUnit()));
                }
                $child->setNextShedule($nextSchedule);
                if ($child->getState() != 'EXPIRED') {
                    $child->setState('ACTIVE')->save();
                }
            }
        }

        return $this;
    }
}
