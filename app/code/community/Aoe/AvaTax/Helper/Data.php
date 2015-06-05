<?php

class Aoe_AvaTax_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_PREFIX = 'tax/aoe_avatax';
    const DOC_CODE_SEPARATOR = '-';

    /**
     * @param mixed $store
     *
     * @return bool
     */
    public function isActive($store = null)
    {
        return $this->getConfigFlag('active', $store);
    }

    /**
     * Generate and send a SalesInvoice
     *
     * @param Aoe_AvaTax_Model_Api           $api
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return string
     * @throws Aoe_AvaTax_Exception
     * @throws Exception
     */
    public function registerInvoice(Aoe_AvaTax_Model_Api $api, Mage_Sales_Model_Order_Invoice $invoice)
    {
        if (!$this->isActive($invoice->getStore())) {
            return null;
        }

        $incrementId = $invoice->getIncrementId();
        if (empty($incrementId)) {
            /* @var $entityType Mage_Eav_Model_Entity_Type */
            $entityType = Mage::getModel('eav/entity_type')->loadByCode('invoice');
            $invoice->setIncrementId($entityType->fetchNewIncrementId($invoice->getStoreId()));
        }

        $result = $api->callGetTaxForInvoice($invoice, true);
        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        // NB: Ignoring the returned tax data on purpose

        $invoice->getResource()->beginTransaction();

        try {
            $invoice->setAvataxDocument($result['DocCode']);
            $invoice->addComment(sprintf('SalesInvoice sent to AvaTax (%s)', $result['DocCode']));
            $invoice->save();

            $invoice->getOrder()->addStatusHistoryComment(sprintf('SalesInvoice sent to AvaTax (%s)', $result['DocCode']));
            $invoice->getOrder()->save();

            $invoice->getResource()->commit();
        } catch (Exception $e) {
            $invoice->getResource()->rollBack();
            throw $e;
        }

        return $result['DocCode'];
    }

    public function voidInvoice(Aoe_AvaTax_Model_Api $api, Mage_Sales_Model_Order_Invoice $invoice)
    {
        if (!$this->isActive($invoice->getStore())) {
            return;
        }

        $result = $api->callVoidTaxForInvoice($invoice);
        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        $invoice->getOrder()->addStatusHistoryComment(sprintf('Voided SalesInvoice sent to AvaTax (%s)', $this->getInvoiceDocCode($invoice)));
        $invoice->getOrder()->save();
    }

    /**
     * Generate and send a RefundInvoice
     *
     * @param Aoe_AvaTax_Model_Api              $api
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return string
     * @throws Aoe_AvaTax_Exception
     * @throws Exception
     */
    public function registerCreditmemo(Aoe_AvaTax_Model_Api $api, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if (!$this->isActive($creditmemo->getStore())) {
            return null;
        }

        $incrementId = $creditmemo->getIncrementId();
        if (empty($incrementId)) {
            /* @var $entityType Mage_Eav_Model_Entity_Type */
            $entityType = Mage::getModel('eav/entity_type')->loadByCode('creditmemo');
            $creditmemo->setIncrementId($entityType->fetchNewIncrementId($creditmemo->getStoreId()));
        }

        $result = $api->callGetTaxForCreditmemo($creditmemo, true);
        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        // NB: Ignoring the returned tax data on purpose

        $creditmemo->getResource()->beginTransaction();

        try {
            $creditmemo->setAvataxDocument($result['DocCode']);
            $creditmemo->addComment(sprintf('RefundInvoice sent to AvaTax (%s)', $result['DocCode']));
            $creditmemo->save();

            $creditmemo->getOrder()->addStatusHistoryComment(sprintf('RefundInvoice sent to AvaTax (%s)', $result['DocCode']));
            $creditmemo->getOrder()->save();

            $creditmemo->getResource()->commit();
        } catch (Exception $e) {
            $creditmemo->getResource()->rollBack();
            throw $e;
        }

        return $result['DocCode'];
    }

    public function getInvoicedSum(Mage_Sales_Model_Order_Invoice $invoice, $attribute)
    {
        $amount = 0.0;

        foreach ($invoice->getOrder()->getInvoiceCollection() as $previous) {
            /** @var Mage_Sales_Model_Order_Invoice $previous */
            if ($previous->getState() !== Mage_Sales_Model_Order_Invoice::STATE_CANCELED) {
                $amount += floatval($previous->getDataUsingMethod($attribute));
            }
        }

        return $amount;
    }

    public function getCreditedSum(Mage_Sales_Model_Order_Creditmemo $creditmemo, $attribute)
    {
        $amount = 0.0;

        foreach ($creditmemo->getOrder()->getCreditmemosCollection() as $previous) {
            /** @var Mage_Sales_Model_Order_Creditmemo $previous */
            if ($previous->getState() !== Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED) {
                $amount += floatval($previous->getDataUsingMethod($attribute));
            }
        }

        return $amount;
    }

    public function isShippingInvoiced(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $ordered_shipping_amount = floatval($invoice->getOrder()->getBaseShippingAmount());
        $invoiced_shipping_amount = $this->getInvoicedSum($invoice, 'base_shipping_amount');

        return ($invoiced_shipping_amount >= $ordered_shipping_amount);
    }

    public function isShippingCredited(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $ordered_shipping_amount = floatval($creditmemo->getOrder()->getBaseShippingAmount());
        $credited_shipping_amount = $this->getCreditedSum($creditmemo, 'base_shipping_amount');

        return ($credited_shipping_amount >= $ordered_shipping_amount);
    }

    /**
     * @param int $taxCodeId
     *
     * @return string|null
     */
    public function getTaxCode($taxCodeId)
    {
        return Mage::getModel('tax/class')->load($taxCodeId)->getDataUsingMethod('avatax_code') ?: null;
    }

    /**
     * @param int $taxCodeId
     *
     * @return string|null
     */
    public function getProductTaxCode(Mage_Catalog_Model_Product $product)
    {
        return $this->getTaxCode($product->getTaxClassId());
    }

    /**
     * @param Mage_Core_Model_Store|int|null $store
     *
     * @return null|string
     */
    public function getShippingTaxCode($store = null)
    {
        return $this->getTaxCode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $store));
    }

    public function getQuoteItemRef1(Mage_Sales_Model_Quote_Item_Abstract $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('quote_item_ref1_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getQuoteItemRef2(Mage_Sales_Model_Quote_Item_Abstract $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('quote_item_ref2_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getInvoiceItemRef1(Mage_Sales_Model_Order_Invoice_Item $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('invoice_item_ref1_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getInvoiceItemRef2(Mage_Sales_Model_Order_Invoice_Item $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('invoice_item_ref2_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getCreditmemoItemRef1(Mage_Sales_Model_Order_Creditmemo_Item $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('creditmemo_item_ref1_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getCreditmemoItemRef2(Mage_Sales_Model_Order_Creditmemo_Item $item, $store = null)
    {
        $value = null;
        $attributeCode = $this->getConfig('creditmemo_item_ref2_attribute', $store);
        if ($attributeCode) {
            $value = $this->getObjectData($item, $attributeCode);
        }
        return $value;
    }

    public function getCustomerDocCode(Mage_Customer_Model_Customer $customer)
    {
        $prefix = trim($this->getConfig('customer_prefix', $customer->getStore()), self::DOC_CODE_SEPARATOR);
        $prefix = (empty($prefix) ? 'C' : $prefix) . self::DOC_CODE_SEPARATOR;

        if ($customer->getId()) {
            return $prefix . $customer->getId();
        } else {
            return null;
        }
    }

    public function getQuoteDocCode(Mage_Sales_Model_Quote $quote)
    {
        $prefix = trim($this->getConfig('quote_prefix', $quote->getStore()), self::DOC_CODE_SEPARATOR);
        $prefix = (empty($prefix) ? 'Q' : $prefix) . self::DOC_CODE_SEPARATOR;

        if ($quote->getId()) {
            return $prefix . $quote->getId();
        } else {
            return null;
        }
    }

    public function getOrderDocCode(Mage_Sales_Model_Order $order)
    {
        $prefix = trim($this->getConfig('order_prefix', $order->getStore()), self::DOC_CODE_SEPARATOR);
        $prefix = (empty($prefix) ? 'O' : $prefix) . self::DOC_CODE_SEPARATOR;

        if ($order->getIncrementId()) {
            return $prefix . $order->getIncrementId();
        } else {
            return null;
        }
    }

    public function getInvoiceDocCode(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $prefix = trim($this->getConfig('invoice_prefix', $invoice->getStore()), self::DOC_CODE_SEPARATOR);
        $prefix = (empty($prefix) ? 'I' : $prefix) . self::DOC_CODE_SEPARATOR;

        if ($invoice->getAvataxDocument()) {
            return $invoice->getAvataxDocument();
        } elseif ($invoice->getIncrementId()) {
            return $prefix . $invoice->getIncrementId();
        } else {
            return null;
        }
    }

    public function getCreditmemoDocCode(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $prefix = trim($this->getConfig('creditmemo_prefix', $creditmemo->getStore()), self::DOC_CODE_SEPARATOR);
        $prefix = (empty($prefix) ? 'R' : $prefix) . self::DOC_CODE_SEPARATOR;

        if ($creditmemo->getAvataxDocument()) {
            return $creditmemo->getAvataxDocument();
        } elseif ($creditmemo->getIncrementId()) {
            return $prefix . $creditmemo->getIncrementId();
        } else {
            return null;
        }
    }

    /**
     * @param string $key
     * @param mixed  $store
     *
     * @return mixed
     */
    public function getConfig($key, $store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_PREFIX . '/' . ltrim($key, '/'), $store);
    }

    /**
     * @param string $key
     * @param mixed  $store
     *
     * @return bool
     */
    public function getConfigFlag($key, $store = null)
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PREFIX . '/' . ltrim($key, '/'), $store);
    }

    public function getMode($store = null)
    {
        $mode = $this->getConfig('mode', $store);
        return ($mode === 'production' ? 'production' : 'sandbox');
    }

    public function getAccount($store = null)
    {
        return trim($this->getConfig('account', $store));
    }

    public function getLicense($store = null)
    {
        return trim($this->getConfig('license', $store));
    }

    public function getUrl($store = null)
    {
        return trim($this->getConfig($this->getMode($store) . '_url', $store));
    }

    /**
     * @param array $data
     * @param array $extra
     *
     * @return string
     */
    protected function generateHash(array $data, array $extra = array())
    {
        return sha1(json_encode(array($data, $extra)));
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     *
     * @return array|false
     */
    public function loadResult(Mage_Core_Model_Store $store, array $request)
    {
        $extra = array(
            $this->getAccount($store),
            $this->getLicense($store),
            $this->getMode($store)
        );

        $hash = $this->generateHash($request, $extra);

        $result = Mage::app()->loadCache('Aoe_AvaTax_CACHE_' . $hash);

        if ($result) {
            $result = json_decode($result, true);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     * @param array                 $result
     *
     * @return $this
     */
    public function saveResult(Mage_Core_Model_Store $store, array $request, array $result)
    {
        $extra = array(
            $this->getAccount($store),
            $this->getLicense($store),
            $this->getMode($store)
        );

        $hash = $this->generateHash($request, $extra);

        $cacheTimeout = max(intval($this->getConfig('api_cache_timeout')), 0);

        if ($cacheTimeout > 0 && isset($result['ResultCode']) && $result['ResultCode'] === 'Error') {
            $cacheTimeout = intval(ceil($cacheTimeout / 10));
        }

        if ($cacheTimeout > 0) {
            Mage::app()->saveCache(
                json_encode($result),
                'Aoe_AvaTax_CACHE_' . $hash,
                array('Aoe_AvaTax_CACHE'),
                $cacheTimeout
            );
        }

        return $this;
    }


    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     * @param array                 $result
     */
    public function logRequestResult(Mage_Core_Model_Store $store, array $request, array $result)
    {
        try {
            /** @var Aoe_AvaTax_Model_Log $log */
            $log = Mage::getModel('Aoe_AvaTax/Log');
            $log->setCreatedAt(new Zend_Db_Expr('NOW()'));
            $log->setStore($store);
            $log->setUrl($this->getUrl($store));
            $log->setRequestBody(json_encode($request));
            $log->setResultBody(json_encode($result));
            $log->setResultCode(isset($result['ResultCode']) ? $result['ResultCode'] : '');
            $log->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     * @param array                 $result
     * @param Exception             $exception
     */
    public function logRequestException(Mage_Core_Model_Store $store, array $request, array $result, Exception $exception)
    {
        try {
            /** @var Aoe_AvaTax_Model_Log $log */
            $log = Mage::getModel('Aoe_AvaTax/Log');
            $log->setCreatedAt(new Zend_Db_Expr('NOW()'));
            $log->setStore($store);
            $log->setUrl($this->getUrl($store));
            $log->setRequestBody(json_encode($request));
            $log->setResultBody(json_encode($result));
            $log->setFailureMessage($exception->getMessage());
            $log->setResultCode(Aoe_AvaTax_Model_Log::CODE_FAILURE);
            $log->save();

            Mage::logException($exception);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function limit($value, $limit = 0)
    {
        $value = trim($value);
        $limit = intval($limit);
        if ($limit > 0) {
            $value = substr($value, 0, $limit);
        }

        return $value;
    }

    public function recursiveKeySort(array $data)
    {
        ksort($data);
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->recursiveKeySort($v);
            }
        }
        return $data;
    }

    public function recursiveFilter(array $data, $strict = false)
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

    public function getObjectData(Varien_Object $object, $key)
    {
        $value = null;

        if (strpos($key, '/') === false) {
            $value = $object->getDataUsingMethod($key);
        } else {
            $key = explode('/', $key);
            $value = $object;
            foreach ($key as $i => $k) {
                if ($k === '') {
                    $value = null;
                    break;
                }
                if (is_array($value)) {
                    if (!isset($value[$k])) {
                        $value = null;
                        break;
                    }
                    $value = $value[$k];
                } elseif ($value instanceof Varien_Object) {
                    $value = $value->getDataUsingMethod($k);
                } else {
                    $value = null;
                    break;
                }
            }
        }

        return $value;
    }
}
