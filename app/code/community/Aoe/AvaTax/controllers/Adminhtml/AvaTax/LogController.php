<?php
/**
 * Adminhtml avatax log controller
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain <manish.jain@aoe.com>
 */
class Aoe_AvaTax_Adminhtml_AvaTax_LogController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Log list
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Tax'))->_title($this->__('AvaTax Log'));

        $this->loadLayout();
        $this->_setActiveMenu('sales/tax/avatax_log');
        $this->renderLayout();
    }

    /**
     * Render Banner grid
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Edit action
     *
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_initAvaTaxLog('id');

        if (!$model->getId() && $id) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('Aoe_AvaTax')->__('This avatax log no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }

        //$this->_title($model->getId() ? $this->__('AvaTax Log') : $this->__('New Banner'));

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->loadLayout();
        $this->_setActiveMenu('sales/tax/avatax_log');
        $breadcrumbMessage = Mage::helper('Aoe_AvaTax')->__('View AvaTax Log');
        $this->_addBreadcrumb($breadcrumbMessage, $breadcrumbMessage)
            ->renderLayout();
    }
    
    /**
     * export as csv - action
     * @access public
     * @return void
     * @author Manish Jain
     */
    public function exportCsvAction(){
        $fileName   = 'avatax_log.csv';
        $content    = $this->getLayout()->createBlock('Aoe_AvaTax/Adminhtml_Log_Grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     * @access public
     * @return void
     * @author Manish Jain
     */
    public function exportExcelAction(){
        $fileName   = 'avatax_log.xls';
        $content    = $this->getLayout()->createBlock('Aoe_AvaTax/Adminhtml_Log_Grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     * @access public
     * @return void
     * @author Manish Jain
     */
    public function exportXmlAction(){
        $fileName   = 'avatax_log.xml';
        $content    = $this->getLayout()->createBlock('Aoe_AvaTax/Adminhtml_Log_Grid')->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Load AvaTax log from request
     *
     * @param string $idFieldName
     * @return Aoe_AvaTax_Model_Log $model
     */
    protected function _initAvaTaxLog($idFieldName = 'id')
    {
        $this->_title($this->__('Sales'))->_title($this->__('Tax'))->_title($this->__('AvaTax Log'));;

        $id = (int)$this->getRequest()->getParam($idFieldName);
        $model = Mage::getModel('Aoe_AvaTax/log');
        if ($id) {
            $model->load($id);
        }
        if (!Mage::registry('current_avatax_log')) {
            Mage::register('current_avatax_log', $model);
        }
        return $model;
    }

    /**
     * Check if is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('aoe_avatax');
    }
}
