<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-18
 * Time: 12:09
 */

namespace Iways\PayPalInstalments\Model\Order\Creditmemo\Total;


class Fee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        parent::collect($creditmemo);
        $order = $creditmemo->getOrder();

        $creditmemo->setInstalmentsFee($order->getInstalmentsFee());
        $creditmemo->setBaseInstalmentsFee($order->getBaseInstalmentsFee());
        return $this;
    }
}