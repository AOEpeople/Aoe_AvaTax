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

    public function registerInvoices(Mage_Cron_Model_Schedule $schedule)
    {
        $helper = $this->getHelper();

        /** @var Aoe_AvaTax_Model_Api $api */
        $api = Mage::getModel('Aoe_AvaTax/RestApi');

        foreach (Mage::app()->getStores() as $store) {
            /** @var Mage_Core_Model_Store $store */
            if (!$helper->isActive($store)) {
                continue;
            }

            $limit = max(intval($helper->getConfig('register_invoices_batch', $store)), 25);

            /** @var Mage_Sales_Model_Resource_Order_Invoice_Collection $invoices */
            $invoices = Mage::getSingleton('sales/order_invoice')->getCollection();
            $invoices->addFieldToFilter('store_id', $store->getId());
            $invoices->addFieldToFilter('avatax_document', array('null' => true));
            $invoices->addOrder('updated_at', 'ASC');

            if ($limit) {
                $invoices->setPageSize($limit);
            }

            foreach ($invoices as $invoice) {
                /** @var Mage_Sales_Model_Order_Invoice $invoice */
                try {
                    $helper->registerInvoice($api, $invoice);
                } catch (Exception $e) {
                    try {
                        $invoice->setDataChanges(true);
                        $invoice->addComment('Failed to register invoice with AvaTax: ' . $e->getMessage());
                        $invoice->save();
                    } catch (Exception $e2) {
                        Mage::logException($e2);
                    }
                    Mage::logException($e);
                }
            }
        }
    }

    public function registerCreditmemos(Mage_Cron_Model_Schedule $schedule)
    {
        $helper = $this->getHelper();

        /** @var Aoe_AvaTax_Model_Api $api */
        $api = Mage::getModel('Aoe_AvaTax/RestApi');

        foreach (Mage::app()->getStores() as $store) {
            /** @var Mage_Core_Model_Store $store */
            if (!$helper->isActive($store)) {
                continue;
            }

            $limit = max(intval($helper->getConfig('register_creditmemos_batch', $store)), 25);

            /** @var Mage_Sales_Model_Resource_Order_Creditmemo_Collection $creditmemos */
            $creditmemos = Mage::getSingleton('sales/order_invoice')->getCollection();
            $creditmemos->addFieldToFilter('store_id', $store->getId());
            $creditmemos->addFieldToFilter('avatax_document', array('null' => true));
            $creditmemos->addOrder('updated_at', 'ASC');

            if ($limit) {
                $creditmemos->setPageSize($limit);
            }

            foreach ($creditmemos as $creditmemo) {
                /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
                try {
                    $helper->registerInvoice($api, $creditmemo);
                } catch (Exception $e) {
                    try {
                        $creditmemo->setDataChanges(true);
                        $creditmemo->addComment('Failed to register creditmemo with AvaTax: ' . $e->getMessage());
                        $creditmemo->save();
                    } catch (Exception $e2) {
                        Mage::logException($e2);
                    }
                    Mage::logException($e);
                }
            }
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
