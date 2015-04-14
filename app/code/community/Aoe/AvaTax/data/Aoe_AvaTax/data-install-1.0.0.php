<?php
/** @var Mage_Tax_Model_Resource_Class_Collection $taxClasses */
$taxClasses = Mage::getModel('tax/class')->getCollection();
$taxClasses->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
$taxClasses->addFieldToFilter('class_name', array('Shipping', 'Shipping (not used by AvaTax)'));
foreach ($taxClasses as $taxClass) {
    /** @var Mage_Tax_Model_Class $taxClass */
    $taxClass->setAvataxCode('FR020100')->save();
}
