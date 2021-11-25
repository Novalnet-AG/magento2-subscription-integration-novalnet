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
namespace Novalnet\Subscription\Plugin;

class ProductList
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;
    
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;
    
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->layout          = $layout;
        $this->assetRepo       = $assetRepo;
        $this->productloader   = $productloader;
    }

    /**
     * Display Subscription logo
     *
     * @param Magento\Catalog\Block\Product\ListProduct $subject
     * @param Closure $proceed
     * @param Magento\Catalog\Model\Product $product
     * @return int|void|null
     */
    public function aroundGetProductDetailsHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getId()) {
            $product = $this->productloader->create()->load($product->getId());
            if ($product->getNovalnetSubEnabled() == 1) {
                $params = ['_secure' => true];
                $asset = $this->assetRepo->createAsset(
                    'Novalnet_Subscription::images/recurring_icon.png',
                    $params
                );
                $logoSrc = $asset->getUrl();
                return '<img class="nnsubscription-label" src="'.$logoSrc.'" style="position: absolute;bottom: 0;right:0;width: 40px;z-index: 999999;">';
            }
        }
    }
}
