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

class InitialFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
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
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->request           = $request;
        $this->product           = $product;
        $this->coreSession       = $coreSession;
    }

    /**
     * Collect
     *
     * @param object $quote
     * @param mixed $shippingAssignment
     * @param mixed $total
     * @return object
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        $initialFee = $this->getInitialFee($quote);
        $total->setGrandTotal($initialFee);
        $total->setBaseGrandTotal($initialFee);
        parent::collect($quote, $shippingAssignment, $total);

        return $this;
    }

    /**
     * Fetch
     *
     * @param object $quote
     * @param object $total
     * @return string
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $initialFee = $this->getInitialFee($quote);
        $area = ($initialFee != 0) ? 1 : 0;
        return [
            'code' => 'nn_initial_fee',
            'title' => __('Initial Fee'),
            'value' => $initialFee,
            'area' => $area
        ];
    }

    /**
     * Get Initial label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Initial Fee');
    }

    /**
     * Get Initial Fee
     *
     * @param object $quote
     * @return string
     */
    public function getInitialFee($quote)
    {
        $recordeId = $this->request->getParam('recurring_option');
        $initialFee = 0;
        foreach ($quote->getAllItems() as $item) {
            $option = $item->getOptionByCode('info_buyRequest');
            $additionalData = json_decode($option->getValue(), true);
            if (isset($additionalData['recurring'])) {
                $recurringData = $additionalData['recurring'];
                $product = $this->product->create()->load($item->getProduct()->getId());
                if (!empty($recurringData['period_unit']) && !empty($recurringData['billing_frequency']) &&
                    !$this->coreSession->getRecurringProcess()) {
                    if (!empty($recurringData['initial_fee']) && empty($item->getParentItemId())) {
                        $initialFee += $recurringData['initial_fee'] * $item->getQty();
                    } elseif (!empty($product->getNovalnetSubInitialFee()) && empty($item->getParentItemId())) {
                        $initialFee += $product->getNovalnetSubInitialFee() * $item->getQty();
                    }
                }
            }
        }
        return $initialFee;
    }
}
