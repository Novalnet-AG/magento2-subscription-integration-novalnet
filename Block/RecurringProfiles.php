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

class RecurringProfiles extends \Magento\Framework\View\Element\Template
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
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager        = $storeManager;
        $this->customerSession     = $customerSession;
        $this->subscriptionItems   = $subscriptionItems;
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
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'recurring.profiles.list'
            )->setAvailableLimit([10=>10,15=>15,20=>20])
                ->setShowPerPage(true)->setCollection(
                    $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
    }

    /**
     * Get collection
     *
     * @return object
     */
    public function getCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()
            ->getParam('limit') : 10;
        $customerId = $this->customerSession->getCustomer()->getId();
        $storeId = $this->getStoreId();
        $collection = $this->subscriptionDetails->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->setOrder('id', 'DESC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
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
     * Get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->subscriptionHelper;
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
