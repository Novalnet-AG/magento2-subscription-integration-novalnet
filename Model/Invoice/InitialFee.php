<?php

namespace Novalnet\Subscription\Model\Invoice;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class InitialFee extends AbstractTotal
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository     = $cartRepository;
    }

    /**
     * To collect invoice totals
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $quote = $this->cartRepository->get($invoice->getOrder()->getQuoteId());
        $totals = $quote->getTotals();
        $initialFeeObject = $totals['nn_initial_fee']->getData();
        $initialFee = $initialFeeObject['value'];
        $invoice->setGrandTotal($invoice->getGrandTotal() + $initialFee);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $initialFee);
        parent::collect($invoice);

        return $this;
    }
}
