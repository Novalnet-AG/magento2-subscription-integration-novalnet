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
namespace Novalnet\Subscription\Block\Adminhtml\Profile\View;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Recurring Profile Tabs
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('nn_profile_info');
        $this->setDestElementId('recurring_profile_view');
        $this->setTitle(__('Profile Information'));
    }
}
