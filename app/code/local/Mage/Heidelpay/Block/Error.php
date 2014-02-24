<?php

class Mage_Heidelpay_Block_Error extends Mage_Core_Block_Template # Mage_Checkout_Block_Onepage_Success
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('heidelpay/error.phtml');
    }

}
