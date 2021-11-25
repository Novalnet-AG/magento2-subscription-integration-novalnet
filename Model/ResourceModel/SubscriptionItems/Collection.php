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
namespace Novalnet\Subscription\Model\ResourceModel\SubscriptionItems;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initializing
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Novalnet\Subscription\Model\SubscriptionItems::class,
            \Novalnet\Subscription\Model\ResourceModel\SubscriptionItems::class
        );
    }
}
