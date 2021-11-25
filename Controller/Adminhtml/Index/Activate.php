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

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Novalnet\Subscription\Model\ResourceModel\SubscriptionDetails\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\ResponseInterface;
use Novalnet\Subscription\Model\SubscriptionItems;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;

class Activate extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SubscriptionItems
     */
    protected $subscriptionItems;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SubscriptionItems $subscriptionItems
     * @param File $file
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        SubscriptionItems $subscriptionItems,
        File $file,
        Filesystem $filesystem
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->subscriptionItems = $subscriptionItems;
        $this->file = $file;
        $this->filesystem = $filesystem;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $directoryMedia = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $mediaRootDir = $directoryMedia->getAbsolutePath();
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $profileItemsCollection = $this->subscriptionItems->getCollection()
                ->addFieldToFilter('parent_id', ['eq' => $item->getId()]);
            foreach ($profileItemsCollection as $child) {
                $child->setState('ACTIVE')->save();
            }
            $item->setState('ACTIVE')->save();
        }

        $this->messageManager->addSuccess(__('A total of %1 profile(s) have been Activated.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
