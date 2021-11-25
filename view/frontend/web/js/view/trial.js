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
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        var NAME_SEGMENT = 'nn_trial_payment';
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Novalnet_Subscription/totals/summary'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            /**
             * @returns {Boolean}
             */
            isDisplayed: function () {
                var trialPayment = totals.getSegment(NAME_SEGMENT);

                if (this.totals() && trialPayment !== null && trialPayment.area > 0) {
                    return true;
                }

                return false;
            },
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('nn_trial_payment').value;
                }
                return this.getFormattedPrice(price);
            }
        });
    }
);
