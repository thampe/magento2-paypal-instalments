<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalInstalments\Controller\Instalments;
use Magento\Framework\Controller\ResultFactory;

class Cancel extends \Magento\Paypal\Controller\Express\Cancel
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
     * Cancel Express Checkout
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_initToken(false);
            // TODO verify if this logic of order cancellation is deprecated
            // if there is an order - cancel it
            $orderId = $this->_getCheckoutSession()->getLastOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;
            if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
                $order->cancel()->save();
                $this->_getCheckoutSession()
                    ->unsLastQuoteId()
                    ->unsLastSuccessQuoteId()
                    ->unsLastOrderId()
                    ->unsLastRealOrderId();
                $this->messageManager->addSuccessMessage(
                    __('Instalment and Order have been canceled.')
                );
            } else {
                $this->messageManager->addSuccessMessage(
                    __('Instalment has been canceled.')
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to cancel Instalment'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
