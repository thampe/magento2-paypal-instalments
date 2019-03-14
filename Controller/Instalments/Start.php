<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalInstalments\Controller\Instalments;

class Start extends \Magento\Paypal\Controller\Express\Start
{
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType = \Iways\PayPalInstalments\Model\Config::class;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod = \Iways\PayPalInstalments\Model\Config::METHOD_INSTALMENTS;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType = \Iways\PayPalInstalments\Model\Express\Checkout::class;

    /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        try {
            $token = $this->getToken();
            if ($token === null) {
                return;
            }

            $url = $this->_checkout->getRedirectUrl();
            if ($token && $url) {
                $this->_initToken($token);
                $this->getResponse()->setRedirect($url);

                return;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t start Instalment.')
            );
        }

        $this->_redirect('checkout/cart');
    }
}
