<?php

class Mage_Heidelpay_Model_System_Config_Source_Transactionmode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'LIVE', 'label'=>Mage::helper('heidelpay')->__('LIVE')),
            array('value'=>'INTEGRATOR_TEST', 'label'=>Mage::helper('heidelpay')->__('INTEGRATOR_TEST')),
            array('value'=>'CONNECTOR_TEST', 'label'=>Mage::helper('heidelpay')->__('CONNECTOR_TEST')),
        );
    }
}
