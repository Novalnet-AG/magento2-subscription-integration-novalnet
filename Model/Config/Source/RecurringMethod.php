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
namespace Novalnet\Subscription\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RecurringMethod extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray(): array
    {
        $option[] = ['value' => 'subscription_only', 'label' => __('Subscription Only')];
        $option[] = ['value' => 'allow_normal', 'label' => __('Either (Single Purchase or Subscription)')];
        return $option;
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->toOptionArray();
        }

        return $this->_options;
    }
}
