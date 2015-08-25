<?php
/**
 * GetTaxResult.class.php
 */

/**
 * Result data returned from {@link TaxServiceSoap#getTax}.
 *
 * @see       GetTaxRequest
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Tax
 */
namespace AvaTax;

class GetTaxResult
{
    /** @var string */
    private $DocCode;
    /** @var string */
    private $DocId;
    /** @var string */
    private $AdjustmentDescription;
    /** @var string */
    private $DocDate;
    /** @var string */
    private $TaxDate;
    /** @var string */
    private $DocType;
    /** @var string */
    private $DocStatus;
    /** @var boolean */
    private $Reconciled;
    /** @var boolean */
    private $Locked;
    /** @var string */
    private $Timestamp;
    /** @var number */
    private $TotalAmount;
    /** @var number */
    private $TotalDiscount;
    /** @var number */
    private $TotalExemption;
    /** @var number */
    private $TotalTaxable;
    /** @var number */
    private $TotalTax;
    /** @var int */
    private $AdjustmentReason;
    /** @var int */
    private $Version;
    /** @var \stdClass */
    private $TaxLines;
    /** @var  \stdClass */
    private $TaxAddresses;

    //@author:swetal
    //Added new properties to upgrade to 5.3 interface
    /** @var number */
    private $TotalTaxCalculated;
    /** @var \stdClass */
    private $TaxSummary;


    /**
     * Gets the internal reference code used by the client application. This is used for operations such as Post and GetTaxHistory.
     * <p>
     * See {@link GetTaxRequest#getDocId} on <b>GetTaxRequest</b> for more information about this member.
     * </p>
     *
     * @return string
     */
    public function getDocId()
    {
        return $this->DocId;
    }

    /**
     * Gets the internal reference code used by the client application. This is used for operations such as Post and GetTaxHistory.
     * <p>
     * See {@link GetTaxRequest#getDocCode} on <b>GetTaxRequest</b> for more information about this member.
     * </p>
     *
     * @return string
     */
    public function getDocCode()
    {
        return $this->DocCode;
    }

    /**
     * AdjustmentDescription set while making AdjustTax call.
     *
     * @return string
     */
    public function getAdjustmentDescription()
    {
        return $this->AdjustmentDescription;
    }

    /**
     * AdjustmentReason set while making AdjustTax call. It is a high level classification of why an Original Document is being modified.
     *
     * @return int
     */
    public function getAdjustmentReason()
    {
        return $this->AdjustmentReason;
    }

    /**
     * Gets the date on the invoice, purchase order, etc.
     * <p>
     * See {@link GetTaxRequest#getDocDate} on <b>GetTaxRequest</b> for more information about this member.
     * </p>
     *
     * @return string
     */
    public function getDocDate()
    {
        return $this->DocDate;
    }

    /**
     * Tax Date is the date used to calculate tax on the Document.
     * <p>
     * See {@link GetTaxRequest#taxDate} on <b>GetTaxRequest</b> for more information about this member.
     * </p>
     *
     * @return string
     */
    public function getTaxDate()
    {
        return $this->TaxDate;
    }

    /**
     * Gets the Document Type.
     * <p>
     * See {@link GetTaxRequest#DocType} on <b>GetTaxRequest</b> for more information about this member.
     * </p>
     *
     * @see \AvaTax\DocumentType
     *
     * @return string
     */
    public function getDocType()
    {
        return $this->DocType;
    }

    /**
     * Gets the document's status after the tax calculation.
     *
     * @see \AvaTax\DocStatus
     *
     * @return string
     */
    public function getDocStatus()
    {
        return $this->DocStatus;
    }

    /**
     * True if the document has been reconciled;  Only committed documents can be reconciled.
     * <p>
     * For information on committing documents, see the <b>TaxSvc</b>'s
     * {@link TaxSvcSoap#commitTax} method. For information
     * on reconciling documents, see the {@link TaxSvcSoap#reconcileTaxHistory} method.
     * </p>
     *
     * @return boolean
     */
    public function getIsReconciled()
    {
        return $this->Reconciled;
    }

    /**
     * Flag indicating if a Document has been locked by Avalara's MRS process. Locked documents can not be modified and can not be cancelled because they have been reported on Tax Return.
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->Locked;
    }

    /**
     * Date of the last status change on the document (i.e. Save date, Post date, Commit date, Cancel date).
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->Timestamp;
    }

    /**
     * The sum of all line {@link Line#getAmount} values.
     *
     * @param decimal
     */
    public function getTotalAmount()
    {
        return $this->TotalAmount;
    }

    /**
     * Gets the sum of all <b>TaxLine</b> {@link TaxLine#getDiscount} amounts; Typically it
     * will equal the requested Discount, but, but it is possible that no lines were marked as discounted.
     *
     * @return number
     */
    public function getTotalDiscount()
    {
        return $this->TotalDiscount;
    }

    /**
     * Gets the sum of all <b>TaxLine</b> {@link TaxLine#getExemption} amounts.
     *
     * @return number
     *
     * @deprecated See {@link TaxDetail#getExemption}.
     */
    public function getTotalExemption()
    {
        return $this->TotalExemption;
    }

    /**
     * Gets the amount the tax is based on;  This is the total of all {@link Line} <b>Base</b> amounts;
     * Typically it will be equal to the document
     * {@link GetTaxResult#getTotalAmount} - {@link GetTaxRequest#getDiscount} - {@link #getTotalExemption}.
     *
     * @return number
     * @deprecated See {@link TaxDetail#getTaxable}.
     */
    public function getTotalTaxable()
    {
        return $this->TotalTaxable;
    }

    /**
     *  Gets the total tax for the document.
     *
     * @return number
     */
    public function getTotalTax()
    {
        return $this->TotalTax;
    }

    /**
     * HashCode to support Reconciliation.
     *
     * @return int
     */
    public function getHashCode()
    {
        return $this->HashCode;
    }

    /**
     * Current version of the document.
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->Version;
    }

    /**
     * Gets the Tax broken down by individual {@link TaxLine}.
     *
     * @return TaxLine[]
     */
    public function getTaxLines()
    {
        return Utils::EnsureIsArray($this->TaxLines->TaxLine);
    }

    /**
     * Gets the Tax broken down by individual {@link TaxLine}.
     *
     * @return \stdClass[]
     */
    public function getTaxAddresses()
    {
        return Utils::EnsureIsArray($this->TaxAddresses->TaxAddress);
    }

    /**
     * TotalTaxCalculated indicates the total tax calculated by AvaTax. This is usually the same as the TotalTax, except when a tax override amount is specified.
     * This is for informational purposes.The TotalTax will still be used for reporting
     *
     * @return number
     */
    public function getTotalTaxCalculated()
    {
        return $this->TotalTaxCalculated;
    }

    /**
     * TaxSummary is now returned when GetTaxRequest.DetailLevel == DetailLevel.Line in addition to DetailLevel.Summary.
     * It is not returned for DetailLevel.Document or DetailLevel.TaxDetail.
     *
     * @return TaxDetail[]
     */
    public function getTaxSummary()
    {
        return Utils::EnsureIsArray($this->TaxSummary->TaxDetail);
    }

    /**
     * @param $lineNo
     *
     * @return TaxLine
     */
    public function getTaxLine($lineNo)
    {
        if ($this->getTaxLines() != null) {
            foreach ($this->getTaxLines() as $taxLine) {
                if ($lineNo == $taxLine->getNo()) {
                    return $taxLine;
                }
            }
        }
    }



    /////////////////////////////////////////////PHP bug requires this copy from BaseResult ///////////
    /**
     * @var string
     */
    private $TransactionId;
    /**
     * @var string must be one of the values defined in {@link SeverityLevel}.
     */
    private $ResultCode = 'Success';
    /**
     * @var array of Message.
     */
    private $Messages = array();

    /**
     * Accessor
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->TransactionId;
    }

    /**
     * Accessor
     *
     * @see \AvaTax\SeverityLevel
     *
     * @return string
     */
    public function getResultCode()
    {
        return $this->ResultCode;
    }

    /**
     * Accessor
     *
     * @return \AvaTax\Message[]
     */
    public function getMessages()
    {
        return Utils::EnsureIsArray($this->Messages->Message);
    }
}
