<?php

class Aoe_AvaTax_Resource_Log_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'aoe_avatax_log_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'collection';

    /**
     * Configure Collection
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('Aoe_AvaTax/Log');
    }
}
