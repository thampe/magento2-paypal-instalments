<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * Created on 02.03.2015
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 */
namespace Iways\PaypalInstalments\Model\Express;
/**
 * Wrapper that performs Paypal Express and Checkout communication
 * Use current Paypal Express method instance
 *
 * @category   Iways
 * @package    Iways_PaypalInstalments
 * @author robert
 */
class Checkout extends \Magento\Paypal\Model\Express\Checkout
{
    const INSTALMENTS_FEE_AMT = 'instalments_fee_amt';
    const INSTALMENTS_TOTAL_COST = 'instalments_total_cost';
    const INSTALMENTS_MONTHLY_PAYMENT = 'instalments_monthly_payment';
    const INSTALMENTS_IS_FINANCING = 'instalments_is_financing';
    const INSTALMENTS_TERM = 'instalments_term';

    /**
     * Flag for Bill Me Later mode
     *
     * @var bool
     */
    protected $_isBml = false;

    /**
     * Flag which says that was used PayPal Express Checkout button for checkout
     * Uses additional_information as storage
     * @var string
     */
    const PAYMENT_INFO_BUTTON = 'button';

    /**
     * Api Model Type
     *
     * @var string
     */
    protected $_apiType = 'iways_paypalinstalments/api_nvp';

    /**
     * Payment method type
     *
     * @var unknown_type
     */
    protected $_methodType = \Iways\PaypalInstalments\Model\Config::METHOD_INSTALMENTS;

    /**
     * Update quote when returned from PayPal
     * rewrite billing address by paypal
     * save old billing address for new customer
     * export shipping address in case address absence
     * add instalment information to payment additional information
     *
     * @param string $token
     */
    public function returnFromPaypal($token)
    {
        $this->_getApi();
        $this->_api->setToken($token)
            ->callGetExpressCheckoutDetails();
        $quote = $this->_quote;

        $this->_ignoreAddressValidation();

        // import shipping address
        $exportedShippingAddress = $this->_api->getExportedShippingAddress();
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                if ($exportedShippingAddress) {
                    $this->_setExportedAddressData($shippingAddress, $exportedShippingAddress);

                    if ($quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1) {
                        // PayPal doesn't provide detailed shipping info: prefix, middlename, lastname, suffix
                        $shippingAddress->setPrefix(null);
                        $shippingAddress->setMiddlename(null);
                        $shippingAddress->setLastname(null);
                        $shippingAddress->setSuffix(null);
                    }

                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->setSameAsBilling(0);
                }

                // import shipping method
                $code = '';
                if ($this->_api->getShippingRateCode()) {
                    if ($code = $this->_matchShippingMethodCode($shippingAddress, $this->_api->getShippingRateCode())) {
                        // possible bug of double collecting rates :-/
                        $shippingAddress->setShippingMethod($code)->setCollectShippingRates(true);
                    }
                }
                $quote->getPayment()->setAdditionalInformation(
                    self::PAYMENT_INFO_TRANSPORT_SHIPPING_METHOD,
                    $code
                );
            }
        }

        // import billing address
        $portBillingFromShipping = $quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON) == 1
            && $this->_config->requireBillingAddress != \Magento\Paypal\Model\Config::REQUIRE_BILLING_ADDRESS_ALL
            && !$quote->isVirtual();
        if ($portBillingFromShipping) {
            $billingAddress = clone $shippingAddress;
            $billingAddress->unsAddressId()
                ->unsAddressType();
            $data = $billingAddress->getData();
            $data['save_in_address_book'] = 0;
            $quote->getBillingAddress()->addData($data);
            $quote->getShippingAddress()->setSameAsBilling(1);
        } else {
            $billingAddress = $quote->getBillingAddress();
        }
        $exportedBillingAddress = $this->_api->getExportedBillingAddress();
        $this->_setExportedAddressData($billingAddress, $exportedBillingAddress);
        $billingAddress->setCustomerNotes($exportedBillingAddress->getData('note'));
        $quote->setBillingAddress($billingAddress);
        $quote->setInstalmentsFeeAmt($this->_api->getData(self::INSTALMENTS_FEE_AMT));
        $quote->setBaseInstalmentsFeeAmt($this->_api->getData(self::INSTALMENTS_FEE_AMT));
        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        //@TODO Refactor model load
        //Mage::getSingleton('paypal/info')->importToPayment($this->_api, $payment);
        $payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_api->getPayerId())
            ->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token)
            ->setAdditionalInformation(self::INSTALMENTS_FEE_AMT, $this->_api->getData(self::INSTALMENTS_FEE_AMT))
            ->setAdditionalInformation(self::INSTALMENTS_TOTAL_COST, $this->_api->getData(self::INSTALMENTS_TOTAL_COST))
            ->setAdditionalInformation(self::INSTALMENTS_TERM, $this->_api->getData(self::INSTALMENTS_TERM))
            ->setAdditionalInformation(self::INSTALMENTS_MONTHLY_PAYMENT,
                $this->_api->getData(self::INSTALMENTS_MONTHLY_PAYMENT))
            ->setAdditionalInformation(self::INSTALMENTS_IS_FINANCING,
                $this->_api->getData(self::INSTALMENTS_IS_FINANCING));
        $quote->collectTotals()->save();
    }

    /**
     * Make sure addresses will be saved without validation errors
     */
    private function _ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_config->requireBillingAddress && !$this->_quote->getBillingAddress()->getEmail()) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }

    /**
     * Set flag that forces to use BillMeLater
     *
     * @param bool $isBml
     */
    public function setIsBml($isBml)
    {
        $this->_isBml = $isBml;
    }

    /**
     * Check whether system can skip order review page before placing order
     *
     * @return bool
     */
    public function canSkipOrderReviewStep()
    {
        return false;
    }

    /**
     * Reserve order ID for specified quote and start checkout on PayPal
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param bool|null $button
     * @return mixed
     */
    public function start($returnUrl, $cancelUrl, $button = null)
    {
        $this->_quote->collectTotals();

        if (!$this->_quote->getGrandTotal() && !$this->_quote->hasNominalItems()) {
            Mage::throwException(Mage::helper('paypal')->__('PayPal does not support processing orders with zero amount. To complete your purchase, proceed to the standard checkout process.'));
        }

        $this->_quote->reserveOrderId()->save();
        // prepare API
        $this->_getApi();
        $solutionType = $this->_config->getMerchantCountry() == 'DE'
            ? \Magento\Paypal\Model\Config::EC_SOLUTION_TYPE_MARK : $this->_config->solutionType;
        $this->_api->setAmount($this->_quote->getBaseGrandTotal())
            ->setCurrencyCode($this->_quote->getBaseCurrencyCode())
            ->setInvNum($this->_quote->getReservedOrderId())
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl)
            ->setSolutionType($solutionType)
            ->setPaymentAction($this->_config->paymentAction);

        if ($this->_giropayUrls) {
            list($successUrl, $cancelUrl, $pendingUrl) = $this->_giropayUrls;
            $this->_api->addData(array(
                'giropay_cancel_url' => $cancelUrl,
                'giropay_success_url' => $successUrl,
                'giropay_bank_txn_pending_url' => $pendingUrl,
            ));
        }

        if ($this->_isBml) {
            $this->_api->setFundingSource('BML');
        }

        $this->_setBillingAgreementRequest();

        if ($this->_config->requireBillingAddress == \Magento\Paypal\Model\Config::REQUIRE_BILLING_ADDRESS_ALL) {
            $this->_api->setRequireBillingAddress(1);
        }

        // supress or export shipping address
        if ($this->_quote->getIsVirtual()) {
            if ($this->_config->requireBillingAddress == \Magento\Paypal\Model\Config::REQUIRE_BILLING_ADDRESS_VIRTUAL) {
                $this->_api->setRequireBillingAddress(1);
            }
            $this->_api->setSuppressShipping(true);
        } else {
            $address = $this->_quote->getShippingAddress();
            $isOverriden = 0;
            if (true === $address->validate()) {
                $isOverriden = 1;
                $this->_api->setAddress($address);
            }
            $this->_quote->getPayment()->setAdditionalInformation(
                self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN, $isOverriden
            );
            $this->_quote->getPayment()->save();
        }

        // add line items
        $paypalCart = Mage::getModel('iways_paypalinstalments/cart', array($this->_quote));
        $this->_api->setPaypalCart($paypalCart)
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
        ;

        // add recurring payment profiles information
        if ($profiles = $this->_quote->prepareRecurringPaymentProfiles()) {
            foreach ($profiles as $profile) {
                $profile->setMethodCode(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS);
                if (!$profile->isValid()) {
                    Mage::throwException($profile->getValidationErrors(true, true));
                }
            }
            $this->_api->addRecurringPaymentProfiles($profiles);
        }

        $this->_config->exportExpressCheckoutStyleSettings($this->_api);

        // call API and redirect with token
        $this->_api->callSetExpressCheckout();
        $token = $this->_api->getToken();
        $this->_redirectUrl = $button ? $this->_config->getExpressCheckoutStartUrl($token)
            : $this->_config->getPayPalBasicStartUrl($token);

        $this->_quote->getPayment()->unsAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);

        // Set flag that we came from Express Checkout button
        if (!empty($button)) {
            $this->_quote->getPayment()->setAdditionalInformation(self::PAYMENT_INFO_BUTTON, 1);
        } elseif ($this->_quote->getPayment()->hasAdditionalInformation(self::PAYMENT_INFO_BUTTON)) {
            $this->_quote->getPayment()->unsAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        }

        $this->_quote->getPayment()->save();
        return $token;
    }
}
