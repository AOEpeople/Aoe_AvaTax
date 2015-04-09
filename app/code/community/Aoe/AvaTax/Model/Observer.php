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

    /**
     * @return Aoe_AvaTax_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('Aoe_AvaTax/Data');
    }
}
