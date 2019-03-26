<?php
/**
 * Created by PhpStorm.
 * User: robert
 * Date: 2019-03-26
 * Time: 16:45
 */

namespace Iways\PayPalInstalments\Model\Sales\Pdf;


class Fee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    public function getTotalsForDisplay()
    {
        if(!$this->getOrder()->getInstalmentsFeeAmt()) {
            return [];
        }
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $totals = [
            [
                'amount' => $this->getOrder()->formatPriceTxt($this->getOrder()->getInstalmentsFeeAmt()),
                'label' => __('Instalments Fee'),
                'font_size' => $fontSize
            ],
            [
                'amount' => $this->getOrder()->formatPriceTxt($this->getOrder()->getInstalmentsFeeAmt() + $this->getOrder()->getGrandTotal()),
                'label' => __('Instalments Fee Total'),
                'font_size' => $fontSize
            ],
        ];
        return $totals;
    }
}