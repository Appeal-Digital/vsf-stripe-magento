<?php 

namespace Appeal\VsfStripe\Model; 

use \Magento\Framework\App\Config\ScopeConfigInterface; 

class Config
{
    const KEY_PUBLISHABLE_KEY = 'publishable_key';
    const KEY_SECRET_KEY = 'secret_key';
    
    /**
     * @var string
     */
    protected $methodCode = 'vsfstripe';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var int
     */
    protected $storeId = null;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isActive()
    {
        return true; 
    }

    public function getKeyId()
    {
        return $this->getConfigData(self::KEY_PUBLISHABLE_KEY);
    }

    public function getConfigData($field, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->storeId;
        }
        $code = $this->methodCode; 

        $path = 'payment/' . $code . '/' . $field; 

        // echo $field;

        // if ($field == 'title') {
        //     die(print_r($this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)));
        // }

        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

}