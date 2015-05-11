<?php

class Aoe_AvaTax_Model_Observer
{
    public function cleanLog(Mage_Cron_Model_Schedule $schedule)
    {
        foreach (Mage::app()->getStores() as $store) {
            /** @var Mage_Core_Model_Store $store */
            if (!$this->getHelper()->isActive($store)) {
                continue;
            }

            $successLifetime = intval($this->getHelper()->getConfig('log_success_lifetime', $store));
            $successLifetime = ($successLifetime > 0 ? $successLifetime : 60);
            /** @var Aoe_AvaTax_Resource_Log_Collection $logs */
            $logs = Mage::getSingleton('Aoe_AvaTax/Log')->getCollection();
            $logs->addFieldToFilter('store_id', $store->getId());
            $logs->addFieldToFilter('result_code', 'Success');
            $logs->addFieldToFilter('created_at', array('to' => new Zend_Db_Expr('DATE_SUB(CURDATE(). INTERVAL ' . $successLifetime . ' DAY)'), 'datetime' => true));
            $logs->walk('delete');

            $failureLifetime = intval($this->getHelper()->getConfig('log_failure_lifetime', $store));
            $failureLifetime = ($failureLifetime > 0 ? $failureLifetime : 60);
            /** @var Aoe_AvaTax_Resource_Log_Collection $logs */
            $logs = Mage::getSingleton('Aoe_AvaTax/Log')->getCollection();
            $logs->addFieldToFilter('store_id', $store->getId());
            $logs->addFieldToFilter('result_code', array('neq' => 'Success'));
            $logs->addFieldToFilter('created_at', array('to' => new Zend_Db_Expr('DATE_SUB(CURDATE(). INTERVAL ' . $failureLifetime . ' DAY)'), 'datetime' => true));
            $logs->walk('delete');
        }
    }

    public function registerInvoice(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Invoice $record */
        $record = $observer->getEvent()->getData('invoice');
        if (!$record instanceof Mage_Sales_Model_Order_Invoice) {
            return;
        }

        if (!$this->getHelper()->isActive($record->getStore())) {
            return;
        }

        $incrementId = $record->getIncrementId();
        if (empty($incrementId)) {
            /* @var $entityType Mage_Eav_Model_Entity_Type */
            $entityType = Mage::getModel('eav/entity_type')->loadByCode('invoice');
            $record->setIncrementId($entityType->fetchNewIncrementId($record->getStoreId()));
        }

        /** @var Aoe_AvaTax_Model_Api $api */
        $api = Mage::getModel('Aoe_AvaTax/RestApi');
        $result = $api->callGetTaxForInvoice($record, true);
        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        // NB: Ignoring the returned tax data on purpose

        $record->setAvataxDocument($result['DocCode']);
        $record->getOrder()->addStatusHistoryComment(sprintf('SalesInvoice sent to AvaTax (%s)', $result['DocCode']));
    }

    public function registerCreditmemo(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $record */
        $record = $observer->getEvent()->getData('creditmemo');
        if (!$record instanceof Mage_Sales_Model_Order_Creditmemo) {
            return;
        }

        if (!$this->getHelper()->isActive($record->getStore())) {
            return;
        }

        $incrementId = $record->getIncrementId();
        if (empty($incrementId)) {
            /* @var $entityType Mage_Eav_Model_Entity_Type */
            $entityType = Mage::getModel('eav/entity_type')->loadByCode('creditmemo');
            $record->setIncrementId($entityType->fetchNewIncrementId($record->getStoreId()));
        }

        /** @var Aoe_AvaTax_Model_Api $api */
        $api = Mage::getModel('Aoe_AvaTax/RestApi');
        $result = $api->callGetTaxForCreditmemo($record, true);
        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        // NB: Ignoring the returned tax data on purpose

        $record->setAvataxDocument($result['DocCode']);
        $record->getOrder()->addStatusHistoryComment(sprintf('RefundInvoice sent to AvaTax (%s)', $result['DocCode']));
    }

    /**
     * @return Aoe_AvaTax_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('Aoe_AvaTax/Data');
    }
}
