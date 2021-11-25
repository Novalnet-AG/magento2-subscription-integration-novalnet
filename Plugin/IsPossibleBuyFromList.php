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

class IsPossibleBuyFromList
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productloader;

    /**
     * @param \Magento\Catalog\Model\ProductFactory $productloader
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productloader
    ) {
        $this->productloader  = $productloader;
    }

    /**
     * Check possibility buy from list
     *
     * @param object $subject
     * @param boolean $result
     * @param object $product
     * @return $this
     */
    public function afterIsPossibleBuyFromList($subject, $result, $product)
    {
        if ($product->getId()) {
            $product = $this->productloader->create()->load($product->getId());
            if ($product->getNovalnetSubEnabled() == 1) {
                return false;
            }
        }
        return $result;
    }
}
