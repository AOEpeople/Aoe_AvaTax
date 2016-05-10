<?php

/**
 * AvaTax Log edit form
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */


class Aoe_AvaTax_Block_Adminhtml_Log_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    /**
     * prepare form
     * @access protected
     * @return Aoe_AvaTax_Block_Adminhtml_Log_Edit_Form
     * @author Manish Jain
     */
    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
                        'id'         => 'edit_form',
                        'action'     => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                        'method'     => 'post',
                        'enctype'    => 'multipart/form-data'
                    )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
