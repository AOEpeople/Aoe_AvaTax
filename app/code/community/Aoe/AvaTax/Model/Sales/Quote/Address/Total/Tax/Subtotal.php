<?php

/**
 * @see Mage_Sales_Model_Quote_Address_Total_Subtotal
 * @see Mage_Tax_Model_Sales_Total_Quote_Subtotal
 */
class Aoe_AvaTax_Model_Sales_Quote_Address_Total_Tax_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal
{
    /**
     * Collect totals process.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $store = $address->getQuote()->getStore();
        if (!$this->getHelper()->isActive($store)) {
            return parent::collect($address);
        }

        // We do all the subtotal tax processing in the tax total collector

        return $this;
    }

    /**
     * @return Aoe_AvaTax_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('Aoe_AvaTax/Data');
    }
}
