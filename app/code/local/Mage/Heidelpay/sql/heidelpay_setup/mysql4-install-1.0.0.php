<?php
/* @var $installer Heidelpay_Model_Entity_Setup */
/*
$installer = $this;
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('heidelpay')};
    CREATE TABLE {$this->getTable('heidelpay')} (
      `userId` BIGINT NOT NULL ,
      `heidelpay_ccard_unique_id` VARCHAR( 32 ) NOT NULL ,
      `heidelpay_ccard_payment_type` VARCHAR( 32 ) NOT NULL ,
      `heidelpay_ccard` VARCHAR( 20 ) NOT NULL ,
      `heidelpay_ccard_valid_until` VARCHAR( 10 ) NOT NULL ,
      `heidelpay_ccard_brand` VARCHAR( 20 ) NOT NULL ,
      `heidelpay_ccard_holder` VARCHAR( 255 ) NOT NULL ,
      `heidelpay_dcard_unique_id` VARCHAR( 32 ) NOT NULL ,
      `heidelpay_dcard_payment_type` VARCHAR( 32 ) NOT NULL ,
      `heidelpay_dcard` VARCHAR( 20 ) NOT NULL ,
      `heidelpay_dcard_valid_until` VARCHAR( 10 ) NOT NULL ,
      `heidelpay_dcard_brand` VARCHAR( 20 ) NOT NULL ,
      `heidelpay_dcard_holder` VARCHAR( 255 ) NOT NULL,
      PRIMARY KEY (`userId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->installEntities();
 */

// load id for customer entity
$read = Mage::getSingleton('core/resource')->getConnection('core_read');
$eid = $read->fetchRow("select entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code = 'customer'");
$customer_type_id = $eid['entity_type_id'];

$eid = $read->fetchRow("select entity_type_id from {$this->getTable('eav_entity_type')} where entity_type_code = 'order'");
$order_type_id = $eid['entity_type_id'];

// Customer Attribures
$newAttributes = array(
    // Credit Card Data
    'heidelpay_ccard_unique_id' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard Unique ID',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_ccard_payment_type' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard Payment Type',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_ccard' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_ccard_valid_until' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard valid until',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_ccard_brand' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard Brand',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_ccard_holder' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard Holder',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    // Credit Card X Data
    'heidelpay_xcard_unique_id' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X Unique ID',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_xcard_payment_type' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X Payment Type',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_xcard' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_xcard_valid_until' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X valid until',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_xcard_brand' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X Brand',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_xcard_holder' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Creditcard X Holder',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    // Debit Card Data
    'heidelpay_dcard_unique_id' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard Unique ID',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_dcard_payment_type' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard Payment Type',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_dcard' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_dcard_valid_until' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard valid until',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_dcard_brand' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard Brand',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_dcard_holder' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay DebitCard Holder',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    // Bank Data
    'heidelpay_last_blz' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Bankcode',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_last_kto' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Accuntnumber',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
    'heidelpay_last_holder' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Account Holder',
        'global' => 1,
        'required' => 0,
        'default' => '',
        'position' => '0'
    ),
);
// Order Attribures
$newOrderAttributes = array(
    'last_trans_id' => array(
        'type' => 'varchar',
        'input' => 'label',
        'label' => 'Heidelpay Unique ID',
        'global' => 1,
        'required' => 0,
        'sort_order' => 300,
        'visible' => 1,
    ),
);


$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
// Customer changes
foreach ($newAttributes AS $k => $v) {
    $setup->addAttribute($customer_type_id, $k, $v);
}
// Order changes
foreach ($newOrderAttributes AS $k => $v) {
    $setup->addAttribute($order_type_id, $k, $v);
}
$installer->endSetup();
// EOF
