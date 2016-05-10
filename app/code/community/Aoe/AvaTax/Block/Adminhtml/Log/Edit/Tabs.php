<?php

/**
 * Log admin edit tabs
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     * @access public
     * @author Manish Jain
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('avatax_log_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('Aoe_AvaTax')->__('AvaTax Log'));
    }

    /**
     * before render html
     * @access protected
     * @return Aoe_AvaTax_Block_Adminhtml_Log_Edit_Tabs
     * @author Manish Jain
     */
    protected function _beforeToHtml()
    {
        $this->addTab('form_log', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('General'),
            'title' => Mage::helper('Aoe_AvaTax')->__('General'),
            'content' => $this->getLayout()->createBlock('Aoe_AvaTax/adminhtml_log_edit_tab_form')->toHtml(),
        ));
        $this->addTab('request_log', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('Request'),
            'title' => Mage::helper('Aoe_AvaTax')->__('Request'),
            'content' => $this->getLayout()->createBlock('Aoe_AvaTax/adminhtml_log_edit_tab_request')->toHtml(),
        ));
        $this->addTab('response_log', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('Response'),
            'title' => Mage::helper('Aoe_AvaTax')->__('Response'),
            'content' => $this->getLayout()->createBlock('Aoe_AvaTax/adminhtml_log_edit_tab_response')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}
