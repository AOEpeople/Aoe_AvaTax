<?php
/**
 * GetTaxHistoryResult.class.php
 */

/**
 * Result data returned from {@link TaxServiceSoap#getTaxHistory} for a previously calculated tax document.
 *
 * @see GetTaxHistoryRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
namespace AvaTax;
class GetTaxHistoryResult extends BaseResult
{
    private $GetTaxRequest;
    private $GetTaxResult;

   /**
     * Gets the original {@link GetTaxRequest} for the document.
     *
     * @return GetTaxRequest
     */

	public function getGetTaxRequest() { return $this->GetTaxRequest; }

   /**
     * Gets the original {@link GetTaxResult} for the document.
     *
     * @return GetTaxResult
     */

    public function getGetTaxResult() { return $this->GetTaxResult; }
}
