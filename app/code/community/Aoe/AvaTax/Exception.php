<?php

class Aoe_AvaTax_Exception extends Mage_Core_Exception
{
    protected $messages = array();

    public function __construct($resultCode, array $messages = array(), $code = 0)
    {
        parent::__construct('AvaTax failure response: ' . $resultCode, $code);
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }
}
