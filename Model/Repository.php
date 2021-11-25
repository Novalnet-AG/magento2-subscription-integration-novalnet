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
namespace Novalnet\Subscription\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Novalnet\Payment\Model\Ui\ConfigProvider;

class Repository implements \Novalnet\Subscription\Api\SubscriptionInterface
{
    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems
     */
    private $subscriptionItems;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    private $subscriptionDetails;

    /**
     * @var \Novalnet\Subscription\Helper\Data
     */
    private $subscriptionHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * @var \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger
     */
    protected $novalnetLogger;

    /**
     * Repository constructor.
     *
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Helper\Data $subscriptionHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     * @param \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger
     */
    public function __construct(
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Helper\Data $subscriptionHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Novalnet\Subscription\Logger\NovalnetSubscriptionLogger $novalnetLogger
    ) {
        $this->scopeConfig         = $scopeConfig;
        $this->novalnetLogger      = $novalnetLogger;
        $this->storeManager        = $storeManager;
        $this->orderFactory        = $orderFactory;
        $this->subscriptionItems   = $subscriptionItems;
        $this->subscriptionDetails = $subscriptionDetails;
        $this->subscriptionHelper  = $subscriptionHelper;
        $this->transportBuilder    = $transportBuilder;
        $this->inlineTranslation   = $inlineTranslation;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @inheritdoc
     */
    public function cancel($id)
    {
        try {
            $itemModel = $this->subscriptionItems->load($id);
            $itemModel->setState('CANCELED')->save();
            $this->checkProfileState('cancel', $itemModel);
            $this->setMailParams($itemModel);
            $this->sendMail();
        } catch (\Exception $e) {
            $this->novalnetLogger->error($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function pause($id)
    {
        try {
            $itemModel = $this->subscriptionItems->load($id);
            if ($itemModel->getStatte() != 'EXPIRED') {
                $itemModel->setState('PAUSED')->save();
            }
            $this->checkProfileState('pause', $itemModel);
            $this->setMailParams($itemModel);
            $this->sendMail();
        } catch (\Exception $e) {
            $this->novalnetLogger->error($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function activate($id, $nextShedule)
    {
        try {
            $itemModel = $this->subscriptionItems->load($id);
            $itemModel->setState('ACTIVE')
                ->setNextShedule($nextShedule)
                ->save();
            $this->checkProfileState('activate', $itemModel);
        } catch (\Exception $e) {
            $this->novalnetLogger->error($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function changepayment($id, $paymentCode)
    {
        try {
            $itemModel = $this->subscriptionDetails->load($id);
            if ($token = $this->getToken($itemModel, $paymentCode)) {
                $itemModel->setPaymentMethod($paymentCode)
                    ->setToken($token)
                    ->save();
            }
        } catch (\Exception $e) {
            $this->novalnetLogger->error($e->getMessage());
        }
    }

    /**
     * Check Recurring profile state according profile items
     *
     * @param string $action
     * @param object $model
     * @return none
     */
    public function checkProfileState($action, $model)
    {
        $state = 'ACTIVE';
        $itemCollection = $this->subscriptionItems->getCollection()
                ->addFieldToFilter('parent_id', ['eq' => $model->getParentId()])
                ->addFieldToFilter('id', ['neq' => $model->getId()])
                ->addFieldToFilter('state', ['eq' => 'ACTIVE']);
        if ($action == 'cancel') {
            if (empty($itemCollection) || count($itemCollection) < 1) {
                $state = 'CANCELED';
            }
        } elseif ($action == 'pause') {
            if (empty($itemCollection) || count($itemCollection) < 1) {
                $state = 'PAUSED';
            }
        }
        $itemModel = $this->subscriptionDetails->load($model->getParentId());
        $itemModel->setState($state)->save();
    }

    /**
     * Send Mail
     *
     * @return boolean
     */
    public function sendMail()
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
     * Set mail parameters
     *
     * @param object $model
     * @return none
     */
    public function setMailParams($model)
    {
        $this->emailToAddr = $this->subscriptionHelper->getSubscriptionConfig('cancel_email_address');
        $this->order = $this->orderFactory->create()->loadByIncrementId($model->getOrderId());
        $this->emailSubject = __('Subscription Cancel from %1', $this->order->getStore()->getName());
        $orderItem = $this->orderItemRepository->get($model->getItemId());
        $product = $orderItem->getProduct();
        $this->emailBody = __('Subscription has been successfully cancelled for %1', $product->getName());
        $this->emailFromAddr = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
        $this->emailFromName  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
        $this->emailToName  = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
    }
}
