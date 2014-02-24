<?php

class Mage_Heidelpay_Block_Form_Xcard extends Mage_Heidelpay_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('heidelpay/form/xcard.phtml');
    }

    /**
     * Retrieve payment configuration object
     *
     * @return Mage_Payment_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('payment/config');
    }

}
