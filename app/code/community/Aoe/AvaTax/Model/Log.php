<?php

/**
 * @method Aoe_AvaTax_Model_Log load(mixed $id, string $field = null)
 * @method Aoe_AvaTax_Resource_Log getResource()
 * @method Aoe_AvaTax_Resource_Log_Collection getResourceCollection()
 * @method Aoe_AvaTax_Resource_Log_Collection getCollection()
 * @method string getCreatedAt()
 * @method Aoe_AvaTax_Model_Log setCreatedAt(string $dateTime)
 * @method int getStoreId()
 * @method Aoe_AvaTax_Model_Log setStoreId(int $id)
 * @method string getUrl()
 * @method Aoe_AvaTax_Model_Log setUrl(string $url)
 * @method string getRequestBody()
 * @method Aoe_AvaTax_Model_Log setRequestBody(string $body)
 * @method string getResultBody()
 * @method Aoe_AvaTax_Model_Log setResultBody(string $body)
 * @method string getResultCode()
 * @method Aoe_AvaTax_Model_Log setResultCode(string $code)
 * @method string getFailureMessage()
 * @method Aoe_AvaTax_Model_Log setFailureMessage(string $message)
 */
class Aoe_AvaTax_Model_Log extends Mage_Core_Model_Abstract
{
    const CODE_SUCCESS = 'Success';
    const CODE_ERROR = 'Error';
    const CODE_WARNING = 'Warning';
    const CODE_EXCEPTION = 'Exception';
    const CODE_FAILURE = 'Failure';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'aoe_avatax_log';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'log';

    /**
     * @var Mage_Core_Model_Store
     */
    protected $store = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote = null;

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order = null;

    /**
     * @var Mage_Sales_Model_Order_Invoice
     */
    protected $invoice = null;

    /**
     * @var Mage_Sales_Model_Order_Creditmemo
     */
    protected $creditmemo = null;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_setResourceModel('Aoe_AvaTax/Log', 'Aoe_AvaTax/Log_Collection');
    }

    /**
     * @return Mage_Core_Model_Store|null
     */
    public function getStore()
    {
        if ($this->getStoreId() === null) {
            return null;
        }

        if (!$this->store) {
            $this->store = Mage::app()->getStore($this->getStoreId());
        }

        return $this->store;
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return $this
     */
    public function setStore(Mage_Core_Model_Store $store)
    {
        $this->store = $store;

        return $this->setStoreId($store->getId());
    }

    public function setData($key, $value = null)
    {
        parent::setData($key, $value);

        if ($this->getStoreId() && $this->store && $this->store->getId() != $this->getStoreId()) {
            $this->store = null;
        }

        return $this;
    }
}
