<?php

class Mage_Heidelpay_Block_Secure extends Mage_Core_Block_Template # Mage_Checkout_Block_Onepage_Success
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('heidelpay/secure.phtml');
    }

    protected function get3DFrame()
    {
        return $this->getHP3DIframe();
    }

}
