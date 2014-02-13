<?php
class Mage_Heidelpay_Model_Entity_Setup extends Mage_Customer_Model_Entity_Setup
{
    public function getDefaultEntities()
    {
      return array();

      return array(
        
            'customer' => array(
                #'entity_model'          => 'customer/customer',
                #'table'                 => 'customer/entity',
                #'increment_model'       => 'eav/entity_increment_numeric',
                #'increment_per_store'   => false,
                #'additional_attribute_table' => 'customer/eav_attribute',
                #'entity_attribute_collection' => 'customer/attribute_collection',
                'attributes' => array(
                    'heidelpay_ccard_unique_id' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard Unique ID',
                        'required'      => FALSE,
                        'sort_order'    => 300,
                      ),
                    'heidelpay_ccard_payment_type' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard Payment Type',
                        'required'      => FALSE,
                        'sort_order'    => 310,
                      ),
                    'heidelpay_ccard' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard',
                        'required'      => FALSE,
                        'sort_order'    => 320,
                      ),
                    'heidelpay_ccard_valid_until' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard valid until',
                        'required'      => FALSE,
                        'sort_order'    => 330,
                      ),
                    'heidelpay_ccard_brand' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard Brand',
                        'required'      => FALSE,
                        'sort_order'    => 340,
                      ),
                    'heidelpay_ccard_holder' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard Holder',
                        'required'      => FALSE,
                        'sort_order'    => 350,
                      ),

                    'heidelpay_xcard_unique_id' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X Unique ID',
                        'required'      => FALSE,
                        'sort_order'    => 300,
                      ),
                    'heidelpay_xcard_payment_type' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X Payment Type',
                        'required'      => FALSE,
                        'sort_order'    => 310,
                      ),
                    'heidelpay_xcard' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X',
                        'required'      => FALSE,
                        'sort_order'    => 320,
                      ),
                    'heidelpay_xcard_valid_until' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X valid until',
                        'required'      => FALSE,
                        'sort_order'    => 330,
                      ),
                    'heidelpay_xcard_brand' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X Brand',
                        'required'      => FALSE,
                        'sort_order'    => 340,
                      ),
                    'heidelpay_xcard_holder' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Creditcard X Holder',
                        'required'      => FALSE,
                        'sort_order'    => 350,
                      ),

                    'heidelpay_dcard_unique_id' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard Unique ID',
                        'required'      => FALSE,
                        'sort_order'    => 400,
                      ),
                    'heidelpay_dcard_payment_type' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard Payment Type',
                        'required'      => FALSE,
                        'sort_order'    => 410,
                      ),
                    'heidelpay_dcard' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard',
                        'required'      => FALSE,
                        'sort_order'    => 420,
                      ),
                    'heidelpay_dcard_valid_until' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard valid until',
                        'required'      => FALSE,
                        'sort_order'    => 430,
                      ),
                    'heidelpay_dcard_brand' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard Brand',
                        'required'      => FALSE,
                        'sort_order'    => 440,
                      ),
                    'heidelpay_dcard_holder' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay DebitCard Holder',
                        'required'      => FALSE,
                        'sort_order'    => 450,
                      ),
                    'heidelpay_last_blz' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Bankcode',
                        'required'      => FALSE,
                        'sort_order'    => 460,
                      ),
                    'heidelpay_last_kto' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Accuntnumber',
                        'required'      => FALSE,
                        'sort_order'    => 470,
                      ),
                    'heidelpay_last_holder' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Account Holder',
                        'required'      => FALSE,
                        'sort_order'    => 480,
                      ),
                    ),
                  ),
         
            'order' => array(
                'entity_model'          => 'sales/order',
                'table'                 => 'sales/order',
                'increment_model'       => 'eav/entity_increment_numeric',
                'increment_per_store'   => TRUE,
                'attributes' => array(
                    'last_trans_id' => array(
                        'type'          => 'varchar',
                        'label'         => 'Heidelpay Unique ID',
                        'required'      => FALSE,
                        'sort_order'    => 300,
                        'visible'       => TRUE,
                        ),
                    ),
                ),
            );
    }
}
