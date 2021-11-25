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
namespace Novalnet\Subscription\Api;

interface SubscriptionInterface
{
    /**
     * Cancel subscription Item
     *
     * @api
     * @param int $id
     * @return boolean
     */
    public function cancel($id);

    /**
     * Reactivate subscription Item
     *
     * @api
     * @param int $id
     * @param string $nextShedule
     * @return boolean
     */
    public function activate($id, $nextShedule);

    /**
     * Change Payment method
     *
     * @api
     * @param int $id
     * @param string $paymentCode
     * @return boolean
     */
    public function changepayment($id, $paymentCode);

    /**
     * Pause subscription Item
     *
     * @api
     * @param int $id
     * @return boolean
     */
    public function pause($id);
}
