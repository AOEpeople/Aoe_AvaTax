<?php

/**
 * @see Mage_Tax_Model_Sales_Total_Quote_Nominal_Subtotal
 */
class Aoe_AvaTax_Model_Sales_Quote_Address_Total_Nominal_Subtotal extends Aoe_AvaTax_Model_Sales_Quote_Address_Total_Tax_Subtotal
{
    /**
     * Don't add amounts to address
     *
     * @var bool
     */
    protected $_canAddAmountToAddress = false;

    /**
     * Get nominal items only
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getAllNominalItems();
    }
}
