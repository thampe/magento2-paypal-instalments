<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-18
 * Time: 12:09
 */

namespace Iways\PayPalInstalments\Model\Order\Invoice\Total;


class Fee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        parent::collect($invoice);
        $order = $invoice->getOrder();

        $invoice->setInstalmentsFee($order->getInstalmentsFee());
        $invoice->setBaseInstalmentsFee($order->getBaseInstalmentsFee());
        $invoice->setInstalmentsFeeTotal($order->getInstalmentsFeeTotal());
        $invoice->setBaseInstalmentsFeeTotal($order->getBaseInstalmentsFeeTotal());
        return $this;
    }
}