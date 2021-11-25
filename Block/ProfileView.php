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
namespace Novalnet\Subscription\Block;

use Magento\Framework\View\Element\Template;

class ProfileView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    public $pricingHelper;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $collection;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Data\Collection $collection,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collection          = $collection;
        $this->storeManager        = $storeManager;
        $this->orderFactory        = $orderFactory;
        $this->pricingHelper       = $pricingHelper;
        $this->addressRenderer     = $addressRenderer;
        $this->customerSession     = $customerSession;
        $this->subscriptionItems   = $subscriptionItems;
        $this->collectionFactory   = $collectionFactory;
        $this->orderItemRepository = $orderItemRepository;
        $this->subscriptionHelper  = $subscriptionHelper;
        $this->subscriptionDetails = $subscriptionDetails;
    }

    /**
     * Prepare Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Subscription Profile'));
        if ($this->getRelatedOrderCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'recurring.related.orders'
            )->setAvailableLimit([10=>10,15=>15,20=>20])
                ->setShowPerPage(true)->setCollection(
                    $this->getRelatedOrderCollection()
                );
            $this->setChild('pager', $pager);
            $this->getRelatedOrderCollection()->load();
        }
    }

    /**
     * Get Collection
     *
     * @return string
     */
    public function getCollection()
    {
        $id = $this->getRequest()->getParam('id');
        $customerId = $this->customerSession->getCustomer()->getId();
        $storeId = $this->getStoreId();
        $collection = $this->subscriptionDetails->getCollection()
            ->addFieldToFilter('id', ['eq' => $id]);
        return $collection;
    }

    /**
     * Get Collection data
     *
     * @param string $field
     * @return string
     */
    public function getCollectionData($field)
    {
        $collection = $this->getCollection()->getFirstItem();
        return $collection->getData($field);
    }

    /**
     * End customer can cancel recurring
     *
     * @return boolean
     */
    public function canCancel()
    {
        return $this->subscriptionHelper->getSubscriptionConfig('customer_can_cancel');
    }

    /**
     * Get formatted address
     *
     * @return array
     */
    public function getFormattedAddress()
    {
        $address = [];
        $order = $this->getOrder();
        if (!$order->getIsVirtual()) {
            $address['billing'] = $this->addressRenderer->format($order->getBillingAddress(), 'html');
            $address['shipping'] = $this->addressRenderer->format($order->getShippingAddress(), 'html');
        }
        return $address;
    }

    /**
     * Get Order
     *
     * @return object
     */
    public function getOrder()
    {
        $orderId = $this->getCollectionData('order_id');
        return $this->orderFactory->create()->loadByIncrementId($orderId);
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        $state = $this->getCollectionData('state');
        $stateClass = ($state == 'ACTIVE') ? 'notice' : (($state == 'CANCELED') ? 'critical' : (($state == 'EXPIRED') ? 'critical' : 'minor'));
        return '<span class="grid-severity-'.$stateClass.'">'.__($state).'</span>';
    }

    /**
     * Get related order collection
     *
     * @return collection
     */
    public function getRelatedOrderCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()
            ->getParam('limit') : 10;
        $relatedOrderIds = $this->getCollectionData('related_order');
        $relatedOrderIds = json_decode($relatedOrderIds, true);
        $collection = $this->orderFactory->create()->getCollection()
            ->addFieldToFilter('increment_id', ['in' => $relatedOrderIds]);
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Get Location hash
     *
     * @return string
     */
    public function getLocationHash()
    {
        $hash = 'profile_details';
        if ($this->getRequest()->getParam('limit') || $this->getRequest()->getParam('p')) {
            $hash = 'related_orders';
        }
        return $hash;
    }

    /**
     * Get pager HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get order item details
     *
     * @return array
     */
    public function getOrderItemDetails()
    {
        $id = $this->getRequest()->getParam('id');
        $items = $this->subscriptionItems->getCollection()->addFieldToFilter('parent_id', ['eq' => $id]);
        $itemDetails = [];
        foreach ($items as $item) {
            $orderItem = $this->orderItemRepository->get($item->getItemId());
            $product = $orderItem->getProduct();
            $itemDetails[$item->getId()]['Name'] = $product->getName();
            $itemDetails[$item->getId()]['Qty'] = $item->getQty();
            $itemDetails[$item->getId()]['state'] = $item->getState();
            $itemDetails[$item->getId()]['BillingDetails'] = $this->getBillingDetails($item);
            $itemDetails[$item->getId()]['BillingInformation'] = $this->getBillingInformation($item);
            $itemDetails[$item->getId()]['BillingAmount'] = $this->pricingHelper->currency($item->getBillingAmount(), true, false);
        }
        return $itemDetails;
    }

    /**
     * Get Billing details
     *
     * @param object $item
     * @return string
     */
    public function getBillingDetails($item)
    {
        $html = '<strong>'.__('Billing Period').':</strong>'. '</br>';
        if (!empty($item->getPeriodMaxCycles())) {
            $html .= __('Recurrs %1 time(s)', $item->getPeriodMaxCycles()). '</br>';
            $html .= __($item->getPeriodFrequency()). ' '. __($item->getPeriodUnit()). ' '. __('cycle'). '</br>';
        } else {
            $html .= __($item->getPeriodFrequency()). ' '. __($item->getPeriodUnit()). ' '. __('cycle'). '</br>';
            $html .= __('Repeats until cancelled'). '</br>';
        }

        if (!empty($item->getTrialPeriodUnit()) && !empty($item->getTrialPeriodFrequency())) {
            $html .= '<strong>'.__('Trial Period').':</strong>'. '</br>';
            $html .= __($item->getTrialPeriodFrequency()). ' '. __($item->getTrialPeriodUnit()). ' '. __('cycle'). '</br>';
        }
        return $html;
    }

    /**
     * Get Billing Information
     *
     * @param object $item
     * @return string
     */
    public function getBillingInformation($item)
    {
        $html = __('Current Running Cycle : %1', $item['executed_cycles']). '</br>';
        if (!empty($item['remaining_cycle'])) {
            $html .= __('Remaining Cycles : %1', $item['remaining_cycle']). '</br>';
        } elseif ($item['remaining_cycle'] == null) {
            $html .= __('Remaining Cycles : %1', __('Until cancelled')). '</br>';
        }
        if ($item->getState() == 'ACTIVE') {
            $html .= __('Next Renewal Date : %1', $this->subscriptionHelper->getDate($item->getNextShedule(), 'd M Y H:i:s')). '</br>';
        }
        return $html;
    }
    
    /**
     * Returns Recurring profile cancel url for the Novalnet module
     *
     * @param  none
     * @return string
     */
    public function getCancelUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return str_replace('index.php/', '', $baseUrl) . 'rest/V1/novalnet/subscription/cancel/';
    }

    /**
     * Get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->subscriptionHelper;
    }

    /**
     * Returns Recurring profile activate url for the Novalnet module
     *
     * @param  none
     * @return string
     */
    public function getActivateUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return str_replace('index.php/', '', $baseUrl) . 'rest/V1/novalnet/subscription/activate/';
    }

    /**
     * Returns Recurring profile payment change url for the Novalnet module
     *
     * @param  none
     * @return string
     */
    public function getChangePaymentUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return str_replace('index.php/', '', $baseUrl) . 'rest/V1/novalnet/subscription/changepayment/';
    }

    /**
     * Get Payment title from payment code
     *
     * @param  string $paymentCode
     * @return string
     */
    public function getPaymentTitleByCode($paymentCode)
    {
        return $this->subscriptionHelper->getPaymentTitleByCode($paymentCode);
    }

    /**
     * Get Available payments
     *
     * @return array
     */
    public function getAvailablePayments()
    {
        $supportedPayments = $this->subscriptionHelper->getSubscriptionConfig('supported_payments');
        $supportedPayments = explode(',', $supportedPayments);
        $availablePayments = [];
        foreach ($supportedPayments as $paymentCode) {
            $availablePayments[$paymentCode] = $this->subscriptionHelper->getPaymentTitleByCode($paymentCode);
        }
        return $availablePayments;
    }
}
