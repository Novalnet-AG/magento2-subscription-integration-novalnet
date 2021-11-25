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
namespace Novalnet\Subscription\Block\Adminhtml\Profile;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Recurring profile view block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Novalnet_Subscription';
        $this->_controller = 'adminhtml_profile';
        $this->_mode = 'view';
        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('cancel');
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Subscription Profile');
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'content');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
