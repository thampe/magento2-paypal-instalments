<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-18
 * Time: 11:59
 */

namespace Iways\PayPalInstalments\Model\Quote\Address\Total;


class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|bool
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);
        $total->addTotalAmount('instalments_fee', $quote->getInstalmentsFee());
        $total->addBaseTotalAmount('instalments_fee', $quote->getBaseInstalmentsFee());
        $total->setGrandTotal($total->getGrandTotal() + $quote->getInstalmentsFee());
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $quote->getBaseInstalmentsFee());
        return $this;
    }
}