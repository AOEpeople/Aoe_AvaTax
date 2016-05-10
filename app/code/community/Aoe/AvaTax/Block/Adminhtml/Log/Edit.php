<?php

/**
 * AvaTax Log admin edit form
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * constructor
     *
     * @return void
     * @author Manish Jain
     */
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'Aoe_AvaTax';
        $this->_controller = 'adminhtml_log';
        $this->_removeButton('save');
        $this->_removeButton('delete');
        $this->_removeButton('reset');
    }

    /**
     * Retrieve avatax log model object
     *
     * @return Aoe_AvaTax_Model_Log
     */
    public function getAvaTaxLog()
    {
        return Mage::registry('current_avatax_log');
    }

    /**
     * Retrieve AvaTax Log Identifier
     *
     * @return int
     */
    public function getAvaTaxLogId()
    {
        return $this->getAvaTaxLog()->getId();
    }

    /**
     * get the edit form header
     * @access public
     * @return string
     * @author Manish Jain
     */
    public function getHeaderText()
    {
        return Mage::helper('Aoe_AvaTax')->__('AvaTax Transaction Details # %s', $this->getAvaTaxLogId());
    }
}
