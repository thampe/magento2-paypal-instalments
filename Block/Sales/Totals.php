<?php
namespace Iways\PayPalInstalments\Block\Sales;

/**
 * Class Totals
 * @package Iways_PayPalInstalments
 */
class Totals extends \Magento\Framework\View\Element\Template
{

    protected function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals()
    {
        if(!$this->getSource()->getInstalmentsFeeAmt()) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'instalments_fee_amt',
                'value' => $this->getSource()->getInstalmentsFeeAmt(),
                'label' => __('Instalments Fee'),
                'strong' => true,
                'area' => 'footer'
            ]
        );
        $this->getParentBlock()->addTotal($total, 'grand_total');

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'instalments_fee_amt_total',
                'value' => $this->getSource()->getInstalmentsFeeAmt() + $this->getSource()->getGrandTotal(),
                'label' => __('Instalments Fee Total'),
                'strong' => true,
                'area' => 'footer'
            ]
        );
        $this->getParentBlock()->addTotal($total, 'instalments_fee_amt');

        return $this;
    }
}
