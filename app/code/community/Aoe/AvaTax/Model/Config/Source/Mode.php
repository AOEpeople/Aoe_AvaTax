<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 * @since  2015-04-06
 */
class Aoe_AvaTax_Model_Config_Source_Mode
{
    public function toOptionArray()
    {
        $helper = Mage::helper('Aoe_AvaTax/Data');
        return array(
            array('value' => 'development', 'label' => $helper->__('Development')),
            array('value' => 'production', 'label' => $helper->__('Production')),
        );
    }

    public function toOptionHash()
    {
        $helper = Mage::helper('Aoe_AvaTax/Data');
        return array(
            'development' => $helper->__('Development'),
            'production'  => $helper->__('Production'),
        );
    }
}
