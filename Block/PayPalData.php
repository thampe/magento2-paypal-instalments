<?php
/**
 * Created by PhpStorm.
 * User: gero
 * Date: 26.03.19
 * Time: 17:02
 */

namespace Iways\PayPalInstalments\Block;

use Magento\Framework\View\Element\Template;

class PayPalData extends Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    )
    {
        return parent::__construct($context, $data);
    }

    public function trying()
    {
        return "Hallo Welt! Wie gehts?";
    }
}