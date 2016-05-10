<?php

/**
 * Log admin grid block
 *
 * @category    Aoe
 * @package     Aoe_AvaTax
 * @author      Manish Jain
 */
class Aoe_AvaTax_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     * @access public
     * @author Manish
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('avaTaxGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     * @access protected
     * @return Aoe_AvaTax_Block_Adminhtml_Log_Grid
     * @author Manish Jain
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('Aoe_AvaTax/log')->getCollection(); /* @var $collection Aoe_AvaTax_Resource_Log_Collection */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     * @access protected
     * @return Aoe_AvaTax_Block_Adminhtml_Log_Grid
     * @author Manish Jain
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => Mage::helper('Aoe_AvaTax')->__('ID'),
            'align' => 'left',
            'index' => 'id',
            'type' => 'number'
        ));
        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'                => Mage::helper('Aoe_AvaTax')->__('Store'),
                'type'                  => 'store',
                'index'                 => 'store_id',
                'sortable'              => false,
                'store_view'            => true,
                'width'                 => 200
            ));
        }
        $this->addColumn('result_code', array(
            'header' => Mage::helper('Aoe_AvaTax')->__('Status'),
            'align' => 'left',
            'index' => 'result_code',
            'type' => 'options',
            'width' => 50,
            'options' => array(
                'Success' => Mage::helper('Aoe_AvaTax')->__('Success'),
                'Error' => Mage::helper('Aoe_AvaTax')->__('Error'),
            )
        ));
        $this->addColumn('failure_message', array(
            'header' => Mage::helper('Aoe_AvaTax')->__('Message'),
            'align' => 'left',
            'index' => 'failure_message',
            'type' => 'text'
        ));
        $this->addColumn('created_at', array(
            'header' => Mage::helper('Aoe_AvaTax')->__('Created at'),
            'index' => 'created_at',
            'width' => '150px',
            'type' => 'datetime',
            'filter_index' => 'main_table.created_at'
        ));
        $this->addColumn('action',
            array(
                'header' => Mage::helper('Aoe_AvaTax')->__('Action'),
                'width' => 100,
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('Aoe_AvaTax')->__('Details'),
                        'url' => array('base' => '*/*/edit'),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'is_system' => true,
                'sortable' => false,
            ));
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');
        $this->addExportType('*/*/exportCsv', Mage::helper('Aoe_AvaTax')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('Aoe_AvaTax')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('Aoe_AvaTax')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * get the row url
     * @access public
     * @param Aoe_AvaTax_Model_Log
     * @return string
     * @author Manish Jain
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     * @access public
     * @return string
     * @author Manish Jain
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
