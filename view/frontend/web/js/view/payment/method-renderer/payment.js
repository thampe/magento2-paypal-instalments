/*browser:true*/
/*global define*/
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Catalog/js/price-utils',
        'mage/translate'
    ],
    function ($, Component, setPaymentMethodAction, additionalValidators, quote, customerData, priceUtils) {
        return Component.extend({
            defaults: {
                template: 'Iways_PayPalInstalments/payment',
            },
            /** Redirect to paypal */
            continueToPayPal: function () {
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.iways_paypalinstalments_payment.redirectUrl
                            );
                        }
                    );

                    return false;
                }
            },
            getInstallmentData: function () {
                var financeInformation = window.checkoutConfig.payment.iways_paypalinstalments_payment.upstreamData;
                var lender = window.checkoutConfig.payment.iways_paypalinstalments_payment.lender;
                var cartAmount = window.checkoutConfig.payment.iways_paypalinstalments_payment.cartAmount;
                var translateInterval = {
                    "MONTHS": $.mage.__("monthly"),
                    "DAYS": $.mage.__("daily"),
                    "WEEKS": $.mage.__("weekly"),
                    "YEARS": $.mage.__("yearly")
                };
                var html = "";
                if(financeInformation !== "hide"){
                    if(financeInformation){
                        if(window.checkoutConfig.payment.iways_paypalinstalments_payment.isSpecific == 1){
                            html +=
                                '<div class="specific-pp-installment checkout-version">' +
                                    '<p class="installment-head">' +
                                        $.mage.__("Financing from %1 in %2 %3 installments with installments Powered by PayPal")
                                            .replace("%1", priceUtils.formatPrice(financeInformation[0]["monthly_payment"]["value"]))
                                            .replace("%2", financeInformation[0]["credit_financing"]["term"])
                                            .replace("%3", translateInterval[financeInformation[0]["credit_financing"]["interval"]]) +
                                    '</p>' +
                                    '<span class="small-font">' + $.mage.__("Representative example according to § 6a PAngV") + ':</span>' +
                                    '<table class="small-font">' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("Net loan amount") + ':</td>' +
                                            '<td>' + priceUtils.formatPrice(cartAmount) + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("Fixed debit interest rate") + ':</td>' +
                                            '<td>' + (Math.round((financeInformation[0]["credit_financing"]["nominal_rate"] * 1000)/10)/100).toFixed(2) + '%</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("Annual percentage rate") + ':</td>' +
                                            '<td>' + financeInformation[0]["credit_financing"]["apr"] + '%</td>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("Total amount to be paid") + ':</td>' +
                                            '<td>' + priceUtils.formatPrice(financeInformation[0]["total_cost"]["value"]) + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("%1 %2 installments of")
                                                        .replace("%1", financeInformation[0]["credit_financing"]["term"])
                                                        .replace("%2", translateInterval[financeInformation[0]["credit_financing"]["interval"]]) +
                                            ':</td>' +
                                            '<td>' + priceUtils.formatPrice(financeInformation[0]["monthly_payment"]["value"]) + '</td>' +
                                        '</tr>' +
                                    '</table>' +
                                    '<p class="small-font">' + $.mage.__("Lender") + ": " + lender + '</p>' +
                                    '<a onclick="showPPOverlay()" id="show-installments-overlay">' + $.mage.__("Information on possible installments") + '</a>' +
                                '</div>';
                        }else{
                            html +=
                                '<div class="specific-pp-installment align-center checkout-version">' +
                                    '<p>' + $.mage.__("You can also finance this cart!") + '</p>' +
                                    '<a onclick="showPPOverlay()" id="show-installments-overlay">' + $.mage.__("Information on possible installments") + '</a>'+
                                '</div>';
                        }

                        html +=
                            '<div id="pp-installments-overlay" style="display: none;">' +
                                '<a onclick="hidePPOverlay()" id="close-button">x</a>' +
                                '<img class="paypal-installments-image" src="' + window.checkoutConfig.payment.iways_paypalinstalments_payment.ppImageUrl + '" alt="paypal-icon">' +
                                '<p class="bigger-font">' + $.mage.__("Pay conveniently and easily in monthly installments") + '</p>' +
                                '<p>' + $.mage.__("You can select your installment payment and the appropriate financing plan during the ordering process. Your application is completely online and can be completed here in the shop in just a few steps.") + '</p>' +
                                '<p class="bigger-font">' + $.mage.__("Net loan amount") + ": " +
                                    priceUtils.formatPrice(cartAmount) + '</p>' +
                                '<div class="paypal-options-wrapper">';

                        $.each(financeInformation, function (index, financeOption) {
                            var star = "";
                            if(index === 0) star = "*";
                            html +=
                                '<div class="paypal-option option-col-' + financeInformation.length + '">' +
                                    '<div class="paypal-option-content">' +
                                        '<p class="big-font"><strong>' + $.mage.__("Plan") + " " + (index + 1) + star + '</strong></p>' +
                                        '<p>' + $.mage.__("%1 %2 installments of")
                                                    .replace("%1", financeOption["credit_financing"]["term"])
                                                    .replace("%2", translateInterval[financeOption["credit_financing"]["interval"]]) +
                                        ':</p>' +
                                        '<table>' +
                                            '<tr>' +
                                                '<td><p></p></td>' +
                                                '<td><p class="big-font"><strong>' + priceUtils.formatPrice(financeOption["monthly_payment"]["value"]) + '</strong></p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("Fixed debit interest rate") + ':</p></td>' +
                                                '<td><p>' + (Math.round((financeOption["credit_financing"]["nominal_rate"] * 1000)/10)/100).toFixed(2) + '%</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("Annual percentage rate") + ':</p></td>' +
                                                '<td><p>' + financeOption["credit_financing"]["apr"] + '%</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("Interest amount") + ':</p></td>' +
                                                '<td><p>' + priceUtils.formatPrice(financeOption["total_interest"]["value"]) + '</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p><strong>' + $.mage.__("Total") + ':</strong></p></td>' +
                                                '<td><p><strong>' + priceUtils.formatPrice(financeOption["total_cost"]["value"]) + '</strong></p></td>' +
                                            '</tr>' +
                                        '</table>' +
                                    '</div>' +
                                '</div>';
                        });

                        html +=
                            '</div>' +
                            '<p>' + $.mage.__("* At the same time representative example according to § 6a PAngV") + '</p>' +
                            '<p>' + $.mage.__("Lender") + ": " + lender + '</p>' +
                        '</div>' +
                        '<script type="text/javascript">' +
                            'function showPPOverlay() {' +
                                'document.getElementById("pp-installments-overlay").style.display = "block";' +
                            '}' +
                            'function hidePPOverlay() {' +
                                'document.getElementById("pp-installments-overlay").style.display = "none";' +
                            '}' +
                        '</script>';
                    }else{
                        html +=
                            '<div class="specific-pp-installment align-center checkout-version">' +
                                '<p>' + $.mage.__("Financing available from %1 to %2 cart value")
                                        .replace("%1", priceUtils.formatPrice(99))
                                        .replace("%2", priceUtils.formatPrice(5000)) +
                                '</p>' +
                            '</div>'
                    }
                }
                $('#paypal-installment-hook').append(html);
            }
        });
    }
);