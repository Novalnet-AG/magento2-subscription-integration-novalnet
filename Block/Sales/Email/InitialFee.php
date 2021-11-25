<?php
namespace Novalnet\Subscription\Block\Sales\Email;

class InitialFee extends \Magento\Framework\View\Element\Template
{
    /**
     * Tax configuration model
     *
     * @var \Magento\Tax\Model\Config
     */
    protected $config;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        array $data = []
    ) {
        $this->config = $taxConfig;
        $this->cartRepository = $cartRepository;
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * To get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->order = $parent->getOrder();
        $quote = $this->cartRepository->get($this->order->getQuoteId());
        $totals = $quote->getTotals();
        $initialFeeObject = $totals['nn_initial_fee']->getData();
        $initialFee = $initialFeeObject['value'];
        $this->source = $parent->getSource();
        $store = $this->getStore();
        if ($initialFee) {
            $charges = new \Magento\Framework\DataObject(
                [
                    'code' => 'initial_fee',
                    'strong' => false,
                    'value' => $initialFee,
                    'label' => __('Initial Fee'),
                ]
            );
            $parent->addTotal($charges, 'initial_fee');
            $parent->addTotal($charges, 'initial_fee');
        }
        return $this;
    }
}
