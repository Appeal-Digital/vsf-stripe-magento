<?php 

namespace Appeal\VsfStripe\Model;

use Magento\Framework\Exception\LocalizedException; 
use Magento\Sales\Model\Order\Payment\Transaction; 
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Payment\Model\InfoInterface;
use Appeal\VsfStripe\Model\Config; 
use Stripe\Stripe; 
use Stripe\PaymentIntent;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const METHOD_CODE = 'vsfstripe';
    const CURRENCY = "gbp";

    protected $_code = self::METHOD_CODE;

    protected $_canAuthorize = true; 
    protected $_canCapture = true; 
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true; 

    protected $config; 
    protected $request; 
    protected $salesTransactionCollectionFactory;
    protected $productMetaData;
    protected $regionFactory; 
    protected $publishableKey;
    protected $secretKey;
    protected $stripe; 

    protected $paymentIntentId; 

    protected $order; 

    protected $data; 

    protected $invoiceService;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Appeal\VsfStripe\Model\Config $config
     * @param \Magento\Framework\App\RequestInterface $request
     * @param TransactionCollectionFactory $salesTransactionCollectionFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetaData
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Appeal\VsfStripe\Model\Config $config,
        \Magento\Framework\App\RequestInterface $request,
        TransactionCollectionFactory $salesTransactionCollectionFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Appeal\VsfStripe\Controller\Payment\Order $order,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->config = $config;
        $this->request = $request;
        $this->salesTransactionCollectionFactory = $salesTransactionCollectionFactory;
        $this->productMetaData = $productMetaData;
        $this->regionFactory = $regionFactory;
        $this->orderRepository = $orderRepository;

        $this->invoiceService = $invoiceService;

        $this->publishableKey = $this->config->getConfigData(Config::KEY_PUBLISHABLE_KEY);
        $this->secretKey = $this->config->getConfigData(Config::KEY_SECRET_KEY);

        $this->order = $order;
        $this->data = $data;
    }

    /**
     * Validate data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        return $this;
    }

    protected function getPostData()
    {
        $request = file_get_contents('php://input');

        return json_decode($request, true);
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        $request = $this->getPostData();

        $intentId = $request['paymentMethod']['additional_data']['intentId']; 

        // $this->_logger->critical($request);

        $additionalData = unserialize($payment->getData('additional_data'));
        $additionalData['intent_id'] = $intentId;
        $payment->setData('additional_data', serialize($additionalData));

        $order = $payment->getOrder();
        $orderId = $order->getIncrementId();

        $payment->setAmountPaid($amount)
                ->setLastTransId($intentId)
                ->setTransactionId($intentId);       

        // Capture the payment 

        return $this;
    }

    public function capture(InfoInterface $payment, $amount)
    {
        // echo "<pre>";
        // $order->setStripeIntentId('hellos');
        $request = $this->getPostData();

        $intentId = $request['paymentMethod']['additional_data']['intentId']; 
       
        Stripe::setApiKey($this->secretKey);

        $intent = PaymentIntent::retrieve($intentId);
        $captureResult = $intent->capture();

        
        // It's important we get the chard ID to refund 

        if ($captureResult['status'] == 'succeeded') {
            $charge = $captureResult['charges']['data'][0];

            $additionalData = unserialize($payment->getData('additional_data'));
            $additionalData['charge_id'] = $charge['id'];
            
            if (isset($additionalData['intent_id'])) {
                $additionalData['intent_id'] = $intentId; 
            }

            $payment->setData('additional_data', serialize($additionalData));

            $payment->setAmountPaid($charge['amount'])
                    ->setLastTransId($charge['id'])
                    ->setTransactionId($charge['id'])
                    ->setIsTransactionClosed(true)
                    ->setShouldCloseParentTransaction(true);
        }

        return $this;
    }

    /**
     * Refunds specified amount
     *
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount) 
    {
        // Get the payment ID 
        $additionalData = unserialize($payment->getData('additional_data'));
        $chargeId = $additionalData['charge_id'];

        $stripeAmount = $amount * 100; 

        Stripe::setApiKey($this->secretKey);

        $refund = \Stripe\Refund::create([
            "charge" => $chargeId,
            "amount" => $stripeAmount, 
        ]);

        return $this;
    }

}