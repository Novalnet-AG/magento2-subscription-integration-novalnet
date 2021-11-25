<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Novalnet\Subscription\Block\Sales\Order\Creditmemo;

class InitialFee extends \Magento\Framework\View\Element\Template
{
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $order = $this->getParentBlock()->getOrder();
        if ($order->getNnInitialFee()) {
            $initialFee = new \Magento\Framework\DataObject(
                [
                    'code' => 'initial_fee',
                    'strong' => false,
                    'value' => $order->getNnInitialFee(),
                    'label' => __('Initial Fee'),
                ]
            );

            $this->getParentBlock()->addTotalBefore($initialFee, 'grand_total');
        }

        return $this;
    }
}
