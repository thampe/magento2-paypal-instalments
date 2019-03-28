<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalInstalments\Controller\Instalments;
use Magento\Framework\Controller\ResultFactory;

class ReturnAction extends \Magento\Paypal\Controller\Express\ReturnAction
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
     * Return from PayPal and dispatch customer to order review page
     *
     * @return void|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if ($this->getRequest()->getParam('retry_authorization') == 'true'
            && is_array($this->_getCheckoutSession()->getPaypalTransactionData())
        ) {
            $this->_forward('placeOrder');
            return;
        }
        try {
            $this->_getCheckoutSession()->unsPaypalTransactionData();
            $this->_initCheckout();
            $this->_checkout->returnFromPaypalInstalments($this->_initToken());
            $this->_redirect('*/*/review');
            return;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t process Instalment approval.')
            );
        }
        $this->_getCheckoutSession()->getQuote()->setInstalmentsFee(null)->setBaseInstalmentsFee(null)->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
