<?php 

namespace Appeal\VsfStripe\Model\Payment;

class VsfStripe extends \Magento\Payment\Model\Method\AbstractMethod 
{
    protected $_code = "vsfstripe";
    protected $_isOffline = false; 


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = array()
    ) {
        parent::__construct(
            $context, $registry, $extensionFactory, $customAttributeFactory,
            $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, null,
            null, $data
        );

        $this->_countryFactory = $countryFactory; 

        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The authorize action is not available.'));
        }
        return $this;
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->canCapture()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The capture action is not available.'));
        }
        return $this;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount) 
    {
        if (!$this->canRefund()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available'));
        }
        return $this;
    }

    // Not sure if the below is even needed anymore.. 
    public function isAvailable(
        \Magento\Quote\Api\Data\CartInterface $quote = null 
    ) {
        // die(parent::isAvailable($quote));
        // return true;
        return parent::isAvailable($quote);
    }
}