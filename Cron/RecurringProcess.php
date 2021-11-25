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
namespace Novalnet\Subscription\Cron;

use Magento\Framework\App\State as AppState;
use Magento\Store\Model\ScopeInterface;

class RecurringProcess
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @var \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger
     */
    protected $novalnetLogger;

    /**
     * @var \Magento\Sales\Model\Service\OrderService
     */
    protected $orderService;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagementInterface;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Novalnet\Subscription\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger
     * @param \Magento\Sales\Model\Service\OrderService $orderService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        AppState $appState,
        \Magento\Catalog\Model\Product $product,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Novalnet\Subscription\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->cart                     = $cart;
        $this->date                     = $date;
        $this->quote                    = $quote;
        $this->helper                   = $helper;
        $this->product                  = $product;
        $this->appState                 = $appState;
        $this->coreSession              = $coreSession;
        $this->scopeConfig              = $scopeConfig;
        $this->orderSender              = $orderSender;
        $this->eventManager             = $eventManager;
        $this->orderRepository          = $orderRepository;
        $this->storeManager             = $storeManager;
        $this->orderService             = $orderService;
        $this->orderFactory             = $orderFactory;
        $this->invoiceSender            = $invoiceSender;
        $this->quoteManagement          = $quoteManagement;
        $this->customerFactory          = $customerFactory;
        $this->subscriptionItems        = $subscriptionItems;
        $this->customerRepository       = $customerRepository;
        $this->orderItemRepository      = $orderItemRepository;
        $this->subscriptionDetails      = $subscriptionDetails;
        $this->checkoutSession          = $checkoutSession;
        $this->inlineTranslation        = $inlineTranslation;
        $this->transportBuilder         = $transportBuilder;
        $this->productRepository        = $productRepository;
        $this->subscriptionHelper       = $subscriptionHelper;
        $this->novalnetLogger           = $novalnetLogger;
        $this->cartRepositoryInterface  = $cartRepositoryInterface;
        $this->cartManagementInterface  = $cartManagementInterface;
    }

    /**
     * Method will run the cron process
     *
     * @return int|void|null
     */
    public function execute()
    {
        $this->checkExpiredRecurring();
        $collection = $this->getCollections();
        //Create Recurring
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $this->isError = false;
                $this->errorMessage = [];
                $this->coreSession->setRecurringProcess(true);
                $this->coreSession->setCanCalculateShipping(true);

                $recurringDetails = $this->subscriptionDetails->load($item->getParentId());

                $quote = $this->quote->create();
                $order = $this->orderFactory->create()->loadByIncrementId($item->getOrderId());
                $orderItem = $this->orderItemRepository->get($item->getItemId());
                $calculateShipping = (bool) $this->helper->getSubscriptionConfig('calculate_shipping', $order->getStoreId());
                if (!$calculateShipping) {
                    $this->coreSession->setCanCalculateShipping(false);
                }
                $quote->setStore($order->getStore());
                $quote->setCurrency();
                //Assign Customer
                $this->assignCustomer($quote, $order, $item);

                $quote->setBaseCurrencyCode($order->getBaseCurrencyCode());

                //Add product to the quote
                $product = $this->productRepository->getById($orderItem->getProduct()->getId());
                $product->setPrice($item->getBillingAmount());
                $this->addProduct($quote, $product, $item->getQty(), $orderItem);

                // set billing address
                $quote->getBillingAddress()->addData($order->getBillingAddress()->getData());

                // set shipping address & shipping method
                if (!$order->getIsVirtual()) {
                    $shippingAddress = $quote->getShippingAddress()->addData($order->getShippingAddress()->getData());
                    $shippingAddress->setShippingMethod($order->getShippingMethod())
                        ->setCollectShippingRates(true);
                }

                $quote->setPaymentMethod($order->getPayment()->getMethod()); //payment method
                $quote->setInventoryProcessed(false); //not effetc inventory
                $quote->save();

                $paymentData = ['method' => $order->getPayment()->getMethod()];

                $paymentData = $this->subscriptionHelper->getPaymentDataForRecurring($paymentData, $order->getPayment()->getMethod(), $item->getOrderId(), $recurringDetails);

                // Set Sales Order Payment
                try {
                    $quote->getPayment()->importData($paymentData);
                } catch (\Exception $e) {
                    $this->isError = true;
                    $this->errorMessage[] = $e->getMessage();
                    $this->novalnetLogger->error($e->getMessage());
                }
                // Collect Totals & Save Quote
                $quote->collectTotals()->save();

                try {
                    // Create Order From Quote
                    $cart = $this->cartRepositoryInterface->get($quote->getId());
                    $order_id = $this->cartManagementInterface->placeOrder($cart->getId());
                    $order = $this->orderRepository->get($order_id);
                    $this->checkoutSession->clearQuote();

                    if ($order->getCanSendNewEmailFlag()) {
                        $this->orderSender->send($order);
                        $invoice = current($order->getInvoiceCollection()->getItems());
                        if ($invoice) {
                            $this->invoiceSender->send($invoice);
                        }
                    }
                } catch (\Exception $e) {
                    $this->isError = true;
                    $this->errorMessage[] = $e->getMessage();
                    $this->novalnetLogger->error($e->getMessage());
                }
                //Unset session value
                $this->coreSession->unsRecurringProcess();

                if ($this->isError) {
                    $messages = json_encode($this->errorMessage);
                    $item->setState('PAUSED')
                        ->setMessage($messages)->save();
                    $this->checkProfileState('pause', $item);
                } else {
                    $this->doFollowupProcess($item, $recurringDetails, $order);
                }
            }
        }
    }

    /**
     * Do Folllowup process
     *
     * @param object $item
     * @param object $recurringDetails
     * @param object $order
     * @return none
     */
    public function doFollowupProcess($item, $recurringDetails, $order)
    {
        //Add current order ID to related orders
        $relatedOrders = json_decode($recurringDetails->getRelatedOrder());
        $relatedOrders[] = $order->getIncrementId();
        $recurringDetails->setRelatedOrder(json_encode($relatedOrders))->save();

        //reset remaining cycle
        $remainingCycle = $item->getRemainingCycle();
        $executedCycles = $item->getExecutedCycles();
        $item->setExecutedCycles($executedCycles + 1)->save();
        if ($remainingCycle > 0) {
            $remainingCycle --;
            $item->setRemainingCycle($remainingCycle)->save();
        }
        if ($item->getRemainingCycle() == 0) {
            $this->checkExpiredRecurring();
        }
        //save next schedule
        $this->getNextSchedule($item);
    }

    /**
     * Get Collection
     *
     * @return object
     */
    protected function getCollections()
    {
        $collection = $this->subscriptionItems->getCollection();
        $dateFrom = $this->date->gmtDate('Y-m-d H:i:s', strtotime('-1 hour'));
        $dateTo = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+1 hour'));
        $collection->addFieldToFilter('state', ['eq' => 'ACTIVE'])
            ->addFieldToFilter('remaining_cycle', [['neq' => 0], ['remaining_cycle', 'null'=>'']])
            ->addFieldToFilter('next_shedule', ['lt' => $dateTo]);
         return $collection;
    }

    /**
     * Add product to the quote
     *
     * @param object $quote
     * @param object $product
     * @param int $qty
     * @param object $orderItem
     * @return none
     */
    public function addProduct($quote, $product, $qty, $orderItem)
    {
        $params = [];
        $params['product'] = $product->getId();
        $params['qty'] = (int) $qty;
        if ($product->getTypeId() == 'configurable') {
            $option = $orderItem->getProductOptions();
            if ($option) {
                $infoBuyRequest = $option['info_buyRequest'];
                $params['item'] = 116;
                $params['super_attribute'] = $infoBuyRequest['super_attribute'];
                $params['selected_configurable_option'] = $infoBuyRequest['selected_configurable_option'];
            }
        } elseif ($product->getTypeId() == 'bundle') {
            $option = $orderItem->getProductOptions();
            if ($option) {
                $infoBuyRequest = $option['info_buyRequest'];
                $params['bundle_option'] = $infoBuyRequest['bundle_option'];
                $params['bundle_option_qty'] = $infoBuyRequest['bundle_option_qty'];
            }
        }
        $params = new \Magento\Framework\DataObject($params);

        try {
            $quote->addProduct(
                $product,
                $params
            );
        } catch (\Exception $e) {
            $this->isError = true;
            $this->errorMessage[] = $e->getMessage();
            $this->novalnetLogger->error($e->getMessage());
        }
    }

    /**
     * Get Next Scedule
     *
     * @param object $item
     * @return string
     */
    public function getNextSchedule($item)
    {
        $nextSchedule = '';
        //Check Trial period
        if ($item->getRemainingCycle() != 0 || $item->getRemainingCycle() == null) { //check remaining cycles
            $nextSchedule = $this->date->gmtDate('Y-m-d H:i:s', strtotime('+'.$item->getPeriodFrequency().' '.$item->getPeriodUnit()));
        }
        $item->setNextShedule($nextSchedule)->save();
    }

    /**
     * Assign customer to the quote
     *
     * @param object $quote
     * @param object $order
     * @param object $item
     * @return none
     */
    public function assignCustomer($quote, $order, $item)
    {
        try {
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($order->getStore()->getWebsiteId())->loadByEmail($order->getCustomerEmail());
            if (!$customer->getEntityId()) {
                //If not avilable then create this customer
                $customer->setWebsiteId($order->getStore()->getWebsiteId())
                        ->setStore($order->getStore())
                        ->setFirstname($order->getCustomerFirstname())
                        ->setLastname($order->getCustomerLastname())
                        ->setEmail($order->getCustomerEmail());
                $customer->save();
            }
            $this->updateCustomerToProfile($customer->getEntityId(), $item->getParentId());
            $this->coreSession->setCustomerId($customer->getEntityId());
            $quote->setStore($order->getStore());
            $customer= $this->customerRepository->getById($customer->getEntityId());
            $quote->setRemoteIp($order->getRemoteIp());
            $quote->assignCustomer($customer);
        } catch (\Exception $e) {
            $this->isError = true;
            $this->errorMessage[] = $e->getMessage();
            $this->novalnetLogger->error('Exception occured while assign customer to quote');
        }
    }

    /**
     * Assign customer to the profile
     *
     * @param int $customerId
     * @param int $profileId
     * @return none
     */
    public function updateCustomerToProfile($customerId, $profileId)
    {
        $itemModel = $this->subscriptionDetails->load($profileId);
        $itemModel->setCustomerId($customerId)->save();
    }

    /**
     * Get Expired items collection
     *
     * @return object
     */
    protected function getExpiredCollection()
    {
        $collection = $this->subscriptionItems->getCollection();
        $dateFrom = $this->date->gmtDate('Y-m-d', strtotime('+0 days'));
        $dateTo = $this->date->gmtDate('Y-m-d', strtotime('+3 days'));
        $collection->addFieldToFilter('state', ['eq' => 'ACTIVE'])
            ->addFieldToFilter('remaining_cycle', ['eq' => 0]);
         return $collection;
    }

    /**
     * Check Expired recurring profile and change state accordingly
     *
     * @return object
     */
    public function checkExpiredRecurring()
    {
        $collection = $this->getExpiredCollection();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                $item->setState('EXPIRED')->save();
                $itemCollection = $this->subscriptionItems->getCollection()
                    ->addFieldToFilter('parent_id', ['eq' => $item->getParentId()])
                    ->addFieldToFilter('id', ['neq' => $item->getId()])
                    ->addFieldToFilter('state', ['neq' => 'EXPIRED']);
                if (empty($itemCollection) || count($itemCollection) < 1) {
                    $itemModel = $this->subscriptionDetails->load($item->getParentId());
                    $itemModel->setState('EXPIRED')->save();
                }
            }
        }
    }

    /**
     * Check recurring profile and change state accordingly
     *
     * @param string $state
     * @param object $model
     * @return object
     */
    public function checkProfileState($state, $model)
    {
        $itemCollection = $this->subscriptionItems->getCollection()
                ->addFieldToFilter('parent_id', ['eq' => $model->getParentId()])
                ->addFieldToFilter('id', ['neq' => $model->getId()])
                ->addFieldToFilter('state', ['eq' => 'ACTIVE']);
        if ($state == 'cancel') {
            if (empty($itemCollection) || count($itemCollection) < 1) {
                $state = 'CANCELED';
            }
        } elseif ($state == 'pause') {
            if (empty($itemCollection) || count($itemCollection) < 1) {
                $state = 'PAUSED';
            }
        }
        $itemModel = $this->subscriptionDetails->load($model->getParentId());
        $itemModel->setState($state)->save();
    }
}
