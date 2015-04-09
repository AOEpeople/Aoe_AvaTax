<?php
/** @var Mage_Core_Model_Resource_Setup $this */
$this->startSetup();

// TODO: Remove this
$this->getConnection()->dropTable($this->getTable('Aoe_AvaTax/Log'));

$table = $this->getConnection()->newTable($this->getTable('Aoe_AvaTax/Log'));

$table->addColumn(
    'id',
    'integer',
    null,
    array(
        'primary'  => true,
        'identity' => true,
        'unsigned' => true,
    )
);

$table->addColumn(
    'created_at',
    Varien_Db_Ddl_Table::TYPE_DATETIME,
    null,
    array(
        'nullable' => false
    )
);

$table->addColumn(
    'store_id',
    Varien_Db_Ddl_Table::TYPE_SMALLINT,
    null,
    array(
        'unsigned' => true,
        'nullable' => false,
    )
);
$table->addForeignKey(
    $this->getFkName('Aoe_AvaTax/Log', 'store_id', 'core/store', 'store_id'),
    'store_id',
    $this->getTable('core/store'),
    'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE,
    Varien_Db_Ddl_Table::ACTION_CASCADE
);

$table->addColumn(
    'url',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    array(
        'nullable' => false,
    )
);

$table->addColumn(
    'request_body',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    65536,
    array(
        'nullable' => true,
    )
);

$table->addColumn(
    'result_body',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    65536,
    array(
        'nullable' => true,
    )
);

$table->addColumn(
    'result_code',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    20,
    array(
        'nullable' => true,
    )
);

$table->addColumn(
    'failure_message',
    Varien_Db_Ddl_Table::TYPE_TEXT,
    255,
    array(
        'nullable' => true,
    )
);

$this->getConnection()->createTable($table);

$this->getConnection()->addColumn(
    $this->getTable('tax/tax_class'),
    'avatax_code',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 25,
        'nullable' => true,
        'comment' => 'AvaTax Tax Class Code'
    )
);

$this->getConnection()->addColumn(
    $this->getTable('sales/invoice'),
    'avatax_document',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 50,
        'nullable' => true,
        'comment' => 'AvaTax Document'
    )
);

$this->getConnection()->addColumn(
    $this->getTable('sales/creditmemo'),
    'avatax_document',
    array(
        'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'   => 50,
        'nullable' => true,
        'comment' => 'AvaTax Document'
    )
);

$this->endSetup();
