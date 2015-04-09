<?php

class Aoe_AvaTax_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('Aoe_AvaTax/Log', 'id');
    }
}
