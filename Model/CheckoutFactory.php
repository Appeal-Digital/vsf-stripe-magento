<?php 

namespace Appeal\VsfStripe\Model; 

class CheckoutFactory
{
    protected $_objectManager = null; 

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create($className, array $data = [])
    {
        return $this->_objectManager->create($className, $data);
    }
}