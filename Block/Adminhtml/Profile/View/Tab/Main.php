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
namespace Novalnet\Subscription\Block\Adminhtml\Profile\View\Tab;

/**
 * Deliverydate edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
        $this->order               = $order;
        $this->systemStore         = $systemStore;
        $this->pricingHelper       = $pricingHelper;
        $this->addressRenderer     = $addressRenderer;
        $this->subscriptionItems   = $subscriptionItems;
        $this->subscriptionHelper  = $subscriptionHelper;
        $this->orderItemRepository = $orderItemRepository;
        $this->subscriptionDetails = $subscriptionDetails;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Profile Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Profile Information');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get Target Option Array
     *
     * @return value
     */
    public function getTargetOptionArray()
    {
        return ['_self' => "Self", '_blank' => "New Page"];
    }

    /**
     * Get Recurring Profile
     *
     * @return object
     */
    public function getProfile()
    {
        return $this->_coreRegistry->registry('recurring_profile');
    }

    /**
     * Returns Recurring profile item cancel url for the Novalnet module
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
     * Returns Recurring profile item activate url for the Novalnet module
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
     * Returns Recurring profile item activate url for the Novalnet module
     *
     * @param  none
     * @return string
     */
    public function getPauseUrl()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        return str_replace('index.php/', '', $baseUrl) . 'rest/V1/novalnet/subscription/pause/';
    }

    /**
     * Get Recurring Profile Parent Order
     *
     * @return object
     */
    public function getOrder()
    {
        $profile = $this->getProfile();
        return $this->order->loadByIncrementId($profile->getOrderId());
    }

    /**
     * Get Formatted shipping and billing address
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
     * Get Order item details
     *
     * @return array
     */
    public function getOrderItemDetails()
    {
        $id = $this->getProfile()->getId();
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
     * Get Subscription billing details
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
        $html = __('Current Running Cycle : %1', $item->getExecutedCycles()). '</br>';
        if (!empty($item->getRemainingCycle())) {
            $html .= __('Remaining Cycles : %1', $item->getRemainingCycle()). '</br>';
        } elseif ($item->getRemainingCycle() == null) {
            $html .= __('Remaining Cycles : %1', __('Until cancelled')). '</br>';
        }
        if ($item->getState() == 'ACTIVE') {
            $html .= __('Next Renewal Date : %1', $this->subscriptionHelper->getDate($item->getNextShedule(), 'd M Y H:i:s')). '</br>';
        }
        return $html;
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
}
