<?php

class Aoe_AvaTax_Model_Sales_Creditmemo extends Mage_Sales_Model_Order_Creditmemo
{
    /**
     * Register creditmemo
     *
     * NB: This override is used to trigger a missing event in the core code
     *
     * @return $this
     */
    public function register()
    {
        Mage::dispatchEvent($this->_eventPrefix . '_register', array($this->_eventObject => $this, 'order' => $this->getOrder()));

        return $this;
    }
}
