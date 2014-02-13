<?php

class Mage_Heidelpay_Block_Info_Payment extends Mage_Heidelpay_Block_Info
{
	/**
	 * Init default template for block
	 */
	protected function _construct()
	{
    parent::_construct();

    $order = $this->getOrder();
    #echo '<pre>'.print_r($order, 1).'</pre>';
    if ($order instanceof Mage_Sales_Model_Order) {
    	if ($order->getId()) {
        
    	}
    }
    $this->setTemplate('heidelpay/info/payment.phtml');
	}

}
