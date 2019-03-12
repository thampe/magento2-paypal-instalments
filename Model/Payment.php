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


/**
 * Class Payment
 *
 * @package Iways\PayPalInstalments\Model
 */
class Payment extends \Magento\PayPal\Model\Express
{
    const CODE = 'iways_paypalinstalments_payment';

    protected $_code = self::CODE;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'iways_paypalinstalments/pro';
    /**
     * Payment Method features
     * @var bool
     */
    protected $_canManageRecurringProfiles = false;

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see \Magento\Checkout\Controller\Onepage::savePaymentAction()
     * @see Quote\Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('instalments/instalments/start');
    }
}