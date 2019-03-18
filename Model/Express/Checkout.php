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
use Iways\PayPalInstalments\Model\Api\Nvp;
use Iways\PayPalInstalments\Model\Cart as PaypalCart;
use Iways\PayPalInstalments\Model\Config as PaypalConfig;
use Magento\Quote\Model\Quote\Address;
use Magento\Customer\Model\AccountManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\DataObject;

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
    const INSTALMENTS_FEE = 'instalments_fee_amt';
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
    protected $_apiType = Nvp::class;

    /**
     * Payment method type
     *
     * @var unknown_type
     */
    protected $_methodType = \Iways\PaypalInstalments\Model\Config::METHOD_INSTALMENTS;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Paypal\Model\Info $paypalInfo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $coreUrl
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory
     * @param \Magento\Quote\Api\CartManagementInterface $quoteManagement
     * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
     * @param \Magento\Paypal\Model\Api\Type\Factory $apiTypeFactory
     * @param DataObject\Copy $objectCopyService
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param AccountManagement $accountManagement
     * @param OrderSender $orderSender
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $params
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Paypal\Model\Info $paypalInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $coreUrl,
        \Iways\PayPalInstalments\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Paypal\Model\Api\Type\Factory $apiTypeFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AccountManagement $accountManagement,
        OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        $params = []
    ) {
        parent::__construct(
            $logger,
            $customerUrl,
            $taxData,
            $checkoutData,
            $customerSession,
            $configCacheType,
            $localeResolver,
            $paypalInfo,
            $storeManager,
            $coreUrl,
            $cartFactory,
            $onepageFactory,
            $quoteManagement,
            $agreementFactory,
            $apiTypeFactory,
            $objectCopyService,
            $checkoutSession,
            $encryptor,
            $messageManager,
            $customerRepository,
            $accountManagement,
            $orderSender,
            $quoteRepository,
            $totalsCollector,
            $params
        );
    }

    /**
     * Update quote when returned from PayPal
     *
     * Rewrite billing address by paypal, save old billing address for new customer, and
     * export shipping address in case address absence
     *
     * @param string $token
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function returnFromPaypal($token)
    {
        $this->_getApi()
            ->setToken($token)
            ->callGetExpressCheckoutDetails();
        $quote = $this->_quote;

        $this->ignoreAddressValidation();

        // check if we came from the Express Checkout button
        $isButton = (bool)$quote->getPayment()->getAdditionalInformation(self::PAYMENT_INFO_BUTTON);

        // import shipping address
        $exportedShippingAddress = $this->_getApi()->getExportedShippingAddress();
        if (!$quote->getIsVirtual()) {
            $shippingAddress = $quote->getShippingAddress();
            if ($shippingAddress) {
                if ($exportedShippingAddress && $isButton) {
                    $this->_setExportedAddressData($shippingAddress, $exportedShippingAddress);
                    // PayPal doesn't provide detailed shipping info: prefix, middlename, lastname, suffix
                    $shippingAddress->setPrefix(null);
                    $shippingAddress->setMiddlename(null);
                    $shippingAddress->setLastname(null);
                    $shippingAddress->setSuffix(null);
                    $shippingAddress->setCollectShippingRates(true);
                    $shippingAddress->setSameAsBilling(0);
                }

                // import shipping method
                $code = '';
                if ($this->_getApi()->getShippingRateCode()) {
                    $code = $this->_matchShippingMethodCode($shippingAddress, $this->_getApi()->getShippingRateCode());
                    if ($code) {
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
        $requireBillingAddress = (int)$this->_config->getValue(
                'requireBillingAddress'
            ) === \Magento\Paypal\Model\Config::REQUIRE_BILLING_ADDRESS_ALL;

        if ($isButton && !$requireBillingAddress && !$quote->isVirtual()) {
            $billingAddress = clone $shippingAddress;
            $billingAddress->unsAddressId()->unsAddressType()->setCustomerAddressId(null);
            $data = $billingAddress->getData();
            $data['save_in_address_book'] = 0;
            $quote->getBillingAddress()->addData($data);
            $quote->getShippingAddress()->setSameAsBilling(1);
        } else {
            $billingAddress = $quote->getBillingAddress()->setCustomerAddressId(null);
        }
        $exportedBillingAddress = $this->_getApi()->getExportedBillingAddress();

        // Since country is required field for billing and shipping address,
        // we consider the address information to be empty if country is empty.
        $isEmptyAddress = ($billingAddress->getCountryId() === null);

        if ($requireBillingAddress || $isEmptyAddress) {
            $this->_setExportedAddressData($billingAddress, $exportedBillingAddress);
        }
        $billingAddress->setCustomerNote($exportedBillingAddress->getData('note'));
        $quote->setBillingAddress($billingAddress);
        $quote->setCheckoutMethod($this->getCheckoutMethod());

        // import payment info
        $payment = $quote->getPayment();
        $payment->setMethod($this->_methodType);
        $this->_paypalInfo->importToPayment($this->_getApi(), $payment);
        $payment->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_PAYER_ID, $this->_getApi()->getPayerId())
            ->setAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_TOKEN, $token)
            ->setAdditionalInformation(self::INSTALMENTS_FEE_AMT, $this->_api->getData(self::INSTALMENTS_FEE_AMT))
            ->setAdditionalInformation(self::INSTALMENTS_TOTAL_COST, $this->_api->getData(self::INSTALMENTS_TOTAL_COST))
            ->setAdditionalInformation(self::INSTALMENTS_TERM, $this->_api->getData(self::INSTALMENTS_TERM))
            ->setAdditionalInformation(self::INSTALMENTS_MONTHLY_PAYMENT,
                $this->_api->getData(self::INSTALMENTS_MONTHLY_PAYMENT))
            ->setAdditionalInformation(self::INSTALMENTS_IS_FINANCING,
                $this->_api->getData(self::INSTALMENTS_IS_FINANCING));;
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
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
     * Set shipping options to api
     *
     * @param \Magento\Paypal\Model\Cart $cart
     * @param \Magento\Quote\Model\Quote\Address|null $address
     * @return void
     */
    private function setShippingOptions(PaypalCart $cart, Address $address = null)
    {
        // for included tax always disable line items (related to paypal amount rounding problem)
        $this->_getApi()->setIsLineItemsEnabled($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS));

        // add shipping options if needed and line items are available
        $cartItems = $cart->getAllItems();
        if ($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS)
            && $this->_config->getValue(PaypalConfig::TRANSFER_SHIPPING_OPTIONS)
            && !empty($cartItems)
        ) {
            if (!$this->_quote->getIsVirtual()) {
                $options = $this->_prepareShippingOptions($address, true);
                if ($options) {
                    $this->_getApi()->setShippingOptionsCallbackUrl(
                        $this->_coreUrl->getUrl(
                            '*/*/shippingOptionsCallback',
                            ['quote_id' => $this->_quote->getId()]
                        )
                    )->setShippingOptions($options);
                }
            }
        }
    }

    /**
     * Reserve order ID for specified quote and start checkout on PayPal
     *
     * @param string $returnUrl
     * @param string $cancelUrl
     * @param bool|null $button
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function start($returnUrl, $cancelUrl, $button = null)
    {
        $this->_quote->collectTotals();

        if (!$this->_quote->getGrandTotal()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'PayPal can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
        }

        $this->_quote->reserveOrderId();
        $this->quoteRepository->save($this->_quote);
        // prepare API
        $solutionType = $this->_config->getMerchantCountry() == 'DE'
            ? \Magento\Paypal\Model\Config::EC_SOLUTION_TYPE_MARK
            : $this->_config->getValue('solutionType');
        $totalAmount = round($this->_quote->getBaseGrandTotal(), 2);
        $this->_getApi()->setAmount($totalAmount)
            ->setCurrencyCode($this->_quote->getBaseCurrencyCode())
            ->setInvNum('5'.$this->_quote->getReservedOrderId())
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl)
            ->setSolutionType($solutionType)
            ->setPaymentAction($this->_config->getValue('paymentAction'));


        $this->_getApi()->setRequireBillingAddress(1);

        // suppress or export shipping address
        $address = null;
        if ($this->_quote->getIsVirtual()) {
            if ($this->_config->getValue('requireBillingAddress')
                == PaypalConfig::REQUIRE_BILLING_ADDRESS_VIRTUAL
            ) {
                $this->_getApi()->setRequireBillingAddress(1);
            }
            $this->_getApi()->setSuppressShipping(true);
        } else {
            $this->_getApi()->setBillingAddress($this->_quote->getBillingAddress());

            $address = $this->_quote->getShippingAddress();
            $isOverridden = 0;
            if (true === $address->validate()) {
                $isOverridden = 1;
                $this->_getApi()->setAddress($address);
            }
            $this->_quote->getPayment()->setAdditionalInformation(
                self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN,
                $isOverridden
            );
            $this->_quote->getPayment()->save();
        }

        /** @var $cart \Magento\Payment\Model\Cart */
        $cart = $this->_cartFactory->create(['salesModel' => $this->_quote]);

        $this->_getApi()->setPaypalCart($cart);

        if (!$this->_taxData->getConfig()->priceIncludesTax()) {
            $this->setShippingOptions($cart, $address);
        }

        $this->_config->exportExpressCheckoutStyleSettings($this->_getApi());

        /* Temporary solution. @TODO: do not pass quote into Nvp model */
        $this->_getApi()->setQuote($this->_quote);
        $this->_getApi()->callSetExpressCheckout();

        $token = $this->_getApi()->getToken();

        $this->_setRedirectUrl($button, $token);

        $payment = $this->_quote->getPayment();
        $payment->unsAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
        // Set flag that we came from Express Checkout button
        if (!empty($button)) {
            $payment->setAdditionalInformation(self::PAYMENT_INFO_BUTTON, 1);
        } elseif ($payment->hasAdditionalInformation(self::PAYMENT_INFO_BUTTON)) {
            $payment->unsAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        }
        $payment->save();

        return $token;
    }

    /**
     * Make sure addresses will be saved without validation errors
     *
     * @return void
     */
    private function ignoreAddressValidation()
    {
        $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
        if (!$this->_quote->getIsVirtual()) {
            $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
            if (!$this->_config->getValue('requireBillingAddress')
                && !$this->_quote->getBillingAddress()->getEmail()
            ) {
                $this->_quote->getBillingAddress()->setSameAsBilling(1);
            }
        }
    }
}
