<?php

abstract class Aoe_AvaTax_Model_Api
{
    abstract public function callGetTaxForQuote(Mage_Sales_Model_Quote $quote);

    abstract public function callGetTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice, $commit = false);

    abstract public function callVoidTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice);

    abstract public function callDeleteTaxForInvoice(Mage_Sales_Model_Order_Invoice $invoice);

    abstract public function callGetTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo, $commit = false);

    abstract public function callVoidTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo);

    abstract public function callDeleteTaxForCreditmemo(Mage_Sales_Model_Order_Creditmemo $creditmemo);

    protected function getMode(Mage_Core_Model_Store $store)
    {
        $mode = $this->getHelper()->getConfig('mode', $store);
        return ($mode === 'production' ? 'production' : 'sandbox');
    }

    protected function getBaseUrl(Mage_Core_Model_Store $store)
    {
        return trim($this->getHelper()->getConfig($this->getMode($store) . '_url', $store), ' /');
    }

    protected function getAccount(Mage_Core_Model_Store $store)
    {
        return trim($this->getHelper()->getConfig('account', $store));
    }

    protected function getLicense(Mage_Core_Model_Store $store)
    {
        return trim($this->getHelper()->getConfig('license', $store));
    }

    protected function getTimeout(Mage_Core_Model_Store $store)
    {
        return intval($this->getHelper()->getConfig('api_timeout', $store));
    }

    /**
     * @param array $data
     * @param array $extra
     *
     * @return string
     */
    protected function generateHash(array $data, array $extra = array())
    {
        return sha1(json_encode($this->recursiveKeySort(array($data, $extra))));
    }

    /**
     * @param string $hash
     *
     * @return array|false
     */
    protected function loadResult($hash)
    {
        $result = Mage::app()->loadCache('Aoe_AvaTax_CACHE_' . $hash);

        if ($result) {
            $result = json_decode($result, true);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param string                $hash
     * @param stdClass|array|string $result
     *
     * @return $this
     */

    protected function saveResult($hash, $result)
    {
        Mage::app()->saveCache(
            (is_string($result) ? $result : json_encode($result)),
            'Aoe_AvaTax_CACHE_' . $hash,
            array('Aoe_AvaTax_CACHE'),
            36000
        );

        return $this;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     * @param array                 $result
     */
    protected function logRequestResult(Mage_Core_Model_Store $store, array $request, array $result)
    {
        try {
            /** @var Aoe_AvaTax_Model_Log $log */
            $log = Mage::getModel('Aoe_AvaTax/Log');
            $log->setCreatedAt(new Zend_Db_Expr('NOW()'));
            $log->setStore($store);
            $log->setUrl($this->getBaseUrl($store));
            $log->setRequestBody(is_string($request) ? $request : json_encode($request));
            $log->setResultBody(is_string($result) ? $result : json_encode($result));
            $log->setResultCode(isset($result['ResultCode']) ? $result['ResultCode'] : '');
            $log->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param array                 $request
     * @param Exception             $exception
     */
    protected function logRequestException(Mage_Core_Model_Store $store, array $request, Exception $exception)
    {
        try {
            /** @var Aoe_AvaTax_Model_Log $log */
            $log = Mage::getModel('Aoe_AvaTax/Log');
            $log->setCreatedAt(new Zend_Db_Expr('NOW()'));
            $log->setStore($store);
            $log->setUrl($this->getBaseUrl($store));
            $log->setRequestBody(is_string($request) ? $request : json_encode($request));
            $log->setFailureMessage($exception->getMessage());
            $log->setResultCode(Aoe_AvaTax_Model_Log::CODE_FAILURE);
            $log->save();

            Mage::logException($exception);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @return Aoe_AvaTax_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('Aoe_AvaTax/Data');
    }

    protected function recursiveKeySort(array $data)
    {
        ksort($data);
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->recursiveKeySort($v);
            }
        }
        return $data;
    }

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
