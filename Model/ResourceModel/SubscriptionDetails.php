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
namespace Novalnet\Subscription\Model\ResourceModel;

class SubscriptionDetails extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('novalnet_subscription_profile_details', 'id');
    }
}
