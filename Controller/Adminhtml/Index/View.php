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
namespace Novalnet\Subscription\Controller\Adminhtml\Index;

class View extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory = false;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionDetails
     */
    protected $subscriptionDetails;

    /**
     * @var \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails
     * @param \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Novalnet\Subscription\Model\SubscriptionDetails $subscriptionDetails,
        \Novalnet\Subscription\Model\SubscriptionItems $subscriptionItems
    ) {
        parent::__construct($context);
        $this->coreRegistry        = $registry;
        $this->resultPageFactory   = $resultPageFactory;
        $this->subscriptionItems   = $subscriptionItems;
        $this->subscriptionDetails = $subscriptionDetails;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Novalnet_Subscription::profile')
            ->addBreadcrumb(__('Recurring Profile'), __('Recurring Profile'))
            ->addBreadcrumb(__('View Profile'), __('View Profile'));
        return $resultPage;
    }

    /**
     * Execute
     *
     * @return this
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->subscriptionDetails;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->coreRegistry->register('recurring_profile', $model);
        $this->coreRegistry->register('recurring_id', $id);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Subscription Profile # %1', $model->getSubscriptionId()) : __('Recurring Profile'));
        return $resultPage;
    }
}
