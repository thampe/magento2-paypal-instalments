<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-26
 * Time: 14:33
 */

namespace Iways\PayPalInstalments\Block;


class Info extends \Magento\Paypal\Block\Payment\Info
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Paypal\Model\InfoFactory $paypalInfoFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Iways\PayPalInstalments\Model\InfoFactory $paypalInfoFactory,
        array $data = []
    ) {
        $this->_paypalInfoFactory = $paypalInfoFactory;
        parent::__construct($context, $paymentConfig, $paypalInfoFactory, $data);
    }
}