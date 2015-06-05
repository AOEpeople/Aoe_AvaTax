<?php

/**
 * @author Lee Saferite <lee.saferite@aoe.com>
 */
class Aoe_AvaTax_Helper_Soap extends Aoe_AvaTax_Helper_Data
{
    /**
     * @param \AvaTax\GetTaxRequest $soapRequest
     *
     * @return array
     */
    public function normalizeGetTaxRequest(AvaTax\GetTaxRequest $soapRequest)
    {
        $isDevMode = Mage::getIsDeveloperMode();
        if ($isDevMode) {
            Mage::setIsDeveloperMode(false);
        }

        $soapRequest->prepare();

        $request = array(
            'CompanyCode'              => $soapRequest->getCompanyCode(),
            'DocType'                  => $soapRequest->getDocType(),
            'DocCode'                  => $soapRequest->getDocCode(),
            'Commit'                   => $soapRequest->getCommit(),
            'DetailLevel'              => $soapRequest->getDetailLevel(),
            'DocDate'                  => $soapRequest->getDocDate(),
            'CustomerCode'             => $soapRequest->getCustomerCode(),
            'CurrencyCode'             => $soapRequest->getCurrencyCode(),
            'Discount'                 => $soapRequest->getDiscount(),
            'BusinessIdentificationNo' => $soapRequest->getBusinessIdentificationNo(),
            'Addresses'                => array(),
            'Lines'                    => array(),
        );

        foreach ($soapRequest->getAddresses() as $soapAddress) {
            /** @var AvaTax\Address $soapAddress */
            $request['Addresses'][] = array(
                'AddressCode' => $soapAddress->getAddressCode(),
                'Line1'       => $soapAddress->getLine1(),
                'Line2'       => $soapAddress->getLine2(),
                'Line3'       => $soapAddress->getLine3(),
                'City'        => $soapAddress->getCity(),
                'Region'      => $soapAddress->getRegion(),
                'Country'     => $soapAddress->getCountry(),
                'PostalCode'  => $soapAddress->getPostalCode(),
            );
        }

        foreach ($soapRequest->getLines() as $soapLine) {
            /** @var AvaTax\Line $soapLine */
            $request['Lines'][] = array(
                "LineNo"          => $soapLine->getNo(),
                "ItemCode"        => $soapLine->getItemCode(),
                "Qty"             => $soapLine->getQty(),
                "Amount"          => $soapLine->getAmount(),
                "OriginCode"      => $soapLine->getOriginAddress()->getAddressCode(),
                "DestinationCode" => $soapLine->getDestinationAddress()->getAddressCode(),
                "Description"     => $soapLine->getDescription(),
                "TaxCode"         => $soapLine->getTaxCode(),
                "Discounted"      => $soapLine->getDiscounted(),
                "TaxIncluded"     => $soapLine->getTaxIncluded(),
                "Ref1"            => $soapLine->getRef1(),
                "Ref2"            => $soapLine->getRef2(),
            );
        }

        if ($isDevMode) {
            Mage::setIsDeveloperMode(true);
        }

        return $this->recursiveKeySort($request);
    }

    /**
     * @param \AvaTax\GetTaxResult $soapResult
     *
     * @return array
     */
    public function normalizeGetTaxResult(AvaTax\GetTaxResult $soapResult)
    {
        $isDevMode = Mage::getIsDeveloperMode();
        if ($isDevMode) {
            Mage::setIsDeveloperMode(false);
        }

        $result = array(
            'Version'               => $soapResult->getVersion(),
            'Timestamp'             => $soapResult->getTimestamp(),
            'TransactionId'         => $soapResult->getTransactionId(),
            'ResultCode'            => $soapResult->getResultCode(),
            'DocId'                 => $soapResult->getDocId(),
            'DocCode'               => $soapResult->getDocCode(),
            'DocDate'               => $soapResult->getDocDate(),
            'TaxDate'               => $soapResult->getTaxDate(),
            'DocType'               => $soapResult->getDocType(),
            'DocStatus'             => $soapResult->getDocStatus(),
            'AdjustmentReason'      => $soapResult->getAdjustmentReason(),
            'AdjustmentDescription' => $soapResult->getAdjustmentDescription(),
            'TaxLines'              => array(),
            'TaxSummary'            => array(),
            'TaxAddresses'          => array(),
            'Messages'              => array(),
        );

        foreach ($soapResult->getTaxLines() as $taxLine) {
            /** @var AvaTax\TaxLine $taxLine */
            $line = array(
                'LineNo'           => $taxLine->getNo(),
                'TaxCode'          => $taxLine->getTaxCode(),
                'Taxability'       => $taxLine->getTaxability(),
                'BoundaryLevel'    => $taxLine->getBoundaryLevel(),
                'Exemption'        => $taxLine->getExemption(),
                'Discount'         => $taxLine->getDiscount(),
                'Taxable'          => $taxLine->getTaxable(),
                'Rate'             => $taxLine->getRate(),
                'Tax'              => $taxLine->getTax(),
                'ExemptCertId'     => $taxLine->getExemptCertId(),
                'TaxDetails'       => array(),
                'TaxCalculated'    => $taxLine->getTaxCalculated(),
                'ReportingDate'    => $taxLine->getReportingDate(),
                'AccountingMethod' => $taxLine->getAccountingMethod(),
                'TaxIncluded'      => $taxLine->getTaxIncluded(),
                //'TaxDate'          => $taxLine->getTaxDate(),
            );

            foreach ($taxLine->getTaxDetails() as $taxLineDetail) {
                /** @var AvaTax\TaxDetail $taxLineDetail */
                $line['TaxDetails'][] = array(
                    'JurisType'        => $taxLineDetail->getJurisType(),
                    'JurisCode'        => $taxLineDetail->getJurisCode(),
                    'TaxType'          => $taxLineDetail->getTaxType(),
                    'Base'             => $taxLineDetail->getBase(),
                    'Taxable'          => $taxLineDetail->getTaxable(),
                    'Rate'             => $taxLineDetail->getRate(),
                    'Tax'              => $taxLineDetail->getTax(),
                    'NonTaxable'       => $taxLineDetail->getNonTaxable(),
                    'Exemption'        => $taxLineDetail->getExemption(),
                    'JurisName'        => $taxLineDetail->getJurisName(),
                    'TaxName'          => $taxLineDetail->getTaxName(),
                    'TaxAuthorityType' => $taxLineDetail->getTaxAuthorityType(),
                    'Country'          => $taxLineDetail->getCountry(),
                    'Region'           => $taxLineDetail->getRegion(),
                    'TaxCalculated'    => $taxLineDetail->getTaxCalculated(),
                    'TaxGroup'         => $taxLineDetail->getTaxGroup(),
                    'StateAssignedNo'  => $taxLineDetail->getStateAssignedNo(),
                    //'RateType' => $taxLineDetail->getRateType(),
                );
            }

            $result['TaxLines'][] = $line;
        }

        foreach ($soapResult->getTaxSummary() as $taxSummary) {
            // TODO
        }

        foreach ($soapResult->getTaxAddresses() as $taxAddress) {
            $result['TaxAddresses'][] = array(
                'Address'            => $taxAddress->Address,
                'AddressCode'        => $taxAddress->AddressCode,
                'BoundaryLevel'      => $taxAddress->BoundaryLevel,
                'City'               => $taxAddress->City,
                'Country'            => $taxAddress->Country,
                'PostalCode'         => $taxAddress->PostalCode,
                'Region'             => $taxAddress->Region,
                'TaxRegionId'        => $taxAddress->TaxRegionId,
                'JurisCode'          => $taxAddress->JurisCode,
                'Latitude'           => $taxAddress->Latitude,
                'Longitude'          => $taxAddress->Longitude,
                'ValidateStatus'     => $taxAddress->ValidateStatus,
                'GeocodeType'        => $taxAddress->GeocodeType,
                'DistanceToBoundary' => $taxAddress->DistanceToBoundary,
            );
        }

        foreach ($soapResult->getMessages() as $message) {
            /** @var AvaTax\Message $message */
            $result['Messages'][] = array(
                'Summary'  => $message->getSummary(),
                'Details'  => $message->getDetails(),
                'HelpLink' => $message->getHelpLink(),
                'RefersTo' => $message->getRefersTo(),
                'Severity' => $message->getSeverity(),
                'Source'   => $message->getSource(),
                'Name'     => $message->getName()
            );
        }

        if ($isDevMode) {
            Mage::setIsDeveloperMode(true);
        }

        return $this->recursiveKeySort($result);
    }

    /**
     * @param \AvaTax\CancelTaxRequest $soapRequest
     *
     * @return array
     */
    public function normalizeCancelTaxRequest(AvaTax\CancelTaxRequest $soapRequest)
    {
        $isDevMode = Mage::getIsDeveloperMode();
        if ($isDevMode) {
            Mage::setIsDeveloperMode(false);
        }

        $request = array(
            'CompanyCode' => $soapRequest->getCompanyCode(),
            'DocId'       => $soapRequest->getDocId(),
            'DocCode'     => $soapRequest->getDocCode(),
            'DocType'     => $soapRequest->getDocType(),
            'CancelCode'  => $soapRequest->getCancelCode(),
        );

        if ($isDevMode) {
            Mage::setIsDeveloperMode(true);
        }

        return $this->recursiveKeySort($request);
    }

    /**
     * @param \AvaTax\CancelTaxResult $soapResult
     *
     * @return array
     */
    public function normalizeCancelTaxResult(AvaTax\CancelTaxResult $soapResult)
    {
        $isDevMode = Mage::getIsDeveloperMode();
        if ($isDevMode) {
            Mage::setIsDeveloperMode(false);
        }

        $result = array(
            'TransactionId' => $soapResult->getTransactionId(),
            'DocId'         => $soapResult->getDocId(),
            'ResultCode'    => $soapResult->getResultCode(),
            'Messages'      => array(),
        );

        foreach ($soapResult->getMessages() as $message) {
            /** @var AvaTax\Message $message */
            $result['Messages'][] = array(
                'Summary'  => $message->getSummary(),
                'Details'  => $message->getDetails(),
                'HelpLink' => $message->getHelpLink(),
                'RefersTo' => $message->getRefersTo(),
                'Severity' => $message->getSeverity(),
                'Source'   => $message->getSource(),
                'Name'     => $message->getName()
            );
        }

        if ($isDevMode) {
            Mage::setIsDeveloperMode(true);
        }

        return $this->recursiveKeySort($result);
    }
}
