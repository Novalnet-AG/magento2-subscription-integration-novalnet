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
namespace Novalnet\Subscription\Helper;

use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\DataObject;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param EventManager $eventManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        EventManager $eventManager
    ) {
        $this->date                   = $date;
        $this->product                = $product;
        $this->jsonHelper             = $jsonHelper;
        $this->scopeConfig            = $scopeConfig;
        $this->serializer             = $serializer;
        $this->cartRepository         = $cartRepository;
        $this->orderRepository        = $orderRepository;
        $this->subscriptionItems      = $subscriptionItems;
        $this->subscriptionDetails    = $subscriptionDetails;
        $this->eventManager           = $eventManager;
    }

    /**
     * Get Novalnet Subscription Configuration values
     *
     * @param  string $field
     * @param  int|null $storeId
     * @return string
     */
    public function getSubscriptionConfig($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'novalnet/subscription/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Payment title from payment code
     *
     * @param string $paymentCode
     * @param mixed $storeId
     * @return string
     */
    public function getPaymentTitleByCode($paymentCode, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            'payment/'.$paymentCode.'/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Date with format
     *
     * @param  string $date
     * @param  string|null $format
     * @return string
     */
    public function getDate($date, $format = 'Y-m-d')
    {
        return $this->date->gmtDate($format, strtotime($date));
    }

    /**
     * Save subscription items
     *
     * @param  object $order
     * @return none
     */
    public function saveSubscriptionItems($order)
    {
        $quote = $this->cartRepository->get($order->getQuoteId());
        $totals = $quote->getTotals();
        $initialFeeObject = $totals['nn_initial_fee']->getData();
        $initialFee = $initialFeeObject['value'];
        $orderAdditionalData = json_decode($order->getPayment()->getAdditionalData(), true);
        $paymentMethodCode = $order->getPayment()->getMethod();
        $method = $order->getPayment()->getMethodInstance();
        $subscriptionItems = [];
        $update = false;
        foreach ($order->getAllItems() as $item) {
            if (empty($item->getParentItemId())) {
                $additionalData = json_decode($item->getAdditionalData(), true);
                $product = $this->product->create()->load($item->getProduct()->getId());
                if (!empty($additionalData['period_unit']) && !empty($additionalData['billing_frequency'])) {
                    $profileItemState = $this->getState($order);
                    $update = true;
                    $subscriptionItems[$item->getId()]['order_id'] = $order->getIncrementId();
                    $subscriptionItems[$item->getId()]['item_id'] = $item->getId();
                    $subscriptionItems[$item->getId()]['qty'] = $item->getQtyOrdered();
                    $subscriptionItems[$item->getId()]['product_id'] = $product->getId();
                    $subscriptionItems[$item->getId()]['state'] = $profileItemState;
                    $subscriptionItems[$item->getId()]['billing_amount'] = $product->getFinalPrice();
                    $subscriptionItems[$item->getId()]['period_unit'] = $additionalData['period_unit'];
                    $subscriptionItems[$item->getId()]['period_frequency'] = $additionalData['billing_frequency'];
                    $subscriptionItems[$item->getId()]['period_max_cycles'] = !empty($additionalData['maximum_billing_frequency']) ? $additionalData['maximum_billing_frequency'] : null;
                    $subscriptionItems[$item->getId()]['trial_period_unit'] = $product->getNovalnetSubTrialPeriodUnit();
                    $subscriptionItems[$item->getId()]['trial_period_frequency'] = $product->getNovalnetSubTrialPeriodFrequency();
                    $subscriptionItems[$item->getId()]['remaining_cycle'] = $this->getRemainingCycle($additionalData);
                    $subscriptionItems[$item->getId()]['executed_cycles'] = $this->getExecutedCycle($additionalData);
                    $subscriptionItems[$item->getId()]['next_shedule'] = $this->getNextScheduleDate($subscriptionItems[$item->getId()]);
                }
            }
        }

        //save profile details
        if ($update) {
            $order->setNnInitialFee($initialFee)->save();
            $relatedOrder = [$order->getIncrementId()];
            $profileDetails = [];
            $profileDetails['store_id'] = $order->getStoreId();
            $profileDetails['order_id'] = $order->getIncrementId();
            $profileDetails['reference_id'] = $order->getPayment()->getLastTransId();
            $profileDetails['customer_id'] = $order->getCustomerId();
            $profileDetails['payment_method'] = $paymentMethodCode;
            $profileDetails['related_order'] = $this->jsonHelper->jsonEncode($relatedOrder);

            $profileData = new DataObject();
            $profileData->setData($profileDetails);

            // Dispatch event
            $this->eventManager->dispatch(
                'save_subscription_profile',
                [
                    'item' => $profileData,
                    'order' => $order,
                    'payment_code' => $paymentMethodCode
                ]
            );

            $this->subscriptionDetails->setData($profileData->getData())
                ->save();
            $parentId = $this->subscriptionDetails->getId();
            $subscriptionSequenceId = $this->subscriptionSequenceId($this->subscriptionDetails, $parentId);
            foreach ($subscriptionItems as $item) {
                $item['parent_id'] = $parentId;
                $itemData = new DataObject();
                $itemData->setData($item);

                // Dispatch event
                $this->eventManager->dispatch(
                    'save_subscription_profile_item',
                    [
                        'item' => $itemData,
                        'order' => $order,
                        'payment_code' => $paymentMethodCode
                    ]
                );
                $this->subscriptionItems->setData($itemData->getData())->save();
            }
        }
    }

    /**
     * Get Next schedule date
     *
     * @param array $item
     * @return void
     */
    public function getNextScheduleDate($item)
    {
        $periodUnit = $item['period_unit'];
        $periodFrequency = $item['period_frequency'];
        $trialPeriodUnit = $item['trial_period_unit'];
        $trialPeriodFrequency = $item['trial_period_frequency'];
        $nextSchedule = '';
        //Check Trial period
        if (!empty($trialPeriodUnit) && !empty($trialPeriodFrequency)) {
            $nextSchedule = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+'.$trialPeriodFrequency.' '.$trialPeriodUnit));
        } else { //check remaining cycles
            $nextSchedule = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+'.$periodFrequency.' '.$periodUnit));
        }
        return $nextSchedule;
    }

    /**
     * Get payment data for recurring profile
     *
     * @param  array $paymentData
     * @param  string $paymentCode
     * @param  varchar $parentOrderId
     * @param  mixed $recurringProfile
     * @return array $paymentData
     */
    public function getPaymentDataForRecurring($paymentData, $paymentCode, $parentOrderId, $recurringProfile)
    {
        $additionalData = new DataObject();
        $additionalData->setData($paymentData);

        //For future use in observers
        $this->eventManager->dispatch(
            'subscription_payment_additionaldata',
            [
                'payment_data' => $additionalData,
                'payment_code' => $paymentCode,
                'parent_order_id' => $parentOrderId,
                'profile' => $recurringProfile
            ]
        );

        return $additionalData->getData();
    }

    /**
     * Get Subscription Sequence ID
     *
     * @param  object $subscriptionProfile
     * @param  int $subscriptionProfileId
     * @return string $sequenceId
     */
    public function subscriptionSequenceId($subscriptionProfile, $subscriptionProfileId)
    {
        $sequenceId = 10000 + $subscriptionProfileId;
        $sequenceData = new DataObject();
        $sequenceData->setData('sequence_id', $sequenceId);

        $this->eventManager->dispatch(
            'subscription_sequence_generate',
            [
                'sequence_data' => $sequenceData,
                'subscription_profile' => $subscriptionProfile,
                'subscription_profile_id' => $subscriptionProfileId
            ]
        );

        $subscriptionProfile->setSubscriptionId($sequenceData->getData('sequence_id'))->save();
    }

    /**
     * Get profile state based on order state
     *
     * @param Object $order
     * @return string $state
     */
    public function getState($order)
    {
        $state = 'PENDING';
        $invoice = current($order->getInvoiceCollection()->getItems());
        if ($invoice) {
            $state = 'ACTIVE';
        }
        return $state;
    }

    /**
     * Get Remaining Cycle
     *
     * @param  string $additionalData
     * @return string $state
     */
    public function getRemainingCycle($additionalData)
    {
        $remainingCycle = !empty($additionalData['maximum_billing_frequency']) ? $additionalData['maximum_billing_frequency'] - 1 : null;
        if (!empty($additionalData['trial_period_unit']) && !empty($additionalData['trial_period_frequency'])) {
            $remainingCycle = $additionalData['maximum_billing_frequency'];
        }
        return $remainingCycle;
    }

    /**
     * Get getExecuted Cycle
     *
     * @param  string $additionalData
     * @return string $state
     */
    public function getExecutedCycle($additionalData)
    {
        $executedCycle = 1;
        if (!empty($additionalData['trial_period_unit']) && !empty($additionalData['trial_period_frequency'])) {
            $executedCycle = 0;
        }
        return $executedCycle;
    }
}
