<?php

/**
 * Log admin block
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Initialize avatax log page
     *
     * @return void
     */
    public function __construct()
    {
        $this->_controller = 'adminhtml_log';
        $this->_blockGroup = 'Aoe_AvaTax';
        $this->_headerText = Mage::helper('Aoe_AvaTax')->__('AvaTax Logs');
        parent::__construct();
        $this->_removeButton('add');
    }
}
