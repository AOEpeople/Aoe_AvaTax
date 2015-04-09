<?php

class Aoe_AvaTax_Block_Adminhtml_Tax_Class_Grid extends Mage_Adminhtml_Block_Tax_Class_Grid
{
    protected function _prepareColumns()
    {
        if ($this->getClassType() === Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT) {
            $this->addColumnAfter(
                'avatax_code',
                array(
                    'index'  => 'avatax_code',
                    'header' => $this->__('AvaTax Code'),
                    'width'  => '150px',
                ),
                'class_name'
            );
        }

        return parent::_prepareColumns();
    }
}
