<?php
/**
 * BaseResult.class.php
 */

/**
 * The base class for result objects that return a ResultCode and Messages collection -- There is no reason for clients to create these.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */

namespace AvaTax;

abstract class BaseResult
{
    /**
     * @var string
     */
    protected $TransactionId;

    /**
     * @var string must be one of the values defined in {@link SeverityLevel}.
     */
    protected $ResultCode;

    /**
     * @var \stdClass
     */
    protected $Messages;

    /**
     * A unique Transaction ID identifying a specific request/response set.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->TransactionId;
    }

    /**
     * Indicates whether operation was successfully completed or not.
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
     * @return \AvaTax\Message[]
     */
    public function getMessages()
    {
        return (isset($this->Messages->Message) ? Utils::EnsureIsArray($this->Messages->Message) : []);
    }

}
