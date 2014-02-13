<?php
class Mage_Heidelpay_Model_Method_Hpcc extends Mage_Heidelpay_Model_Method_Payment
{  
	/**
	* unique internal payment method identifier
	*    
	* @var string [a-z0-9_]   
	**/
	protected $_code = 'hpcc';
	protected $_formBlockType = 'payment/form';
	protected $_infoBlockType = 'heidelpay/info_payment';

  public function getLightbox($descArea = false)
  {
    $this->getSession()->setHpUniqueId('');
    if ($this->getConfigData('modulemode') == 'AFTER') return '';
    if ($descArea){ 
      $info = '';
      $customer = $this->getQuote()->getCustomer();
      $brand = $customer->getHeidelpay_ccard_brand();
      $ccard = $customer->getHeidelpay_ccard();
      $valid = $customer->getHeidelpay_ccard_valid_until();
      if (empty($brand) || empty($ccard) || empty($valid)){
        $info.= '';
        $this->actualPaymethod = 'CC';
        $src = $this->handleRegister();
        $info.= '<iframe src="'.$src.'" allowtransparency="true" frameborder="0" width="500" height="210"></iframe>';
      } else {
        $this->actualPaymethod = 'CC';
        $src = $this->handleRegister();
        $info.= '<div id="heidelpay_reuse_'.$this->_code.'">'
           .  preg_replace('/{NAME}/', $customer->getLastname(), $this->_getHelper('heidelpay')->__('Dear {NAME}, you used the following creditcard on your last visit. Do you want to re-use it?'))
           .  '<br><br>'.$this->_getHelper('heidelpay')->__('Cardtype:').' '
           .  $brand
           .  '<br>'.$this->_getHelper('heidelpay')->__('No.').' '
           .  $ccard
           .  '<br>'.$this->_getHelper('heidelpay')->__('Valid until:').' '
           .  $valid
           .  '</div>'
           .  '<div id="heidelpay_new_'.$this->_code.'" style="display: none">'
           .  '<iframe src="'.$src.'" allowtransparency="true" frameborder="0" width="500" height="210"></iframe>'
           .  '</div>'
           .  '<br><br><input type="checkbox" name="heidelpay_use_ccard" id="heidelpay_use_ccard" value="1" checked 
		   onClick="
			  var reuseObj = document.getElementById(\'heidelpay_reuse_'.$this->_code.'\');
			  var newObj = document.getElementById(\'heidelpay_new_'.$this->_code.'\');
			  if (this.checked){
				reuseObj.style.display = \'block\';
				newObj.style.display = \'none\';
			  } else {
				reuseObj.style.display = \'none\';
				newObj.style.display = \'block\';
			  }
		   "> '.$this->_getHelper('heidelpay')->__('Yes, I want to re-use my creditcard.').' ';
      }
      return $info;
    }
  }

  public function getHeidelpayIFrameUrl()
  {
    $this->actualPaymethod = 'CC';
    $src = $this->handleDebit();
    return $src;
  }
	
	/**
   * Retrieve block type for method form generation
   *
   * @return string
   */
  public function getFormBlockType()
  {
  	$this->_formBlockType = 'heidelpay/form_ccard';
    return $this->_formBlockType;
  }

   public function validate()
  {
    parent::validate();
    $post = Mage::app()->getRequest()->getParams();
    if ($post['payment']['method'] == $this->_code) {
      if (isset($post['heidelpay_use_ccard']) && $post['heidelpay_use_ccard'] == 1){
        $this->getCheckout()->setHeidelpayUseCcard(1);
        $customer = $this->getQuote()->getCustomer();
        $this->getSession()->setHpUniqueId($customer->getHeidelpay_ccard_unique_id());
      } else {
        $this->getCheckout()->setHeidelpayUseCcard(0);
      }
    }
    return $this;
  }
  
	/**
	 * Retrieve payment method title
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
	/**
	 * Retrieve payment method title for Admin
	 *
	 * @return string
	 */
	public function getAdminTitle()
	{
	  return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
}
