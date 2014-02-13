<?php

class Mage_Heidelpay_Model_System_Config_Source_Modulemode
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'DIRECT', 'label'=>Mage::helper('heidelpay')->__('Direct with registration')),
            array('value'=>'AFTER', 'label'=>Mage::helper('heidelpay')->__('After without registration'))
        );
    }
}
