<?php

/**
 * Log edit form tab
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     * @access protected
     * @return Aoe_AvaTax_Block_Adminhtml_Log_Edit_Tab_Form
     * @author Manish Jain
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('avatax_log_');
        $form->setFieldNameSuffix('avatax_log');
        $this->setForm($form);
        $fieldset = $form->addFieldset('avatax_log_form', array('legend' => Mage::helper('Aoe_AvaTax')->__('General')));

        /* @var $model Aoe_AvaTax_Model_Log */
        $model = $this->getAvaTaxLog();


        $fieldset->addField('id', 'note', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('ID'),
            'name' => 'id',
            'text' => $model->getId(),
        ));

        $fieldset->addField('result_code', 'note', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('Status'),
            'name' => 'result_code',
            'text' => $model->getResultCode(),
        ));

        $fieldset->addField('failure_message', 'note', array(
            'label' => Mage::helper('Aoe_AvaTax')->__('Message'),
            'name' => 'failure_message',
            'text' => $model->getFailureMessage() ? $model->getFailureMessage() : 'NA',
        ));

        $fieldset->addField('store_id', 'note', array(
            'label'     => Mage::helper('Aoe_AvaTax')->__('Store'),
            'name' => 'store_id',
            'text' => $this->getAvaTaxLogStoreName()
        ));

        $fieldset->addField('created_at', 'note', array(
            'label'     => Mage::helper('Aoe_AvaTax')->__('Created At'),
            'name' => 'created_at',
            'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_LONG),
            'text' => Mage::app()->getLocale()->date($model->getCreatedAt())
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Retrieve avatax log model object
     *
     * @return Aoe_AvaTax_Model_Log
     */
    public function getAvaTaxLog()
    {
        return Mage::registry('current_avatax_log');
    }

    public function getAvaTaxLogStoreName()
    {
        if ($this->getAvaTaxLog()) {
            $storeId = $this->getAvaTaxLog()->getStoreId();
            if (is_null($storeId)) {
                $deleted = Mage::helper(',')->__(' [deleted]');
                return $this->getAvaTaxLog()->getStoreId() . $deleted;
            }
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode('<br/>', $name);
        }
        return null;
    }
}
