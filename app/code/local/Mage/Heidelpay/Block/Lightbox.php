<?php

class Mage_Heidelpay_Block_Lightbox extends Mage_Core_Block_Template # Mage_Checkout_Block_Onepage_Success
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('heidelpay/lightbox.phtml');
    }

    protected function getHPUrl()
    {
        return $this->getHPIframeUrl();
    }
    /*
      protected function getIFrameUrl()
      {
        #$payment = $this->getOrder()->getPayment()->getMethodInstance();
        $payment = $this->getPayment();
        echo 'class: '.get_class($this);
        echo '<pre>'.print_r($payment, 1).'</pre>';
        exit();

        #return $payment->getIFrameUrl();
      }
     */
}
