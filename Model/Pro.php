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
namespace Iways\PayPalInstalments\Model;
/**
 * Iways PayPalInstalments Model Instalments
 *
 * @category   Iways
 * @package    Iways_PayPalInstalments
 * @author robert
 */
class Pro extends \Magento\Paypal\Model\Pro
{
    /**
     * API model type
     *
     * @var string
     */
    protected $_apiType = \Iways\PayPalInstalments\Model\Api\Nvp::class;

    /**
     * Config model type
     *
     * @var string
     */
    protected $_configType = \Iways\PayPalInstalments\Model\Config::class;

}