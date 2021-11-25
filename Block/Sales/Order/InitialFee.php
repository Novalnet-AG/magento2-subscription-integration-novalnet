<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Novalnet\Subscription\Block\Sales\Order;

class InitialFee extends \Magento\Framework\View\Element\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * To get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * To get label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * TO get value properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        $store = $this->getStore();

        $fee = new \Magento\Framework\DataObject(
            [
               'code' => 'initial_fee',
               'strong' => false,
               'value' => $this->_order->getNnInitialFee(),
               'label' => __('Initial Fee'),
            ]
        );

        if ($this->_order->getNnInitialFee() != 0) {
            $parent->addTotal($fee, 'initial_fee');
        }

        return $this;
    }
}
