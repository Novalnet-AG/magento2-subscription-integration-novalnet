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
namespace Novalnet\Subscription\Controller\Subscription;
 
class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        return parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }
        $resultPage = $this->_pageFactory->create();
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('novalnet_subscription/subscription/index');
        }
        return $resultPage;
    }
}
