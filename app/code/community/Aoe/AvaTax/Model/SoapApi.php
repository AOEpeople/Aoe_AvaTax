<?php

require_once dirname(__FILE__) . '/../autoloader.php';

class Aoe_AvaTax_Model_SoapApi extends Aoe_AvaTax_Model_Api
{
    /**
     * @var AvaTax\TaxServiceSoap[]
     */
    protected $taxService = array();

    /**
     * @var AvaTax\AddressServiceSoap[]
     */
    protected $addressService = array();

    public function callGetTaxForQuote(Mage_Sales_Model_Quote $quote)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $address = $quote->getShippingAddress();

        if ($address->validate() !== true) {
            $resultArray = array(
                'ResultCode' => 'Skip',
                'Messages'   => array(),
                'TaxLines'   => array(),
            );

            return $resultArray;
        }

        $store = $quote->getStore();

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $timestamp = ($quote->getCreatedAt() ? Varien_Date::toTimestamp($quote->getCreatedAt()) : now());
        $date = new Zend_Date($timestamp);

        $request = new AvaTax\GetTaxRequest();
        $request->setCompanyCode($this->limit($helper->getConfig('company_code', $store), 25));
        $request->setDocType(AvaTax\DocumentType::$SalesOrder);
        $request->setCommit(false);
        $request->setDetailLevel(AvaTax\DetailLevel::$Tax);
        $request->setDocDate($date->toString('yyyy-MM-dd'));
        $request->setCustomerCode($helper->getCustomerDocCode($quote->getCustomer()) ?: $helper->getQuoteDocCode($quote));
        $request->setCurrencyCode($this->limit($quote->getBaseCurrencyCode(), 3));
        $request->setDiscount($hideDiscountAmount ? 0.0 : $store->roundPrice($address->getBaseDiscountAmount()));

        if ($quote->getCustomerTaxvat()) {
            $request->setBusinessIdentificationNo($this->limit($quote->getCustomerTaxvat(), 25));
        }

        $request->setOriginAddress($this->getOriginAddress($store));
        $request->setDestinationAddress($this->getAddress($address));

        $taxLines = array();

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableQuoteAddressItems($address) as $k => $item) {
            /** @var Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Quote_Address_Item $item */
            $itemAmount = $store->roundPrice($itemPriceIncludesTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal());
            //$itemAmount = $store->roundPrice($item->getBaseRowTotal());
            $itemAmount -= $store->roundPrice($item->getBaseDiscountAmount());
            $taxLine = new AvaTax\Line();
            $taxLine->setNo($this->limit($k, 50));
            $taxLine->setItemCode($this->limit($item->getSku(), 50));
            $taxLine->setQty(round($item->getQty(), 4));
            $taxLine->setAmount($itemAmount);
            $taxLine->setDescription($this->limit($item->getName(), 255));
            $taxLine->setTaxCode($this->limit($helper->getProductTaxCode($item->getProduct()), 25));
            $taxLine->setDiscounted($item->getBaseDiscountAmount() > 0.0);
            $taxLine->setTaxIncluded($itemPriceIncludesTax);
            $taxLine->setRef1($this->limit($helper->getQuoteItemRef1($item, $store), 250));
            $taxLine->setRef2($this->limit($helper->getQuoteItemRef2($item, $store), 250));
            $taxLines[] = $taxLine;
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $shippingAmount = $store->roundPrice($shippingPriceIncludesTax ? $address->getBaseShippingInclTax() : $address->getBaseShippingAmount());
        //$shippingAmount = $store->roundPrice($address->getBaseShippingAmount());
        $shippingAmount -= $store->roundPrice($address->getBaseShippingDiscountAmount());
        $taxLine = new AvaTax\Line();
        $taxLine->setNo('SHIPPING');
        $taxLine->setItemCode('SHIPPING');
        $taxLine->setQty(1);
        $taxLine->setAmount($shippingAmount);
        $taxLine->setDescription($this->limit("Shipping: " . $address->getShippingMethod(), 255));
        $taxLine->setTaxCode($this->limit($helper->getShippingTaxCode($store), 25));
        $taxLine->setDiscounted($address->getBaseShippingDiscountAmount() > 0.0);
        $taxLine->setTaxIncluded($shippingPriceIncludesTax);
        $taxLine->setRef1($this->limit($address->getShippingMethod(), 25));
        $taxLines[] = $taxLine;

        $request->setLines($taxLines);

        // TODO: Handle giftwrapping

        return $this->callGetTax($store, $request);
    }

    public function callGetTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice, $commit = false)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $order = $invoice->getOrder();
        $store = $order->getStore();

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $request = new AvaTax\GetTaxRequest();
        $request->setCompanyCode($helper->getConfig('company_code', $store));
        $request->setDocType($commit ? AvaTax\DocumentType::$SalesInvoice : AvaTax\DocumentType::$SalesOrder);
        $request->setDocCode($helper->getInvoiceDocCode($invoice));
        $request->setReferenceCode($helper->getOrderDocCode($order));
        $request->setCommit($commit);
        $request->setDetailLevel(AvaTax\DetailLevel::$Tax);
        $request->setDocDate($invoice->getCreatedAtDate()->toString('yyyy-MM-dd'));
        $request->setCustomerCode($helper->getOrderDocCode($order));
        $request->setCurrencyCode($this->limit($invoice->getBaseCurrencyCode(), 3));
        $request->setDiscount($hideDiscountAmount ? 0.0 : $store->roundPrice($invoice->getBaseDiscountAmount()));

        $request->setOriginAddress($this->getOriginAddress($store));
        $request->setDestinationAddress($this->getAddress($order->getShippingAddress()));

        $taxLines = array();

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableInvoiceItems($invoice) as $k => $item) {
            /** @var Mage_Sales_Model_Order_Invoice_Item $item */
            $itemAmount = $store->roundPrice($itemPriceIncludesTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal());
            $itemAmount -= $store->roundPrice($item->getBaseDiscountAmount());
            $taxLine = new AvaTax\Line();
            $taxLine->setNo($k);
            $taxLine->setItemCode($item->getSku());
            $taxLine->setQty(round($item->getQty(), 4));
            $taxLine->setAmount($itemAmount);
            $taxLine->setDescription($item->getName());
            $taxLine->setTaxCode($helper->getProductTaxCode($item->getOrderItem()->getProduct()));
            $taxLine->setDiscounted($item->getBaseDiscountAmount() > 0.0);
            $taxLine->setTaxIncluded($itemPriceIncludesTax);
            $taxLine->setRef1($helper->getInvoiceItemRef1($item, $store));
            $taxLine->setRef2($helper->getInvoiceItemRef2($item, $store));
            $taxLines[] = $taxLine;
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $shippingAmount = $store->roundPrice($shippingPriceIncludesTax ? $invoice->getBaseShippingInclTax() : $invoice->getBaseShippingAmount());
        $shippingAmount -= $store->roundPrice($invoice->getBaseShippingDiscountAmount());
        $taxLine = new AvaTax\Line();
        $taxLine->setNo('SHIPPING');
        $taxLine->setItemCode('SHIPPING');
        $taxLine->setQty(1);
        $taxLine->setAmount($shippingAmount);
        $taxLine->setDescription("Shipping: " . $order->getShippingMethod());
        $taxLine->setTaxCode($helper->getShippingTaxCode($store));
        $taxLine->setDiscounted($invoice->getBaseShippingDiscountAmount() > 0.0);
        $taxLine->setTaxIncluded($shippingPriceIncludesTax);
        $taxLine->setRef1($order->getShippingMethod());
        $taxLines[] = $taxLine;

        $request->setLines($taxLines);

        // TODO: Handle giftwrapping

        $result = $this->callGetTax($store, $request);

        if ($result['ResultCode'] === 'Error' && count($result['Messages']) === 1 && $helper->getConfigFlag('invoice_reattach', $store)) {
            $message = reset($result['Messages']);
            if ($message['Name'] === 'DocStatusError' && $message['Details'] === 'Expected Saved|Posted') {
                $request = new AvaTax\GetTaxHistoryRequest();
                $request->setCompanyCode($helper->getConfig('company_code', $store));
                $request->setDocType($commit ? AvaTax\DocumentType::$SalesInvoice : AvaTax\DocumentType::$SalesOrder);
                $request->setDocCode($helper->getInvoiceDocCode($invoice));
                $request->setDetailLevel(AvaTax\DetailLevel::$Tax);

                $historyResult = $this->callGetTaxHistory($store, $request);
                $result = $historyResult['GetTaxResult'];
            }
        }

        return $result;
    }

    public function callVoidTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $request = new AvaTax\CancelTaxRequest();
        $request->setCompanyCode($this->limit($helper->getConfig('company_code', $invoice->getStore()), 25));
        $request->setDocType(AvaTax\DocumentType::$SalesInvoice);
        $request->setDocCode($this->limit($helper->getInvoiceDocCode($invoice), 50));
        $request->setCancelCode(AvaTax\CancelCode::$DocVoided);

        return $this->callCancelTax($invoice->getStore(), $request);
    }

    public function callDeleteTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $request = new AvaTax\CancelTaxRequest();
        $request->setCompanyCode($this->limit($this->getHelper()->getConfig('company_code', $invoice->getStore()), 25));
        $request->setDocType(AvaTax\DocumentType::$SalesInvoice);
        $request->setDocCode($this->limit($this->getHelper()->getInvoiceDocCode($invoice), 50));
        $request->setCancelCode(AvaTax\CancelCode::$DocDeleted);

        return $this->callCancelTax($invoice->getStore(), $request);
    }

    public function callGetTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo, $commit = false)
    {
        // TODO: Implement callGetTaxForCreditmemo() method.
        /** @var Aoe_AvaTax_Helper_Data $helper */
        $helper = Mage::helper('Aoe_AvaTax/Data');

        $order = $creditmemo->getOrder();
        $store = $order->getStore();

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $request = new AvaTax\GetTaxRequest();
        $request->setCompanyCode($helper->getConfig('company_code', $store));
        $request->setDocType($commit ? AvaTax\DocumentType::$ReturnInvoice : AvaTax\DocumentType::$ReturnOrder);
        $request->setDocCode($helper->getCreditmemoDocCode($creditmemo));
        $request->setReferenceCode($helper->getOrderDocCode($order));
        $request->setCommit($commit);
        $request->setDetailLevel(AvaTax\DetailLevel::$Tax);
        $request->setDocDate($creditmemo->getCreatedAtDate()->toString('yyyy-MM-dd'));
        $request->setCustomerCode($helper->getOrderDocCode($order));
        $request->setCurrencyCode($this->limit($creditmemo->getBaseCurrencyCode(), 3));
        $request->setDiscount($hideDiscountAmount ? 0.0 : -$store->roundPrice($creditmemo->getBaseDiscountAmount()));

        $request->setOriginAddress($this->getOriginAddress($store));
        $request->setDestinationAddress($this->getAddress($order->getShippingAddress()));

        $taxLines = array();

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableCreditmemoItems($creditmemo) as $k => $item) {
            /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
            $itemAmount = $store->roundPrice($itemPriceIncludesTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal());
            $itemAmount -= $store->roundPrice($item->getBaseDiscountAmount());
            $taxLine = new AvaTax\Line();
            $taxLine->setNo($k);
            $taxLine->setItemCode($item->getSku());
            $taxLine->setQty(round($item->getQty(), 4));
            $taxLine->setAmount(-$itemAmount);
            $taxLine->setDescription($item->getName());
            $taxLine->setTaxCode($helper->getProductTaxCode($item->getOrderItem()->getProduct()));
            $taxLine->setDiscounted($item->getBaseDiscountAmount() > 0.0);
            $taxLine->setTaxIncluded($itemPriceIncludesTax);
            $taxLine->setRef1($helper->getCreditmemoItemRef1($item, $store));
            $taxLine->setRef2($helper->getCreditmemoItemRef2($item, $store));
            $taxLines[] = $taxLine;
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $shippingAmount = $store->roundPrice($shippingPriceIncludesTax ? $creditmemo->getBaseShippingInclTax() : $creditmemo->getBaseShippingAmount());
        $shippingAmount -= $store->roundPrice($creditmemo->getBaseShippingDiscountAmount());
        $taxLine = new AvaTax\Line();
        $taxLine->setNo('SHIPPING');
        $taxLine->setItemCode('SHIPPING');
        $taxLine->setQty(1);
        $taxLine->setAmount(-$shippingAmount);
        $taxLine->setDescription("Shipping: " . $order->getShippingMethod());
        $taxLine->setTaxCode($helper->getShippingTaxCode($store));
        $taxLine->setDiscounted($creditmemo->getBaseShippingDiscountAmount() > 0.0);
        $taxLine->setTaxIncluded($shippingPriceIncludesTax);
        $taxLine->setRef1($order->getShippingMethod());
        $taxLines[] = $taxLine;

        $request->setLines($taxLines);

        // TODO: Handle giftwrapping

        $result = $this->callGetTax($store, $request);

        if ($result['ResultCode'] === 'Error' && count($result['Messages']) === 1 && $helper->getConfigFlag('creditmemo_reattach', $store)) {
            $message = reset($result['Messages']);
            if ($message['Name'] === 'DocStatusError' && $message['Details'] === 'Expected Saved|Posted') {
                $request = new AvaTax\GetTaxHistoryRequest();
                $request->setCompanyCode($helper->getConfig('company_code', $store));
                $request->setDocType($commit ? AvaTax\DocumentType::$ReturnInvoice : AvaTax\DocumentType::$ReturnOrder);
                $request->setDocCode($helper->getCreditmemoDocCode($creditmemo));
                $request->setDetailLevel(AvaTax\DetailLevel::$Tax);

                $historyResult = $this->callGetTaxHistory($store, $request);
                $result = $historyResult['GetTaxResult'];
            }
        }

        return $result;
    }

    public function callVoidTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $request = new AvaTax\CancelTaxRequest();
        $request->setCompanyCode($this->limit($this->getHelper()->getConfig('company_code', $creditmemo->getStore()), 25));
        $request->setDocType(AvaTax\DocumentType::$ReturnInvoice);
        $request->setDocCode($this->limit($this->getHelper()->getCreditmemoDocCode($creditmemo), 50));
        $request->setCancelCode(AvaTax\CancelCode::$DocVoided);

        return $this->callCancelTax($creditmemo->getStore(), $request);
    }

    public function callDeleteTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $request = new AvaTax\CancelTaxRequest();
        $request->setCompanyCode($this->limit($this->getHelper()->getConfig('company_code', $creditmemo->getStore()), 25));
        $request->setDocType(AvaTax\DocumentType::$ReturnInvoice);
        $request->setDocCode($this->limit($this->getHelper()->getCreditmemoDocCode($creditmemo), 50));
        $request->setCancelCode(AvaTax\CancelCode::$DocDeleted);

        return $this->callCancelTax($creditmemo->getStore(), $request);
    }

    /**
     * @param \AvaTax\GetTaxRequest $request
     *
     * @return array
     * @throws Exception
     */
    protected function callGetTax(Mage_Core_Model_Store $store, AvaTax\GetTaxRequest $request)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $requestData = $helper->normalizeGetTaxRequest($request);
        $resultData = $helper->loadResult($store, $requestData);
        if ($resultData === false) {
            $resultData = array();
            try {
                $result = $this->getTaxService($store)->getTax($request);
                $resultData = $helper->normalizeGetTaxResult($result);
                $helper->logRequestResult($store, $requestData, $resultData);
            } catch (Exception $e) {
                $helper->logRequestException($store, $requestData, $resultData, $e);
                throw $e;
            }
            $helper->saveResult($store, $requestData, $resultData);
        }

        return $resultData;
    }

    /**
     * @param \AvaTax\CancelTaxRequest $request
     *
     * @return array
     * @throws Exception
     */
    protected function callCancelTax(Mage_Core_Model_Store $store, AvaTax\CancelTaxRequest $request)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $requestData = $helper->normalizeCancelTaxRequest($request);
        $resultData = $helper->loadResult($store, $requestData);
        if ($resultData === false) {
            $resultData = array();
            try {
                $result = $this->getTaxService($store)->cancelTax($request);
                $resultData = $helper->normalizeCancelTaxResult($result);
                $helper->logRequestResult($store, $requestData, $resultData);
            } catch (Exception $e) {
                $helper->logRequestException($store, $requestData, $resultData, $e);
                throw $e;
            }
            $helper->saveResult($store, $requestData, $resultData);
        }

        return $resultData;
    }

    protected function callGetTaxHistory(Mage_Core_Model_Store $store, AvaTax\GetTaxHistoryRequest $request)
    {
        /** @var Aoe_AvaTax_Helper_Soap $helper */
        $helper = Mage::helper('Aoe_AvaTax/Soap');

        $requestData = $helper->normalizeGetTaxHistoryRequest($request);
        $resultData = array();
        try {
            $result = $this->getTaxService($store)->getTaxHistory($request);
            $resultData = $helper->normalizeGetTaxHistoryResult($result);
            $helper->logRequestResult($store, $requestData, $resultData);
        } catch (Exception $e) {
            $helper->logRequestException($store, $requestData, $resultData, $e);
            throw $e;
        }

        return $resultData;
    }

    protected function getOriginAddress(Mage_Core_Model_Store $store)
    {
        $taxAddress = new AvaTax\Address();
        $taxAddress->setLine1($this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1, $store), 50));
        $taxAddress->setLine2($this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2, $store), 50));
        $taxAddress->setCity($this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $store), 50));
        $taxAddress->setRegion($this->limit(Mage::getModel('directory/region')->load(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID, $store))->getCode(), 3));
        $taxAddress->setCountry($this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store), 2));
        $taxAddress->setPostalCode($this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP, $store), 11));

        return $taxAddress;
    }

    protected function getAddress(Mage_Customer_Model_Address_Abstract $address)
    {
        $taxAddress = new AvaTax\Address();
        $taxAddress->setLine1($this->limit($address->getStreet1(), 50));
        $taxAddress->setLine2($this->limit($address->getStreet2(), 50));
        $taxAddress->setLine3($this->limit($address->getStreet3(), 50));
        $taxAddress->setCity($this->limit($address->getCity(), 50));
        $taxAddress->setRegion($this->limit($address->getRegionCode(), 3));
        $taxAddress->setCountry($this->limit($address->getCountryId(), 2));
        $taxAddress->setPostalCode($this->limit($address->getPostcode(), 11));

        return $taxAddress;
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return \AvaTax\TaxServiceSoap
     */
    protected function getTaxService(Mage_Core_Model_Store $store)
    {
        if (!isset($this->taxService[$store->getId()])) {
            /** @var Aoe_AvaTax_Helper_Data $helper */
            $helper = Mage::helper('Aoe_AvaTax/Data');

            new AvaTax\ATConfig(
                'store-' . $store->getId(),
                array(
                    'url'     => $helper->getConfig($helper->getConfig('mode', $store) . '_url', $store),
                    'account' => $helper->getConfig('account', $store),
                    'license' => $helper->getConfig('license', $store),
                    'trace'   => $helper->getConfigFlag('debug', $store),
                )
            );

            $api = new AvaTax\TaxServiceSoap('store-' . $store->getId());

            $this->taxService[$store->getId()] = $api;
        }

        return $this->taxService[$store->getId()];
    }

    /**
     * @param Mage_Core_Model_Store $store
     *
     * @return \AvaTax\AddressServiceSoap
     */
    protected function getAddressService(Mage_Core_Model_Store $store)
    {
        if (!isset($this->addressService[$store->getId()])) {
            /** @var Aoe_AvaTax_Helper_Data $helper */
            $helper = Mage::helper('Aoe_AvaTax/Data');

            new AvaTax\ATConfig(
                'store-' . $store->getId(),
                array(
                    'url'     => $helper->getConfig($helper->getConfig('mode', $store) . '_url', $store),
                    'account' => $helper->getConfig('account', $store),
                    'license' => $helper->getConfig('license', $store),
                    'trace'   => $helper->getConfigFlag('debug', $store),
                )
            );

            $api = new AvaTax\AddressServiceSoap('store-' . $store->getId());

            $this->addressService[$store->getId()] = $api;
        }

        return $this->addressService[$store->getId()];
    }

    /**
     * Limit the length of a string
     *
     * NB: This also trims $value before limiting
     *
     * @param string $value
     * @param int    $limit
     *
     * @return string
     */
    protected function limit($value, $limit = 0)
    {
        $value = trim($value);
        $limit = intval($limit);
        if ($limit > 0) {
            $value = substr($value, 0, $limit);
        }

        return $value;
    }
}
