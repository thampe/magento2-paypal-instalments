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
namespace Iways\PayPalInstalments\Model\System\Config\Source;
/**
 * Source model for available payment actions
 *
 * @category   Iways
 * @package    Iways_PayPalInstalments
 * @author robert
 */
class PaymentActions
{
    /**
     * @var \Iways\PayPalInstalments\Model\Config
     */
    protected $_config;

    /**
     * PaymentActions constructor.
     * @param \Iways\PayPalInstalments\Model\Config $config
     */
    public function __construct(\Iways\PayPalInstalments\Model\Config $config)
    {
        $this->_config = $config;
    }

    public function toOptionArray()
    {
        return $this->_config->getPaymentActions();
    }
}
