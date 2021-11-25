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

class TrialPayment extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->product = $product;
        $this->request = $request;
    }

    /**
     * Collect
     *
     * @param object $quote
     * @param mixed $shippingAssignment
     * @param mixed $total
     * @return string
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        return $this;
    }

    /**
     * Fetch
     *
     * @param object $quote
     * @param mixed $total
     * @return string
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'nn_trial_payment',
            'title' => __('Trial Payment'),
            'value' => 0,
            'area' => $this->showTrialPayment($quote)
        ];
    }

    /**
     * Get Trial label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Trial Payment');
    }

    /**
     * Get Trial Payment
     *
     * @param object $quote
     * @return string
     */
    public function showTrialPayment($quote)
    {
        $recordeId = $this->request->getParam('recurring_option');
        $show = 0;
        foreach ($quote->getAllItems() as $item) {
            $option = $item->getOptionByCode('info_buyRequest');
            $additionalData = json_decode($option->getValue(), true);
            if (isset($additionalData['recurring'])) {
                $recurringData = $additionalData['recurring'];
                $product = $this->product->create()->load($item->getProduct()->getId());
                if ($product->getNovalnetSubEnabled() && !empty($product->getNovalnetSubTrialPeriodUnit()) && !empty($product->getNovalnetSubTrialPeriodFrequency()) &&
                    !empty($recurringData['period_unit']) && !empty($recurringData['billing_frequency'])) {
                    $show = 1;
                    break;
                }
            }
        }
        return $show;
    }
}
