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
    protected $financeInformation = array();

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Iways\PayPalInstalments\Model\Api\Rest $rest,
        array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->rest = $rest;
        return parent::__construct($context, $data);
    }

    public function getSiteLocation()
    {
        return $this->_request->getFullActionName();
    }

    public function getGenericConfig()
    {
        $display = false;
        $view = $this->getSiteLocation();
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
            /** price error */
            $amount = 99;
        }
        if(!isset($this->financeInformation[$amount])){
            $this->financeInformation[$amount] = $this->rest->getFinanceInfo($amount);
        }
        return $this->financeInformation[$amount];
    }

    public function getFinanceInformationForCurrentProduct()
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_product")){
            return $this->getQualifyingFinancingOptions($this->getItemPrice(false));
        }
        return "hide";
    }

    public function getFinanceInformationForCart()
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_cart")){
            return $this->getQualifyingFinancingOptions($this->getCartTotal());
        }
        return "hide";
    }

    public function getQualifyingFinancingOptionsForPaymentMethod($amount = false)
    {
        if($this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_payment_method")){
            /** TODO: get checkout amount */
            return $this->getQualifyingFinancingOptions(150);
        }
        return "hide";
    }

    public function getQualifyingFinancingOptions($amount = false)
    {
        $financeInformation = $this->getFinanceInformation($amount);
        if(isset($financeInformation->financing_options[0]->qualifying_financing_options[0])){
            return $financeInformation->financing_options[0]->qualifying_financing_options;
        }
        return false;
    }

    public function translateInterval($interval, $checkGrammar)
    {
        if($interval == "MONTHS"){
            if($checkGrammar){
                return "monatlichen";
            }else{
                return "monatliche";
            }
        }
    }

    /** TODO: using final price of product, not sure if that is the correct way */
    public function getItemPrice($withCurrencyCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get('\Magento\Framework\Registry');
        $productId = $registry->registry('current_product')->getId();
        $currentProduct = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        if($withCurrencyCode){
            $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
            return $priceHelper->currency($currentProduct->getFinalPrice(), true, false);
        }
        return $currentProduct->getFinalPrice();
    }

    public function getCartTotal()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        return $cart->getQuote()->getGrandTotal();
    }

    public function formatPrice($price)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
        return $priceHelper->currency($price, true, false);
    }

    public function getLender()
    {
        return $this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/lender");
    }

    public function isSpecific()
    {
        return $this->_scopeConfig->getValue("payment/iways_paypalinstalments_section/iways_paypalinstalments/specific_upstream_calculated");
    }
}