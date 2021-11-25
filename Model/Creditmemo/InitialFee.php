<?php

namespace Novalnet\Subscription\Model\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

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
     * To collect totals
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $quote = $this->cartRepository->get($creditmemo->getOrder()->getQuoteId());
        $totals = $quote->getTotals();
        $initialFeeObject = $totals['nn_initial_fee']->getData();
        $initialFee = $initialFeeObject['value'];
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $initialFee);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $initialFee);
        parent::collect($creditmemo);

        return $this;
    }
}
