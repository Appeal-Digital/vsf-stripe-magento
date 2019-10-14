<?php 

namespace Appeal\VsfStripe\Controller;

use Appeal\VsfStripe\Model\Config;
use Magento\Framework\App\RequestInterface; 

abstract class BaseController extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Appeal\VsfStripe\Model\CheckoutFactory
     */
    protected $checkoutFactory;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = false;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Appeal\VsfStripe\Model\Checkout
     */
    protected $checkout;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Appeal\VsfStripe\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Appeal\VsfStripe\Model\Config $config
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;

        $this->publishable_key = $this->config->getConfigData(Config::KEY_PUBLISHABLE_KEY);
        $this->secret_key = $this->config->getConfigData(Config::KEY_SECRET_KEY);
    }   
}