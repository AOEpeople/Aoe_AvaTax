<?php

class Aoe_AvaTax_Model_Sales_Creditmemo_Total_Tax extends Mage_Sales_Model_Order_Creditmemo_Total_Tax
{
    /**
     * Collects the total tax for the credit memo
     *
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     *
     * @return $this
     *
     * @throws Aoe_AvaTax_Exception
     */
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();
        if (!$this->getHelper()->isActive($store)) {
            return parent::collect($creditmemo);
        }

        $creditmemo->setTaxAmount(0.0);
        $creditmemo->setBaseTaxAmount(0.0);
        $creditmemo->setHiddenTaxAmount(0.0);
        $creditmemo->setBaseHiddenTaxAmount(0.0);

        $items = $creditmemo->getAllItems();

        // Get taxes via API call
        $api = $this->getHelper()->getApi($store);
        $result = $api->callGetTaxForCreditmemo($creditmemo);

        if ($result['ResultCode'] !== 'Success') {
            throw new Aoe_AvaTax_Exception($result['ResultCode'], $result['Messages']);
        }

        $totalTax = 0;
        $baseTotalTax = 0;

        /** @var Mage_Tax_Model_Config $taxConfig */
        $taxConfig = Mage::getSingleton('tax/config');
        $hasDisplayCurrency = ($creditmemo->getBaseCurrencyCode() !== $creditmemo->getOrderCurrencyCode());
        $exchangeRate = ($hasDisplayCurrency ? $creditmemo->getBaseToOrderRate() : 1.0);
        $shippingPriceIncludesTax = $taxConfig->shippingPriceIncludesTax($store);
        $itemPriceIncludesTax = $taxConfig->priceIncludesTax($store);
        foreach ($result['TaxLines'] as $line) {
            $itemId = $line['LineNo'];
            $chargeTax = $store->roundPrice(floatval($line['Tax']));
            switch ($itemId) {
                case 'SHIPPING':
                    // Store the tax amount
                    $creditmemo->setBaseShippingTaxAmount(-$chargeTax);
                    $creditmemo->setShippingTaxAmount($store->roundPrice(-$chargeTax * $exchangeRate));

                    // Update shipping totals
                    if ($shippingPriceIncludesTax) {
                        $creditmemo->setBaseShippingAmount($creditmemo->getBaseShippingInclTax() - $creditmemo->getBaseShippingTaxAmount());
                        $creditmemo->setShippingAmount($creditmemo->getShippingInclTax() - $creditmemo->getShippingTaxAmount());
                    } else {
                        $creditmemo->setBaseShippingInclTax($creditmemo->getBaseShippingAmount() + $creditmemo->getBaseShippingTaxAmount());
                        $creditmemo->setShippingInclTax($creditmemo->getShippingAmount() + $creditmemo->getShippingTaxAmount());
                    }

                    // Add shipping tax to total
                    $baseTotalTax += $creditmemo->getBaseShippingTaxAmount();
                    $totalTax += $creditmemo->getShippingTaxAmount();
                    break;
                default:

                    /** @var Mage_Sales_Model_Order_Creditmemo_Item $item */
                    $item = (isset($items[$itemId]) ? $items[$itemId] : false);
                    if (!$item) {
                        continue;
                    }

                    // Store the tax amount
                    $item->setBaseTaxAmount(-$chargeTax);
                    $item->setTaxAmount($store->roundPrice(-$chargeTax * $exchangeRate));

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

        $creditmemo->setTaxAmount($totalTax);
        $creditmemo->setBaseTaxAmount($baseTotalTax);
        $creditmemo->setHiddenTaxAmount(0.0);
        $creditmemo->setBaseHiddenTaxAmount(0.0);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax);

        if ($creditmemo->getCommitTaxDocuments()) {
            $creditmemo->setAvataxDocument($result['DocCode']);
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
