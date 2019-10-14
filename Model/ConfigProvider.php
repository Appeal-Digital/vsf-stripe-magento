<?php 

namespace Appeal\VsfStripe\Model;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Checkout\Model\ConfigProviderInterface; 
use Appeal\VsfStripe\Model\PaymentMethod;

class ConfigProvider implements ConfigProviderInterface
{

    protected $methodCode = PaymentMethod::METHOD_CODE; 

    /**
     * @var \Appeal\VsfStripe\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

     /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\Url
     */
    protected $urlBuilder;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Url $urlBuilder,
        \Psr\Log\LoggerInterface $logger,
        PaymentHelper $paymentHelper,
        \Appeal\VsfStripe\Model\Config $config,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->assetRepo = $assetRepo;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;
        $this->methodCode = PaymentMethod::METHOD_CODE;
        $this->method = $paymentHelper->getMethodInstance(PaymentMethod::METHOD_CODE);
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    public function getConfig()
    {
        // echo "IS ACTIVE: " . $this->config->isActive();

        if (!$this->config->isActive()) {
            return [];
        }

        $config = [
            'payment' => [
                'vsfstripe' => [
                    'publishable_key' => $this->config->getKeyId()
                ]
            ]
        ];
    }

}