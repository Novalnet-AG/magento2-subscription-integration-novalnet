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
namespace Novalnet\Subscription\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class SerializedProfiles implements ObserverInterface
{
    const NOVALNET_RECURRING_OPTIONS_CODE = 'novalnet_recurring_options';

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $post = $this->request->getPost();
        $post = $post['product'];
        $recurringOptions = isset($post[self::NOVALNET_RECURRING_OPTIONS_CODE]) ? $post[self::NOVALNET_RECURRING_OPTIONS_CODE] : '';
        $product->setNovalnetRecurringOptions($recurringOptions);
        $requiredParams = ['period_unit','billing_frequency'];
        if (is_array($recurringOptions)) {
            $recurringOptions = $this->removeEmptyArray($recurringOptions, $requiredParams);
            $product->setNovalnetRecurringOptions(json_encode($recurringOptions));
        }
    }

    /**
     * Function to remove empty array from the multi dimensional array
     *
     * @param Array $recurringOptions
     * @param Array $requiredParams
     * @return Array
     */
    private function removeEmptyArray($recurringOptions, $requiredParams)
    {
        $requiredParams = array_combine($requiredParams, $requiredParams);
        $reqCount = count($requiredParams);
        foreach ($recurringOptions as $key => $values) {
            $values = array_filter($values);
            $inersectCount = count(array_intersect_key($values, $requiredParams));
            if ($reqCount != $inersectCount) {
                unset($recurringOptions[$key]);
            }
        }
        return $recurringOptions;
    }
}
