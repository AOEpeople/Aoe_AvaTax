<?php

class Aoe_AvaTax_Block_Adminhtml_Tax_Class_Edit_Form extends Mage_Adminhtml_Block_Tax_Class_Edit_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        if ($this->getClassType() === Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
            $form = $this->getForm();
            $fieldset = $form->getElement('base_fieldset');
            if ($fieldset instanceof Varien_Data_Form_Element_Fieldset) {
                $model = Mage::registry('tax_class');
                $value = ($model instanceof Mage_Tax_Model_Class ? $model->getAvataxCode() : '');
                $fieldset->addField(
                    'avatax_code',
                    'text',
                    array(
                        'name'     => 'avatax_code',
                        'label'    => Mage::helper('tax')->__('AvaTax Code'),
                        'class'    => '',
                        'value'    => $value,
                        'required' => false,
                    )
                );
            }
        }

        return $this;
    }
}
