<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-26
 * Time: 13:52
 */

namespace Iways\PayPalInstalments\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class QuoteSubmit implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $quote = $observer->getQuote();

        $order->setInstalmentsFeeAmt($quote->getInstalmentsFeeAmt());
        $order->setBaseInstalmentsFeeAmt($quote->getBaseInstalmentsFeeAmt());
    }
}