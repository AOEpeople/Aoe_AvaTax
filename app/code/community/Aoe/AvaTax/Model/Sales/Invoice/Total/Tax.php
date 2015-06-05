<?php

class Aoe_AvaTax_Model_Sales_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Tax
{
    /**
     * Collect invoice tax amount
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Mage_Sales_Model_Order_Invoice_Total_Tax
     * @throws Aoe_AvaTax_Exception
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $store = $invoice->getStore();
        if (!$this->getHelper()->isActive($store)) {
            return parent::collect($invoice);
        }

        $invoice->setTaxAmount(0.0);
        $invoice->setBaseTaxAmount(0.0);
        $invoice->setHiddenTaxAmount(0.0);
        $invoice->setBaseHiddenTaxAmount(0.0);

        $items = $invoice->getAllItems();

        // Get taxes via API call
        $api = $this->getHelper()->getApi($store);
        $result = $api->callGetTaxForInvoice($invoice);

        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        $totalTax = 0;
        $baseTotalTax = 0;

        /** @var Mage_Tax_Model_Config $taxConfig */
        $taxConfig = Mage::getSingleton('tax/config');
        $hasDisplayCurrency = ($invoice->getBaseCurrencyCode() !== $invoice->getOrderCurrencyCode());
        $exchangeRate = ($hasDisplayCurrency ? $invoice->getBaseToOrderRate() : 1.0);
        $shippingPriceIncludesTax = $taxConfig->shippingPriceIncludesTax($store);
        $itemPriceIncludesTax = $taxConfig->priceIncludesTax($store);
        foreach ($result['TaxLines'] as $line) {
            $itemId = $line['LineNo'];
            $chargeTax = $store->roundPrice(floatval($line['Tax']));
            switch ($itemId) {
                case 'SHIPPING':
                    // Store the tax amount
                    $invoice->setBaseShippingTaxAmount($chargeTax);
                    $invoice->setShippingTaxAmount($store->roundPrice($chargeTax * $exchangeRate));

                    // Update shipping totals
                    if ($shippingPriceIncludesTax) {
                        $invoice->setBaseShippingAmount($invoice->getBaseShippingInclTax() - $invoice->getBaseShippingTaxAmount());
                        $invoice->setShippingAmount($invoice->getShippingInclTax() - $invoice->getShippingTaxAmount());
                    } else {
                        $invoice->setBaseShippingInclTax($invoice->getBaseShippingAmount() + $invoice->getBaseShippingTaxAmount());
                        $invoice->setShippingInclTax($invoice->getShippingAmount() + $invoice->getShippingTaxAmount());
                    }

                    // Add shipping tax to total
                    $baseTotalTax += $invoice->getBaseShippingTaxAmount();
                    $totalTax += $invoice->getShippingTaxAmount();
                    break;
                default:
                    /** @var Mage_Sales_Model_Order_Invoice_Item $item */
                    $item = (isset($items[$itemId]) ? $items[$itemId] : false);
                    if (!$item) {
                        continue;
                    }

                    // Store the tax amount
                    $item->setBaseTaxAmount($chargeTax);
                    $item->setTaxAmount($store->roundPrice($chargeTax * $exchangeRate));

                    if ($itemPriceIncludesTax) {
                        $item->setBaseRowTotal($item->getBaseRowTotalInclTax() - $item->getBaseTaxAmount());
                        $item->setRowTotal($item->getRowTotalInclTax() - $item->getTaxAmount());
                    } else {
                        $item->setBaseRowTotalInclTax($item->getBaseRowTotal() + $item->getBaseTaxAmount());
                        $item->setRowTotalInclTax($item->getRowTotal() + $item->getTaxAmount());
                    }

                    // Add item tax to tax total
                    $baseTotalTax += $item->getBaseTaxAmount();
                    $totalTax += $item->getTaxAmount();
            }
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setBaseTaxAmount($baseTotalTax);
        $invoice->setHiddenTaxAmount(0.0);
        $invoice->setBaseHiddenTaxAmount(0.0);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax);

        if ($invoice->getCommitTaxDocuments()) {
            $invoice->setAvataxDocument($result['DocCode']);
        }
    }

    /**
     * @return Aoe_AvaTax_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('Aoe_AvaTax/Data');
    }
}
