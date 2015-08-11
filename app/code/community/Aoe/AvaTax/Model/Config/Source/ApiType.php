<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 */
class Aoe_AvaTax_Model_Config_Source_ApiType
{
    public function toOptionArray()
    {
        $helper = Mage::helper('Aoe_AvaTax/Data');

        return array(
            array('value' => 'rest', 'label' => $helper->__('REST')),
            array('value' => 'soap', 'label' => $helper->__('SOAP')),
        );
    }

    public function toOptionHash()
    {
        $helper = Mage::helper('Aoe_AvaTax/Data');

        return array(
            'rest' => $helper->__('REST'),
            'soap' => $helper->__('SOAP'),
        );
    }
}
