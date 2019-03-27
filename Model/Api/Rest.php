<?php
/**
 * Created by PhpStorm.
 * User: gero
 * Date: 27.03.19
 * Time: 12:58
 */

namespace Iways\PayPalInstalments\Model\Api;

class Rest
{
    protected $_scopeConfig;
    protected $clientSecret;
    protected $clientId;
    protected $sandboxFlag;
    protected $cache;
    protected $messageManager;
    protected $storeManager;

    const AUTH_CACHE_KEY = 'paypal_rest_auth';
    const AUTH_CACHE_LIFETIME = 7200;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->clientId = $this->_scopeConfig->getValue("iways_paypalinstalments/api/client_id");
        $this->clientSecret = $this->_scopeConfig->getValue("iways_paypalinstalments/api/client_secret");
        $this->sandboxFlag = $this->_scopeConfig->getValue("iways_paypalinstalments/api/sandbox_flag");
        $this->cache = $cache;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }


    public function getFinanceInfo($amt)
    {
        return $this->request(
            'v1/credit/calculated-financing-options',
            \Zend_Http_Client::POST,
            array(
                'financing_country_code' => 'DE',       //TODO: get the correct country code
                'transaction_amount' => array(
                    'value' => $amt,
                    'currency_code' => $this->storeManager->getStore()->getCurrentCurrency()->getCode()
                )
            ));
    }

    public function request($url, $method = \Zend_Http_Client::GET, $params = array())
    {
        try{
            if(!$this->clientId || !$this->clientSecret){
                return array();
            }
            $request = new \Zend_Http_Client($this->getPayPalUrl($url));
            $request->setHeaders($this->getStandartHeaders());
            if($method != \Zend_Http_Client::POST){
                $request->setParameterGet($params);
            }else{
                $request->setRawData(json_encode($params));
            }
            return json_decode($request->request($method)->getBody());
        }catch (\Exception $e){
            $this->messageManager->addExceptionMessage($e, __('Request failed.'));
        }
    }

    public function getStandartHeaders($withAuth = true)
    {
        $headers = array('Accept' => 'application/json', 'Content-Type' => 'application/json');
        if($withAuth){
            $headers['Authorization'] = 'Bearer ' . $this->getAccessToken();
        }
        return $headers;
    }

    public function getAccessToken()
    {
        $auth = $this->cache->load(self::AUTH_CACHE_KEY);
        if(!$auth){
            $auth = $this->getAccessToken();
        }
        $auth = json_decode($auth);
        return $auth->access_token;
    }

    /**
     * Gets a new PayPal Access Token
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function refreshAccessToken()
    {
        $client = new \Zend_Http_Client($this->getPayPalUrl('v1/oauth/token'));
        $client->setAuth($this->clientId, $this->clientSecret);
        $client->setHeaders($this->getStandartHeaders(false));
        $client->setParameterPost('grant_type', 'client_credentials');
        $auth = $client->request(\Zend_Http_Client::POST)->getBody();
        $authArray = json_decode($auth);
        if(!isset($authArray->access_token)){
            throw new \Magento\Framework\Exception\LocalizedException(__("PayPal Access Token could not be retrieved."));
        }
        $this->cache->save($auth, self::AUTH_CACHE_KEY, array('block_html'), self::AUTH_CACHE_LIFETIME);
        return $auth;
    }

    public function getPayPalUrl($endpoint = "")
    {
        return sprintf('https://api.%spaypal.com/%s',
            $this->sandboxFlag ? 'sandbox.' : '', ltrim($endpoint, '/'));
    }
}