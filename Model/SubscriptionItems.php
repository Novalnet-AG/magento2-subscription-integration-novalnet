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

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Novalnet model for handling Novalnet transaction status
 */
class SubscriptionItems extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Novalnet\Subscription\Model\ResourceModel\SubscriptionItems::class);
    }

    /**
     * Load order transaction status by custom attribute value. Attribute value should be unique
     *
     * @param string $value
     * @param string $attribute
     * @return \Novalnet\Payment\Model\ResourceModel\TransactionStatus
     */
    public function loadByAttribute($value, $attribute)
    {
        $this->load($value, $attribute);
        return $this;
    }
}
