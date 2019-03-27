<?php
/**
 * Created by PhpStorm.
 * User: gero
 * Date: 26.03.19
 * Time: 17:02
 */

namespace Iways\PayPalInstalments\Block;

use Magento\Framework\View\Element\Template;

class Upstream extends Template
{
    protected $_scopeConfig;
    protected $rest;
    protected $objectManager;
    protected $financeInformation = array();

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iways\PayPalInstalments\Model\Api\Rest $rest,
        \Magento\Framework\App\ObjectManager $objectManager,
        array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->rest = $rest;
        $this->objectManager = $objectManager;
        return parent::__construct($context, $data);
    }

    public function getGenericConfig()
    {
        $display = false;
        $view = $this->_request->getFullActionName();
        $data = $this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments");
        if($view == 'cms_index_index'){
            if($data['generic_upstream_homepage']){
                $display = true;
            }
        }elseif($view == 'catalog_category_view'){
            if($data['generic_upstream_category']){
                $display = true;
            }
        }
        return $display;
    }

    public function getFinanceInformation($amount = false)
    {
        if($amount === false){
            $objectManager = $this->objectManager::getInstance();
            $cart = $objectManager->get('\Magento\Checkou\Model\Cart');
            $amount = $cart->getQuote()->getGrandTotal();
        }
        if(!isset($this->financeInformation[$amount])){
            $this->financeInformation[$amount] = $this->rest->getFinanceInfo($amount);
        }
        return $this->financeInformation[$amount];
    }

    public function getFinanceInformationForCurrentProduct()
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_product")){
            return $this->getFinanceInformation();
        }
        return false;
    }

    public function getFinanceInformationForCart()
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_cart")){
            return $this->getFinanceInformation();
        }
        return false;
    }

    public function getQualifyingFinancingOptionsForPaymentMethod($amount = false)
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_payment_method")){
            return $this->getQualifyingFinancingOptions($amount);
        }
        return false;
    }

    public function getQualifyingFinancingOptions($amount = false)
    {
        $financeInformation = $this->getFinanceInformation($amount);
        if(isset($financeInformation->financing_options[0]->qualifying_financing_options[0])){
            return $financeInformation->financing_options[0]->qualifying_financing_options[0];
        }
        return false;
    }
}