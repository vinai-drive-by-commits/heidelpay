<?php

class Mage_Heidelpay_Model_System_Config_Source_Bookingmode
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'DB', 'label' => Mage::helper('heidelpay')->__('Direct Booking')),
            array('value' => 'PA', 'label' => Mage::helper('heidelpay')->__('Preauthorisation'))
        );
    }
}
