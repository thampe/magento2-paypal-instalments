<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Iways\PayPalInstalments\Controller\Instalments;

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
}
