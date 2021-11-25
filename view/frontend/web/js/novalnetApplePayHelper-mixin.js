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
        'jquery'
    ],
    function($) {
    'use strict';

    return function (target) {

        target.initMiniCart = function(miniCartQuote) {
                var self = this,
                    minicartBtn = $('#novalnet_applepay_minicartbtn'),
                    minicartDiv = $('#novalnet_applepay_minicartdiv'),
                    ispaymentPage = ($('#novalnet_applepay_checkoutdiv').length) ? true : false;

                if (NovalnetUtility.isApplePayAllowed() && miniCartQuote.isEnabled && !ispaymentPage && miniCartQuote.total.amount) {
                    NovalnetUtility.setClientKey(miniCartQuote.sheetConfig.clientKey);
                    self.initApplepayButton(miniCartQuote.sheetConfig, minicartBtn, minicartDiv);
                    $('#novalnet_applepay_minicartbtn').off('click').on('click', function() {
                        self.applepayPaymentRequest(miniCartQuote);
                    });
                }
            }

            return target;
        }
});
