<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-18
 * Time: 11:59
 */

namespace Iways\PayPalInstalments\Model\Quote\Address\Total;


class FeeTotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{

    protected $_code = 'instalments_fee_amt';

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
        $total->addTotalAmount('instalments_fee_amt_total', $quote->getGrandTotal() + $quote->getInstalmentsFeeAmt());
        $total->addBaseTotalAmount('instalments_fee_amt_total', $quote->getBaseGrandTotal() + $quote->getBaseInstalmentsFeeAmt());
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $result = null;
        $amount = $total->getInstalmentsFeeAmtTotalAmount();
        if ($amount != 0) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Instalments Fee Total'),
                'value' => $amount,
                'base_value' => $total->getBaseInstalmentsFeeAmtTotalAmount(),
                'area' => 'footer'
            ];
        }
        return $result;
    }
}