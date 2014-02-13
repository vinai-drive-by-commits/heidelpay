<?php
class Mage_Heidelpay_Block_Form_Prepaid extends Mage_Heidelpay_Block_Form
{
	protected function _construct()
	{
    parent::_construct();
    $this->setTemplate('heidelpay/form/prepaid.phtml');
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
