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

class ReminderProcess
{
    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger
     */
    protected $novalnetLogger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Novalnet\Subscription\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Novalnet\Subscription\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->date                     = $date;
        $this->helper                   = $helper;
        $this->scopeConfig              = $scopeConfig;
        $this->storeManager             = $storeManager;
        $this->orderFactory             = $orderFactory;
        $this->subscriptionItems        = $subscriptionItems;
        $this->orderItemRepository      = $orderItemRepository;
        $this->inlineTranslation        = $inlineTranslation;
        $this->transportBuilder         = $transportBuilder;
        $this->novalnetLogger           = $novalnetLogger;
    }

    /**
     * Method will run the cron process
     *
     * @return int|void|null
     */
    public function execute()
    {

        $canRemaind = $this->helper->getSubscriptionConfig('can_remaind');
        if ($canRemaind) {
            $this->doRemainder();
        }
    }

    /**
     * Execute remainder process
     *
     * @return object
     */
    public function doRemainder()
    {
        $collection = $this->getRemainderCollection();
        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {
                try {
                    $this->getMailParams($item);
                    $this->sendRemainder();
                } catch (\Exception $e) {
                    $this->isError = true;
                    $this->errorMessage[] = $e->getMessage();
                    $this->novalnetLogger->error('Remainder mail sending failed');
                }
            }
        }
    }

    /**
     * Get Remainder collectionCollection
     *
     * @return object
     */
    protected function getRemainderCollection()
    {
        $collection = $this->subscriptionItems->getCollection();
        $dateFrom = $this->date->gmtDate('Y-m-d', strtotime('+1 days'));
        $dateTo = $this->date->gmtDate('Y-m-d', strtotime('+3 days'));
        $collection->addFieldToFilter('state', ['eq' => 'ACTIVE'])
            ->addFieldToFilter('remaining_cycle', [
                ['neq' => 0], ['remaining_cycle', 'null'=> null]])
            ->addFieldToFilter('next_shedule', ['lt' => $dateTo])
            ->addFieldToFilter('next_shedule', ['gt' => $dateFrom]);
         return $collection;
    }

    /**
     * Send remainder mail
     *
     * @return object
     */
    public function sendRemainder()
    {
        try {
            $emailToAddrs = str_replace(' ', '', $this->emailToAddr);
            $emailToAddrs = explode(',', $emailToAddrs);
            $templateVars = [
                'fromName' => $this->emailFromName,
                'fromEmail' => $this->emailFromAddr,
                'toName' => $this->emailToName,
                'toEmail' => $this->emailToAddr,
                'subject' => $this->emailSubject,
                'body' => $this->emailBody
            ];

            $from = ['email' => $this->emailFromAddr, 'name' => $this->emailFromName];
            $this->inlineTranslation->suspend();

            $storeId = $this->storeManager->getStore()->getId();
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier(
                'novalnet_subscription_cancel_template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->addTo($emailToAddrs)
                ->setFrom($from)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->novalnetLogger->error("Email sending failed: $e");
            return false;
        }

        return true;
    }

    /**
     * Get Remainder mail params
     *
     * @param object $model
     * @return object
     */
    public function getMailParams($model)
    {
        try {
            $this->order = $this->orderFactory->create()->loadByIncrementId($model->getOrderId());
            $this->emailToAddr = $this->order->getCustomerEmail();
            $this->emailSubject = __('Subscription renewal reminder for your order# %1', $this->order->getIncrementId());
            $orderItem = $this->orderItemRepository->get($model->getItemId());
            $product = $orderItem->getProduct();
            $this->emailBody = __('This email is sent to remind you on %1 that has been recurring on %2.', $product->getName(), $this->date->gmtDate('Y-m-d H:i:s', strtotime($model->getNextShedule())));
            $this->emailFromAddr = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
            $this->emailFromName  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
            $this->emailToName  = $this->order->getCustomerName();
        } catch (\Exception $e) {
            $this->novalnetLogger->error("Email sending failed: $e");
            return false;
        }
    }
}
