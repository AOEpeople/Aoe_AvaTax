<?php

/**
 * @see Mage_Sales_Model_Quote_Address_Total_Tax
 * @see Mage_Tax_Model_Sales_Total_Quote_Tax
 */
class Aoe_AvaTax_Model_Sales_Quote_Address_Total_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
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

        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);

        // Clear applied tax info
        $address->setAppliedTaxes(array());

        // Clear totals for this collector
        $address->setTotalAmount($this->getCode(), 0.0);
        $address->setBaseTotalAmount($this->getCode(), 0.0);

        // Clear subtotal taxes
        $address->setBaseSubtotalTotalInclTax($address->getBaseSubtotal());
        $address->setSubtotalInclTax($address->getSubtotal());

        // Clear shipping taxes (shipping cost is set/reset in shipping collector)
        $address->setBaseShippingInclTax($address->getBaseShippingAmount());
        $address->setShippingInclTax($address->getShippingAmount());
        $address->setBaseShippingTaxAmount(0);
        $address->setShippingTaxAmount(0);

        // Get all applicable items
        $items = $this->_getAddressItems($address);

        // Bail out early if possible
        if (!$items) {
            return $this;
        }

        // Clear item taxes
        foreach ($items as $item) {
            /** @var Mage_Sales_Model_Quote_Address_Item $item */
            // Recalculate row totals
            $item->calcRowTotal();

            // Copy row totals (set in calcRowTotal) to row w/tax totals
            $item->setBaseRowTotalInclTax($item->getBaseRowTotal());
            $item->setRowTotalInclTax($item->getRowTotal());

            // Set zero tax rate
            $item->setTaxPercent(0.0);
        }

        // Bail out early if possible
        if ($address->getAddressType() !== 'shipping' || $address->validate() !== true) {
            return $this;
        }

        // Get taxes via API call
        $api = $this->getHelper()->getApi($store);
        $result = $api->callGetTaxForQuote($address->getQuote());

        if ($result['ResultCode'] === 'Success') {
            $hasDisplayCurrency = ($address->getQuote()->getBaseCurrencyCode() !== $address->getQuote()->getQuoteCurrencyCode());
            $exchangeRate = ($hasDisplayCurrency ? $address->getQuote()->getBaseToQuoteRate() : 1.0);
            $shippingPriceIncludesTax = $this->_config->shippingPriceIncludesTax($store);
            $itemPriceIncludesTax = $this->_config->priceIncludesTax($store);
            foreach ($result['TaxLines'] as $line) {
                $itemId = $line['LineNo'];
                $rate = floatval($line['Rate']);
                $chargeTax = $store->roundPrice(floatval($line['Tax']));
                switch ($itemId) {
                    case 'SHIPPING':
                        // Store the tax amount
                        $address->setBaseShippingTaxAmount($chargeTax);
                        $address->setShippingTaxAmount($store->roundPrice($chargeTax * $exchangeRate));

                        if ($shippingPriceIncludesTax) {
                            // Remove tax from shipping total
                            $address->setBaseShippingAmount($address->getBaseShippingInclTax() - $address->getBaseShippingTaxAmount());
                            $address->setShippingAmount($address->getShippingInclTax() - $address->getShippingTaxAmount());
                            $address->addBaseTotalAmount('shipping', -$address->getBaseShippingTaxAmount());
                            $address->addTotalAmount('shipping', -$address->getShippingTaxAmount());
                        } else {
                            // Add tax to shipping w/tax total
                            $address->setBaseShippingInclTax($address->getBaseShippingAmount() + $address->getBaseShippingTaxAmount());
                            $address->setShippingInclTax($address->getShippingAmount() + $address->getShippingTaxAmount());
                        }

                        // Add shipping tax to tax total
                        $this->_addBaseAmount($address->getBaseShippingTaxAmount());
                        $this->_addAmount($address->getShippingTaxAmount());
                        break;
                    default:
                        /** @var Mage_Sales_Model_Quote_Address_Item $item */
                        $item = (isset($items[$itemId]) ? $items[$itemId] : false);
                        if (!$item) {
                            continue;
                        }

                        // Tax Rate Applied
                        $item->setTaxPercent($rate * 100.0);

                        // Store the tax amount
                        $item->setBaseTaxAmount($chargeTax);
                        $item->setTaxAmount($chargeTax * $exchangeRate);

                        if ($itemPriceIncludesTax) {
                            // Remove tax from item
                            $item->setBaseRowTotal($item->getBaseRowTotalInclTax() - $item->getBaseTaxAmount());
                            $item->setRowTotal($item->getRowTotalInclTax() - $item->getTaxAmount());

                            // Remove tax from subtotal
                            $address->setBaseSubtotal($address->getBaseSubtotal() - $item->getBaseTaxAmount());
                            $address->setSubtotal($address->getSubtotal() - $item->getTaxAmount());
                            $address->addBaseTotalAmount('subtotal', -$item->getBaseTaxAmount());
                            $address->addTotalAmount('subtotal', -$item->getTaxAmount());
                        } else {
                            // Add tax to item
                            $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() + $item->getBaseTaxAmount());
                            $item->setRowTotalInclTax($item->getRowTotalInclTax() + $item->getTaxAmount());

                            // Add tax to subtotal w/tax
                            $address->setBaseSubtotalTotalInclTax($address->getBaseSubtotalTotalInclTax() + $item->getBaseTaxAmount());
                            $address->setSubtotalInclTax($address->getSubtotalInclTax() + $item->getTaxAmount());
                        }

                        // Add item tax to tax total
                        $this->_addBaseAmount($item->getBaseTaxAmount());
                        $this->_addAmount($item->getTaxAmount());
                }
            }
        }

        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param Mage_Sales_Model_Quote_Address $address
     *
     * @return $this
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $store = $address->getQuote()->getStore();
        if (!$this->getHelper()->isActive($store)) {
            return parent::fetch($address);
        }

        Mage_Sales_Model_Quote_Address_Total_Abstract::fetch($address);

        $quote = $address->getQuote();
        $store = $quote->getStore();
        $amount = floatval($address->getTaxAmount());

        if ($amount != 0.0 || $this->_config->displayCartZeroTax($store)) {
            $fullInfo = array();
            $address->addTotal(
                array(
                    'code'      => $this->getCode(),
                    'title'     => Mage::helper('tax')->__('Tax'),
                    'full_info' => $fullInfo,
                    'value'     => $amount,
                )
            );
        }

        $displaySubtotal = ($this->_config->displayCartSubtotalInclTax($store) ? $address->getSubtotalInclTax() : $address->getSubtotal());
        $address->addTotal(
            array(
                'code'           => 'subtotal',
                'title'          => Mage::helper('sales')->__('Subtotal'),
                'value'          => $displaySubtotal,
                'value_incl_tax' => $address->getSubtotalInclTax(),
                'value_excl_tax' => $address->getSubtotal(),
            )
        );

        $displayShipping = ($this->_config->displayCartShippingInclTax($store) ? $address->getShippingInclTax() : $address->getShippingAmount());
        $address->addTotal(
            array(
                'code'           => 'shipping',
                'title'          => Mage::helper('sales')->__('Shipping'),
                'value'          => $displayShipping,
                'value_incl_tax' => $address->getShippingInclTax(),
                'value_excl_tax' => $address->getShippingAmount(),
            )
        );

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
