<?php
class Mage_Heidelpay_Model_Method_Hppp extends Mage_Heidelpay_Model_Method_Payment
{  
	/**
	* unique internal payment method identifier
	*    
	* @var string [a-z0-9_]   
	**/
	protected $_code = 'hppp';
	protected $_formBlockType = 'payment/form';
	protected $_infoBlockType = 'heidelpay/info_payment';

  public function getLightbox($descArea = false)
  {
    $info = '';
    return $info;
  }

  public function getHeidelpayIFrameUrl()
  {
    $this->actualPaymethod = 'PP';
    $src = $this->handleDebit();
    return $src;
  }
	
  public function getFormBlockType()
  {
  	$this->_formBlockType = 'heidelpay/form_prepaid';
    return $this->_formBlockType;
  }

  public function validate()
  {
    parent::validate();
    return $this;
  }
  
	public function getTitle()
	{
		return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
	public function getAdminTitle()
	{
	  return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
}

