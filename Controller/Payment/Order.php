<?php 

namespace Appeal\VsfStripe\Controller\Payment; 

use Appeal\VsfStripe\Model\PaymentMethod;
use Magento\Framework\Controller\ResultFactory; 

class Order extends \Appeal\VsfStripe\Controller\BaseController
{
    protected $quote; 
    protected $checkoutSession;
    protected $currency = PaymentMethod::CURRENCY;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Checkout\Model\Session $checkoutSession,
        \Appeal\VsfStripe\Model\CheckoutFactory $checkoutFactory, 
        \Appeal\VsfStripe\Model\Config $config, 
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        parent::__construct(
            $context, 
            $customerSession,
            $checkoutSession,
            $config
        );

        $this->checkoutFactory = $checkoutFactory;
        $this->catalogSession = $catalogSession;
        $this->config = $config; 
    }

    public function execute() 
    {
        return true; 
    }

}