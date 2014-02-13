<?php

class Mage_Heidelpay_Model_System_Config_Source_Orderstatus extends Mage_Adminhtml_Model_System_Config_Source_Order_Status
{
    public function toOptionArray()
    {
        $this->_stateStatuses = null;
        return parent::toOptionArray();
    }
}
