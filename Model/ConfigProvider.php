<?php
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

namespace Iways\PayPalInstalments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Iways\PayPalInstalments\Block\Upstream as Upstream;
use Psr\Log\LoggerInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Payment::CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    protected $upstream;

    /**
     * ConfigProvider constructor.
     * @param PaymentHelper $paymentHelper
     * @param Upstream upstream
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Upstream $upstream
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->upstream = $upstream;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'iways_paypalinstalments_payment' => [
                    'redirectUrl' => $this->method->getCheckoutRedirectUrl(),
                    'upstreamData' => $this->upstream->getFinanceInformationForCart(),
                    'cartAmount' => $this->upstream->getCartTotal(),
                    'isSpecific' => $this->upstream->isSpecific(),
                    'lender' => $this->upstream->getLender(),
                    'currencyCode' => $this->upstream->getCurrencyCode(),
                    'ppImageUrl' => $this->upstream->getPPImageUrl()
                ]
            ],
        ] : [];
    }
}