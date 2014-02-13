<?php
class Mage_Heidelpay_Model_System_Config_Source_Sepamode
{
    public function toOptionArray(){
        return array(
            array('value'=>'classic', 'label'=>Mage::helper('heidelpay')->__('Account & Bank no.')),
            array('value'=>'iban', 'label'=>Mage::helper('heidelpay')->__('IBAN & BIC')),
            array('value'=>'both', 'label'=>Mage::helper('heidelpay')->__('both with selector')),
        );
    }
}

?>