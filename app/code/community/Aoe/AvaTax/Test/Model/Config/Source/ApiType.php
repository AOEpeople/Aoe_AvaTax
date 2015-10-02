<?php

/**
 * @see Aoe_AvaTax_Model_Config_Source_ApiType
 */
class Aoe_AvaTax_Test_Model_Config_Source_ApiType extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @test
     *
     * @coversNothing
     *
     * @return Aoe_AvaTax_Model_Config_Source_ApiType
     */
    public function loadModel()
    {
        /** @var Aoe_AvaTax_Model_Config_Source_ApiType $model */
        $model = Mage::getModel('Aoe_AvaTax/Config_Source_ApiType');
        $this->assertInstanceOf('Aoe_AvaTax_Model_Config_Source_ApiType', $model);

        return $model;
    }

    /**
     * @test
     *
     * @coversNothing
     *
     * @return Aoe_AvaTax_Helper_Data
     */
    public function loadHelper()
    {
        /** @var Aoe_AvaTax_Helper_Data $helper */
        $helper = Mage::helper('Aoe_AvaTax/Data');
        $this->assertInstanceOf('Aoe_AvaTax_Helper_Data', $helper);

        return $helper;
    }

    /**
     * @test
     *
     * @depends loadModel
     * @depends loadHelper
     * @covers  Aoe_AvaTax_Model_Config_Source_ApiType::toOptionArray
     *
     * @param Aoe_AvaTax_Model_Config_Source_ApiType $model
     * @param Aoe_AvaTax_Helper_Data                 $helper
     */
    public function toOptionArray(Aoe_AvaTax_Model_Config_Source_ApiType $model, Aoe_AvaTax_Helper_Data $helper)
    {
        $expected = [
            ['value' => 'rest', 'label' => $helper->__('REST')],
            ['value' => 'soap', 'label' => $helper->__('SOAP')],
        ];

        $this->assertEquals($expected, $model->toOptionArray());
    }

    /**
     * @test
     *
     * @depends loadModel
     * @depends loadHelper
     * @covers  Aoe_AvaTax_Model_Config_Source_ApiType::toOptionHash
     *
     * @param Aoe_AvaTax_Model_Config_Source_ApiType $model
     * @param Aoe_AvaTax_Helper_Data                 $helper
     */
    public function toOptionHash(Aoe_AvaTax_Model_Config_Source_ApiType $model, Aoe_AvaTax_Helper_Data $helper)
    {
        $expected = [
            'rest' => $helper->__('REST'),
            'soap' => $helper->__('SOAP'),
        ];

        $this->assertEquals($expected, $model->toOptionHash());
    }
}
