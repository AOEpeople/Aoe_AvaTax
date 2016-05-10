<?php

/**
 * Response
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log_Edit_Tab_Response extends Mage_Adminhtml_Block_Widget
{

    public function _toHtml() {
        $avaTaxLog = Mage::registry('current_avatax_log'); /* @var $log Aoe_AvaTax_Model_Log */
        $rawResponse = json_decode($avaTaxLog->getResultBody());
        $response = stripslashes( var_export($rawResponse, true));
        return '<pre>' . $response . '</pre>';
    }
}
