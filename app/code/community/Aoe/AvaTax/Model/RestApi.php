<?php

class Aoe_AvaTax_Model_RestApi extends Aoe_AvaTax_Model_Api
{
    public function callGetTaxForQuote(Mage_Sales_Model_Quote $quote)
    {
        $request = $this->createTaxRequestFromQuoteAddress($quote->getShippingAddress());
        $request = $this->prepareGetTaxRequest($request);
        $errors = $this->validateTaxRequest($request);
        if (count($errors)) {
            return $errors;
        }

        return $this->call($quote->getStore(), '1.0/tax/get', $request);
    }

    public function callGetTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice, $commit = false)
    {
        $request = $this->createTaxRequestFromInvoice($invoice, $commit);
        $request = $this->prepareGetTaxRequest($request);
        $errors = $this->validateTaxRequest($request);
        if (count($errors)) {
            return $errors;
        }

        return $this->call($invoice->getStore(), '1.0/tax/get', $request);
    }

    public function callGetTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo, $commit = false)
    {
        $request = $this->createTaxRequestFromCreditmemo($creditmemo, $commit);
        $request = $this->prepareGetTaxRequest($request);
        $errors = $this->validateTaxRequest($request);
        if (count($errors)) {
            return $errors;
        }

        return $this->call($creditmemo->getStore(), '1.0/tax/get', $request);
    }

    public function callVoidTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $request = array(
            'Client'      => 'Aoe_AvaTax',
            'CompanyCode' => $this->limit($this->getHelper()->getConfig('company_code', $invoice->getStore()), 25),
            'DocType'     => 'SalesInvoice',
            'DocCode'     => $this->limit($this->getHelper()->getInvoiceDocCode($invoice), 50),
            'CancelCode'  => 'DocVoided',
        );

        $request = $this->prepareRequest($request);

        return $this->call($invoice->getStore(), '1.0/tax/cancel', $request, 'CancelTaxResult');
    }

    public function callDeleteTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $request = array(
            'Client'      => 'Aoe_AvaTax',
            'CompanyCode' => $this->limit($this->getHelper()->getConfig('company_code', $invoice->getStore()), 25),
            'DocType'     => 'SalesInvoice',
            'DocCode'     => $this->limit($this->getHelper()->getInvoiceDocCode($invoice), 50),
            'CancelCode'  => 'DocDeleted',
        );

        $request = $this->prepareRequest($request);

        return $this->call($invoice->getStore(), '1.0/tax/cancel', $request, 'CancelTaxResult');
    }

    public function callVoidTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $request = array(
            'Client'      => 'Aoe_AvaTax',
            'CompanyCode' => $this->limit($this->getHelper()->getConfig('company_code', $creditmemo->getStore()), 25),
            'DocType'     => 'ReturnInvoice',
            'DocCode'     => $this->limit($this->getHelper()->getCreditmemoDocCode($creditmemo), 50),
            'CancelCode'  => 'DocVoided',
        );

        $request = $this->prepareRequest($request);

        return $this->call($creditmemo->getStore(), '1.0/tax/cancel', $request, 'CancelTaxResult');
    }

    public function callDeleteTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $request = array(
            'Client'      => 'Aoe_AvaTax',
            'CompanyCode' => $this->limit($this->getHelper()->getConfig('company_code', $creditmemo->getStore()), 25),
            'DocType'     => 'ReturnInvoice',
            'DocCode'     => $this->limit($this->getHelper()->getCreditmemoDocCode($creditmemo), 50),
            'CancelCode'  => 'DocDeleted',
        );

        $request = $this->prepareRequest($request);

        return $this->call($creditmemo->getStore(), '1.0/tax/cancel', $request, 'CancelTaxResult');
    }

    protected function call(Mage_Core_Model_Store $store, $path, array $request, $resultKey = null)
    {
        $account = $this->getAccount($store);
        $license = $this->getLicense($store);
        $url = $this->getUrl($store, $path);
        $timeout = $this->getTimeout($store);
        $request = $this->recursiveKeySort($request);
        $requestBody = json_encode($request);
        $resultBody = '';

        $hash = $this->generateHash($request, array($account, $license, $url));
        $result = $this->loadResult($hash);
        if (is_array($result)) {
            return $result;
        }

        try {
            /** @var Zend_Http_Client $client */
            $client = Mage::getModel('Varien_Http_Client', $url);
            $client->setConfig(array('timeout' => $timeout));
            $client->setHeaders('Content-Type', 'application/json');
            $client->setAuth($account, $license);
            $client->setRawData($requestBody);

            $response = $client->request(Zend_Http_Client::POST);

            $resultBody = $response->getBody();

            $result = json_decode($resultBody, true);
            if (!is_array($result)) {
                Mage::throwException('Invalid response: Could not decode JSON body');
            }

            if ($resultKey && array_key_exists($resultKey, $result)) {
                $result = $result[$resultKey];
            }

            $this->logRequestResult($store, $request, $result);

            $this->saveResult($hash, $result);

            return $result;
        } catch (Exception $e) {
            $this->logRequestException($store, $requestBody, $resultBody, $e);
            throw $e;
        }
    }

    protected function getUrl(Mage_Core_Model_Store $store, $path)
    {
        return $this->getBaseUrl($store) . '/' . trim($path, ' /');
    }

    protected function getOriginAddress($code, $store = null)
    {
        $data = array(
            'Line1'      => $this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1, $store), 50),
            'Line2'      => $this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2, $store), 50),
            'City'       => $this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_CITY, $store), 50),
            'Region'     => $this->limit(Mage::getModel('directory/region')->load(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_REGION_ID, $store))->getCode(), 3),
            'Country'    => $this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_COUNTRY_ID, $store), 2),
            'PostalCode' => $this->limit(Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ZIP, $store), 11),
        );

        $data = array_filter($data);

        if ($code && !empty($data)) {
            $data['AddressCode'] = $code;
        }

        return $data;
    }

    protected function getAddress($code, Mage_Customer_Model_Address_Abstract $address)
    {
        $data = array(
            'Line1'      => $this->limit($address->getStreet1(), 50),
            'Line2'      => $this->limit($address->getStreet2(), 50),
            'Line3'      => $this->limit($address->getStreet3(), 50),
            'City'       => $this->limit($address->getCity(), 50),
            'Region'     => $this->limit($address->getRegionCode(), 3),
            'Country'    => $this->limit($address->getCountryId(), 2),
            'PostalCode' => $this->limit($address->getPostcode(), 11),
        );

        $data = array_filter($data);

        if ($code && !empty($data)) {
            $data['AddressCode'] = $code;
        }

        return $data;
    }

    protected function createTaxRequestFromQuoteAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $quote = $address->getQuote();
        $store = $quote->getStore();

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $timestamp = ($quote->getCreatedAt() ? Varien_Date::toTimestamp($quote->getCreatedAt()) : now());
        $date = new Zend_Date($timestamp);
        $request = array(
            'Client'       => 'Aoe_AvaTax',
            'CompanyCode'  => $this->limit($this->getHelper()->getConfig('company_code', $store), 25),
            'DocType'      => 'SalesOrder',
            'Commit'       => false,
            'DetailLevel'  => 'Tax',
            'DocDate'      => $date->toString('yyyy-MM-dd'),
            'CustomerCode' => $this->getHelper()->getCustomerDocCode($quote->getCustomer()) ?: $this->getHelper()->getQuoteDocCode($quote),
            'CurrencyCode' => $this->limit($quote->getBaseCurrencyCode(), 3),
            'Discount'     => ($hideDiscountAmount ? 0.0 : $store->roundPrice($address->getBaseDiscountAmount())),
            'Addresses'    => array(),
            'Lines'        => array(),
        );

        if ($quote->getCustomerTaxvat()) {
            $request['BusinessIdentificationNo'] = $this->limit($quote->getCustomerTaxvat(), 25);
        }

        $request['Addresses'][] = $this->getOriginAddress('ORIGIN', $store);
        $request['Addresses'][] = $this->getAddress('DESTINATION', $address);

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableQuoteAddressItems($address) as $k => $item) {
            /** @var Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Quote_Address_Item $item */
            $request['Lines'][] = array(
                "LineNo"          => $this->limit($k, 50),
                "ItemCode"        => $this->limit($item->getSku(), 50),
                "Qty"             => round(floatval($item->getQty()), 4),
                "Amount"          => ($store->roundPrice($item->getBaseRowTotal()) - $store->roundPrice($item->getBaseDiscountAmount())),
                "OriginCode"      => "ORIGIN",
                "DestinationCode" => "DESTINATION",
                "Description"     => $this->limit($item->getName(), 255),
                "TaxCode"         => $this->limit($this->getHelper()->getProductTaxCode($item->getProduct()), 25),
                "Discounted"      => ($item->getBaseDiscountAmount() > 0.0),
                "TaxIncluded"     => $itemPriceIncludesTax,
                "Ref1"            => $this->limit($this->getHelper()->getQuoteItemRef1($item, $store), 250),
                "Ref2"            => $this->limit($this->getHelper()->getQuoteItemRef2($item, $store), 250),
            );
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $request['Lines'][] = array(
            "LineNo"          => "SHIPPING",
            "ItemCode"        => "SHIPPING",
            "Qty"             => "1",
            "Amount"          => ($store->roundPrice($address->getBaseShippingAmount()) - $store->roundPrice($address->getBaseShippingDiscountAmount())),
            "OriginCode"      => "ORIGIN",
            "DestinationCode" => "DESTINATION",
            "Description"     => $this->limit("Shipping: " . $address->getShippingMethod(), 255),
            "TaxCode"         => $this->limit($this->getHelper()->getShippingTaxCode($store), 25),
            "Discounted"      => ($address->getBaseShippingDiscountAmount() > 0.0),
            "TaxIncluded"     => $shippingPriceIncludesTax,
            "Ref1"            => $this->limit($address->getShippingMethod(), 250),
        );

        // TODO: Handle giftwrapping

        return $request;
    }

    protected function createTaxRequestFromInvoice(Mage_Sales_Model_Order_Invoice $invoice, $commit = false)
    {
        $order = $invoice->getOrder();
        $store = $invoice->getStore();

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $request = array(
            'Client'        => 'Aoe_AvaTax',
            'CompanyCode'   => $this->limit($this->getHelper()->getConfig('company_code', $store), 25),
            'DocType'       => ($commit ? 'SalesInvoice' : 'SalesOrder'),
            'DocCode'       => $this->limit($this->getHelper()->getInvoiceDocCode($invoice), 50),
            'ReferenceCode' => $this->getHelper()->getOrderDocCode($order),
            'Commit'        => $commit,
            'DetailLevel'   => 'Tax',
            'DocDate'       => $invoice->getCreatedAtDate()->toString('yyyy-MM-dd'),
            'CustomerCode'  => $this->getHelper()->getCustomerDocCode($customer) ?: $this->getHelper()->getOrderDocCode($order),
            'CurrencyCode'  => $this->limit($invoice->getBaseCurrencyCode(), 3),
            'Discount'      => ($hideDiscountAmount ? 0.0 : $store->roundPrice($invoice->getBaseDiscountAmount())),
            'Addresses'     => array(),
            'Lines'         => array(),
        );

        if ($order->getCustomerTaxvat()) {
            $request['BusinessIdentificationNo'] = $this->limit($order->getCustomerTaxvat(), 25);
        }

        $request['Addresses'][] = $this->getOriginAddress('ORIGIN', $store);
        $request['Addresses'][] = $this->getAddress('DESTINATION', $order->getShippingAddress());

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableInvoiceItems($invoice) as $k => $item) {
            /** @var Mage_Sales_Model_Order_Invoice_Item $item */
            $request['Lines'][] = array(
                "LineNo"          => $this->limit($k, 50),
                "ItemCode"        => $this->limit($item->getSku(), 50),
                "Qty"             => round(floatval($item->getQty()), 4),
                "Amount"          => ($store->roundPrice($itemPriceIncludesTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal()) - $store->roundPrice($item->getBaseDiscountAmount())),
                "OriginCode"      => "ORIGIN",
                "DestinationCode" => "DESTINATION",
                "Description"     => $this->limit($item->getName(), 255),
                "TaxCode"         => $this->limit($this->getHelper()->getProductTaxCode($item->getOrderItem()->getProduct()), 25),
                "Discounted"      => ($item->getBaseDiscountAmount() > 0.0),
                "TaxIncluded"     => $itemPriceIncludesTax,
                "Ref1"            => $this->limit($this->getHelper()->getInvoiceItemRef1($item, $store), 250),
                "Ref2"            => $this->limit($this->getHelper()->getInvoiceItemRef2($item, $store), 250),
            );
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $request['Lines'][] = array(
            "LineNo"          => "SHIPPING",
            "ItemCode"        => "SHIPPING",
            "Qty"             => "1",
            "Amount"          => ($store->roundPrice($shippingPriceIncludesTax ? $invoice->getBaseShippingInclTax() : $invoice->getBaseShippingAmount()) - $store->roundPrice($invoice->getBaseShippingDiscountAmount())),
            "OriginCode"      => "ORIGIN",
            "DestinationCode" => "DESTINATION",
            "Description"     => $this->limit("Shipping: " . $order->getShippingMethod(), 255),
            "TaxCode"         => $this->limit($this->getHelper()->getShippingTaxCode($store), 25),
            "Discounted"      => ($invoice->getBaseShippingDiscountAmount() > 0.0),
            "TaxIncluded"     => $shippingPriceIncludesTax,
            "Ref1"            => $this->limit($order->getShippingMethod(), 250),
        );

        // TODO: Handle giftwrapping

        return $request;
    }

    protected function createTaxRequestFromCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo, $commit = false)
    {
        $order = $creditmemo->getOrder();
        $store = $creditmemo->getStore();
        $invoice = $creditmemo->getInvoice();

        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());

        $hideDiscountAmount = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_APPLY_AFTER_DISCOUNT, $store);

        $request = array(
            'Client'        => 'Aoe_AvaTax',
            'CompanyCode'   => $this->limit($this->getHelper()->getConfig('company_code', $store), 25),
            'DocType'       => ($commit ? 'ReturnInvoice' : 'ReturnOrder'),
            'DocCode'       => $this->limit($this->getHelper()->getCreditmemoDocCode($creditmemo), 50),
            'ReferenceCode' => ($invoice ? $this->getHelper()->getInvoiceDocCode($invoice) : $this->getHelper()->getOrderDocCode($order)),
            'Commit'        => $commit,
            'DetailLevel'   => 'Tax',
            'DocDate'       => $creditmemo->getCreatedAtDate()->toString('yyyy-MM-dd'),
            'CustomerCode'  => $this->getHelper()->getCustomerDocCode($customer) ?: $this->getHelper()->getOrderDocCode($order),
            'CurrencyCode'  => $this->limit($creditmemo->getBaseCurrencyCode(), 3),
            'Discount'      => ($hideDiscountAmount ? 0.0 : -$store->roundPrice($creditmemo->getBaseDiscountAmount())),
            'Addresses'     => array(),
            'Lines'         => array(),
        );

        if ($order->getCustomerTaxvat()) {
            $request['BusinessIdentificationNo'] = $this->limit($order->getCustomerTaxvat(), 25);
        }

        $request['Addresses'][] = $this->getOriginAddress('ORIGIN', $store);
        $request['Addresses'][] = $this->getAddress('DESTINATION', $order->getShippingAddress());

        $itemPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX, $store);
        foreach ($this->getHelper()->getActionableCreditmemoItems($creditmemo) as $k => $item) {
            /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
            $request['Lines'][] = array(
                "LineNo"          => $this->limit($k, 50),
                "ItemCode"        => $this->limit($item->getSku(), 50),
                "Qty"             => round(floatval($item->getQty()), 4),
                "Amount"          => -($store->roundPrice($itemPriceIncludesTax ? $item->getBaseRowTotalInclTax() : $item->getBaseRowTotal()) - $store->roundPrice($item->getBaseDiscountAmount())),
                "OriginCode"      => "ORIGIN",
                "DestinationCode" => "DESTINATION",
                "Description"     => $this->limit($item->getDescription(), 255),
                "TaxCode"         => $this->limit($this->getHelper()->getProductTaxCode($item->getOrderItem()->getProduct()), 25),
                "Discounted"      => ($item->getBaseDiscountAmount() > 0.0),
                "TaxIncluded"     => $itemPriceIncludesTax,
                "Ref1"            => $this->limit($this->getHelper()->getCreditmemoItemRef1($item, $store), 250),
                "Ref2"            => $this->limit($this->getHelper()->getCreditmemoItemRef2($item, $store), 250),
            );
        }

        $shippingPriceIncludesTax = Mage::getStoreConfigFlag(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_INCLUDES_TAX, $store);
        $request['Lines'][] = array(
            "LineNo"          => "SHIPPING",
            "ItemCode"        => "SHIPPING",
            "Qty"             => "1",
            "Amount"          => -($store->roundPrice($shippingPriceIncludesTax ? $creditmemo->getBaseShippingInclTax() : $creditmemo->getBaseShippingAmount()) - $store->roundPrice($creditmemo->getBaseShippingDiscountAmount())),
            "OriginCode"      => "ORIGIN",
            "DestinationCode" => "DESTINATION",
            "Description"     => $this->limit("Shipping: " . $order->getShippingMethod(), 255),
            "TaxCode"         => $this->limit($this->getHelper()->getShippingTaxCode($store), 25),
            "Discounted"      => ($creditmemo->getBaseShippingDiscountAmount() > 0.0),
            "TaxIncluded"     => $shippingPriceIncludesTax,
            "Ref1"            => $this->limit($order->getShippingMethod(), 250),
        );

        // TODO: Handle giftwrapping

        return $request;
    }

    protected function prepareRequest(array $request, $filter = false, $filterStrict = false)
    {
        if ($filter) {
            $request = $this->recursiveFilter($request, $filterStrict);
        }

        // Sort by key
        $request = $this->recursiveKeySort($request);

        return $request;
    }

    protected function prepareGetTaxRequest(array $request)
    {
        // Filter out empty data
        $request = $this->prepareRequest($request, true, true);

        // Reset array keys in case of filtering
        $request['Addresses'] = array_values($request['Addresses']);
        $request['Lines'] = array_values($request['Lines']);

        return $request;
    }

    protected function validateTaxRequest(array $request)
    {
        if (count($request['Addresses']) < 2) {
            return array(
                'ResultCode' => 'Error',
                'Messages'   => array(
                    array(
                        'Summary'  => 'At least 2 addresses are required.',
                        'Details'  => 'At least 2 addresses are required.',
                        'RefersTo' => 'Addresses',
                        'Severity' => 'Error',
                        'Source'   => 'Aoe_AvaTax'
                    )
                )
            );
        }

        if (!count($request['Lines'])) {
            return array(
                'ResultCode' => 'Error',
                'Messages'   => array(
                    array(
                        'Summary'  => 'At least 1 line is required.',
                        'Details'  => 'At least 1 line is required.',
                        'RefersTo' => 'Lines',
                        'Severity' => 'Error',
                        'Source'   => 'Aoe_AvaTax'
                    )
                )
            );
        }

        return array();
    }

    protected function recursiveFilter(array $data, $strict = false)
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = $this->recursiveFilter($v, $strict);
                if (count($v) > 0) {
                    $data[$k] = $v;
                } else {
                    unset($data[$k]);
                }
            } elseif ($strict) {
                if ($v === null || $v === '') {
                    unset($data[$k]);
                }
            } elseif (empty($v)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
}
