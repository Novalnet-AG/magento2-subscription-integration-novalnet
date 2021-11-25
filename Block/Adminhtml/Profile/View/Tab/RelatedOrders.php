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
namespace Novalnet\Subscription\Block\Adminhtml\Profile\View\Tab;

class RelatedOrders extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->order         = $order;
        $this->systemStore   = $systemStore;
        $this->pricingHelper = $pricingHelper;
        $this->_coreRegistry = $registry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('related_order_grid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(false);
    }

    /**
     * Generate reset button
     *
     * @return string
     */
    public function getResetFilterButtonHtml()
    {
        return '';
    }

    /**
     * Generate search button
     *
     * @return string
     */
    public function getSearchButtonHtml()
    {
        return '';
    }
    
    /**
     * Return visibility of filter
     *
     * @return boolean
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getFilterVisibility()
    {
        return false;
    }

    /**
     * Prepare Collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getRelatedOrderCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
    
    /**
     * Prepare Columns
     *
     * @return Columns value
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'text',
                'filter' => false,
                'sortable' => false,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'order_id',
            [
                'header' => __('Order'),
                'type' => 'text',
                'index' => 'order_id',
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'date',
            [
                'header' => __('Date'),
                'type' =>'text',
                'index' => 'date',
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
       
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'type' =>'text',
                'filter' => false,
                'sortable' => false,
                'index' => 'customer_name',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        
        $this->addColumn(
            'order_total',
            [
                'header' => __('Order Total'),
                'type' =>'text',
                'filter' => false,
                'sortable' => false,
                'index' => 'order_total',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'type' =>'text',
                'index' => 'status',
                'filter' => false,
                'sortable' => false,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
        return parent::_prepareColumns();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Related orders');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Related orders');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Get Recurring Profile
     *
     * @return object
     */
    public function getProfile()
    {
        return $this->_coreRegistry->registry('recurring_profile');
    }

    /**
     * Get Related Order as array
     *
     * @return array
     */
    public function getRelatedOrders()
    {
        $profile = $this->getProfile();
        $relatedOrderIds = json_decode($profile->getRelatedOrder());
        $relatedOrders = [];
        foreach ($relatedOrderIds as $orderIncrementId) {
            $order = $this->order->loadByIncrementId($orderIncrementId);
            $relatedOrders[$orderIncrementId]['entity_id'] = $order->getEntityId();
            $relatedOrders[$orderIncrementId]['order_id'] = $order->getIncrementId();
            $relatedOrders[$orderIncrementId]['date'] = $order->getCreatedAt();
            $relatedOrders[$orderIncrementId]['customer_name'] = $order->getCustomerName();
            $relatedOrders[$orderIncrementId]['order_total'] = $this->pricingHelper->currency($order->getGrandTotal(), true, false);
            $relatedOrders[$orderIncrementId]['status'] = $order->getStatus();
        }
        return $relatedOrders;
    }

    /**
     * Get Related Order as Collection
     *
     * @return object
     */
    public function getRelatedOrderCollection()
    {
        $collection = $this->collectionFactory->create();
        $items = $this->getRelatedOrders();
        foreach ($items as $item) {
            $varienObject = new \Magento\Framework\DataObject();
            $varienObject->setData($item);
            $collection->addItem($varienObject);
        }
        return $collection;
    }

    /**
     * Get Row Url
     *
     * @param Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'sales/order/view/',
            ['order_id' => $row->getEntityId()]
        );
    }
}
