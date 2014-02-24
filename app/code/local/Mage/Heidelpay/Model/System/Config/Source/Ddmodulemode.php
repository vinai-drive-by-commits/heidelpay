<?php

class Mage_Heidelpay_Model_System_Config_Source_Ddmodulemode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'DIRECT', 'label' => Mage::helper('heidelpay')->__('Direct Data Input')),
        );
    }
}

?>
