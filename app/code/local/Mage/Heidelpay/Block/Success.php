<?php

class Mage_Heidelpay_Block_Success extends Mage_Checkout_Block_Onepage_Success
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('heidelpay/success.phtml');
    }

    protected function getPrePaidData()
    {
        return $this->getHPPPData();
    }
}
