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

namespace Novalnet\Subscription\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

class NovalnetNotice extends BaseHandler
{
    /**
     * @var int
     */
    protected $loggerType = MonologLogger::NOTICE;

    /**
     * @var string
     */
    protected $fileName = '/var/log/subscription/notice.log';
}
