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
        'mage/translate'
    ],
    function ($, Component, setPaymentMethodAction, additionalValidators, quote, customerData) {
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
                var lender = window.checkoutConfig.payment.iways_paypalinstalments_lender;
                console.log(financeInformation);
                var html = "";
                if(financeInformation !== "hide"){
                    if(financeInformation){
                        if(window.checkoutConfig.payment.iways_paypalinstalments_payment.isSpecific){
                            html +=
                                '<div class="specific-pp-installment">' +
                                    '<p class="installment-head">' +
                                        $.mage.__("Finanzierung ab %1 in %2 %3 Raten mit Ratenzahlung Powered by PayPal")
                                            .replace("%1", financeInformation[0]["monthly_payment"]["value"])
                                            .replace("%2", financeInformation[0]["credit_financing"]["term"])
                                            .replace("%3", financeInformation[0]["credit_financing"]["interval"]) +
                                    '</p>' +
                                    '<span class="small-font">' + $.mage.__("Repräsentatives Beispiel gem. $ 6a PAngV:") + '</span>' +
                                    '<table class="small-font">' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("Nettodarlehensbetrag") + '</td>' +
                                            '<td>' + "TODO: ADD NETTO AMOUNT" + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("fester Sollzinssatz:") + '</td>' +
                                            '<td>' + financeInformation[0]["credit_financing"]["nominal_rate"].toFixed(2) + '%</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("effektiver Jahreszins:") + '</td>' +
                                            '<td>' + financeInformation[0]["credit_financing"]["apr"] + '%</td>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("zu zahlender Gesamtbetrag:") + '</td>' +
                                            '<td>' + financeInformation[0]["total_cost"]["value"] + '</td>' +
                                        '</tr>' +
                                        '<tr>' +
                                            '<td>' + $.mage.__("%1 %2 Raten in Höhe von je:")
                                                        .replace("%1", financeInformation[0]["credit_financing"]["term"])
                                                        .replace("%2", financeInformation[0]["credit_financing"]["interval"]) +
                                            '</td>' +
                                        '</tr>' +
                                    '</table>' +
                                    '<p class="small-font">' + $.mage.__("Darlehensgeber:") + " " + lender + '</p>' +
                                    '<a onclick="showPPOverlay()" id="show-installments-overlay">' + $.mage.__("Informationen zu möglichen Raten") + '</a>' +
                                '</div>';
                        }else{
                            html +=
                                '<div class="specific-pp-installment align-center">' +
                                    '<p>' + $.mage.__("Sie können diesen Artikel auch finanzieren!") + '</p>' +
                                    '<a onclick="showPPOverlay()" id="show-installments-overlay">' + $.mage.__("Informationen zu möglichen Raten") + '</a>'+
                                '</div>';
                        }

                        html +=
                            '<div id="pp-installments-overlay" style="display: none;">' +
                                '<a onclick="hidePPOverlay()" id="close-button">x</a>' +
                                '<img class="paypal-installments-image" src="../../../../images/pp_installments.jpg" alt="paypal-icon">' +
                                '<p class="bigger-font">' + $.mage.__("Zahlen Sie bequem und einfach in monatlichen Raten") + '</p>' +
                                '<p>' + $.mage.__("Ihre Ratenzahlung und den passenden Finanzierungsplan können Sie im Rahmen des Bestellprozesses auswählen. Ihr Antrag erfolgt komplett online und wird in wenigen Schritten hier im Shop abgeschlossen.") + '</p>' +
                                '<p class="bigger-font">' + $.mage.__("Nettodarlehensbetrag:") + " " + "TODO: ADD NETTO AMOUNT" + '</p>' +
                                '<div class="paypal-options-wrapper">';

                        $.each(financeInformation, function (index, financeOption) {
                            html +=
                                '<div class="paypal-option option-col-' + financeInformation.length + '">' +
                                    '<div class="paypal-option-content">' +
                                        '<p class="big-font"><strong>' + $.mage.__("Plan") + " " + (index + 1) + (index === 0) ? "*" : ""  + '</strong></p>' +
                                        '<p>' + $.mage.__("%1 %2 Raten in Höhe von je:")
                                                    .replace("%1", financeOption["credit_financing"]["term"])
                                                    .replace("%2", financeOption["credit_financing"]["interval"]) +
                                        '</p>' +
                                        '<table>' +
                                            '<tr>' +
                                                '<td><p></p></td>' +
                                                '<td><p class="big-font"><strong>' + financeOption["monthly_payment"]["value"] + '</strong></p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("fester Sollzinssatz:") + '</p></td>' +
                                                '<td><p>' + financeOption["credit_financing"]["nominal_rate"].toFixed(2) + '%</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("effektiver Jahreszins:") + '</p></td>' +
                                                '<td><p>' + financeOption["credit_financing"]["apr"] + '%</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p>' + $.mage.__("Zinsbetrag:") + '</p></td>' +
                                                '<td><p>' + financeOption["total_interest"]["value"] + '</p></td>' +
                                            '</tr>' +
                                            '<tr>' +
                                                '<td><p><strong>' + $.mage.__("Gesamtbetrag:") + '</strong></p></td>' +
                                                '<td><p><strong>' + financeOption["total_cost"]["value"] + '</strong></p></td>' +
                                            '</tr>' +
                                        '</table>' +
                                    '</div>' +
                                '</div>';
                        });

                        html +=
                            '</div>' +
                            '<p>' + $.mage.__("* Zugleich repräsentatives Beispiel gem. $ 6a PAngV") + '</p>' +
                            '<p>' + $.mage.__("Darlehensgeber:") + " " + lender + '</p>' *
                        '</div>'
                    }else{
                        html +=
                            '<div class="specific-pp-installment align-center">' +
                                '<p>' + $.mage.__("Finanzierung verfügbar ab €99 bis €5.000 Warenkorbwert") + '</p>' +
                            '</div>'
                    }
                }
                $('#addhereguys').append(html);
            }
        });
    }
);