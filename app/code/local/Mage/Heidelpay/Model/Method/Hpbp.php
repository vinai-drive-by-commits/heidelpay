<?php
class Mage_Heidelpay_Model_Method_Hpbp extends Mage_Heidelpay_Model_Method_Payment
{  
	/**
	* unique internal payment method identifier
	*    
	* @var string [a-z0-9_]   
	**/
	protected $_code = 'hpbp';
	protected $_formBlockType = 'payment/form';
	protected $_infoBlockType = 'heidelpay/info_payment';

  public function getLightbox($descArea = false)
  {
    $info = '';
    if ($descArea){
      /*
      $info.= '<table>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Account No').':</td><td><input type="text" name="heidelpay[su_kto]" value=""></td></tr>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Bankcode').':</td><td><input type="text" name="heidelpay[su_blz]" value=""></td></tr>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Owner').':</td><td><input type="text" name="heidelpay[su_holder]" value=""></td></tr>';
      $info.= '</table>';
			*/
			$logopath = Mage::getDesign()->getSkinUrl('images/heidelpay/').'BarPay.jpg';
			#$info.= preg_replace('/{URL}/', $logopath, $this->_getHelper('heidelpay')->__('HP_BARPAY_INFO'));
			$infoLink = '<a href="http://www.barpay.de/info/" onclick="window.open(this.href,\'Popup\',\'width=580,height=550,scrollbars=no\');return false;" rel="nofollow"><img src="'.$logopath.'" style="border:0"></a>';
			$info.= $this->_getHelper('heidelpay')->__('HP_BARPAY_INFO').$infoLink;
			#error_log($this->_getHelper('heidelpay')->__('HP_BARPAY_INFO'), 3, 'd:\LOGS\magento.log');
			#$info.= ($this->_getHelper('heidelpay')->__('HP_BARPAY_INFO'));
			#$info.= 'Wurstblinker'.$this->_getHelper('heidelpay')->__('DescBarpay');
    }
    return $info;
  }

  public function getHeidelpayIFrameUrl()
  {
    $this->actualPaymethod = 'BP';
    $src = $this->handleDebit();
    return $src;
  }
	
  public function getFormBlockType()
  {
  	$this->_formBlockType = 'heidelpay/form_barpay';
    return $this->_formBlockType;
  }

  public function validate()
  {
    parent::validate();
		/*
    $post = Mage::app()->getRequest()->getParams();
    if ($post['payment']['method'] == $this->_code) {
      if (empty($post['heidelpay']['su_kto']) || !is_numeric($post['heidelpay']['su_kto'])){
        Mage::throwException($this->_getHelper('heidelpay')->__('Please enter account no.'));
      }
      if (empty($post['heidelpay']['su_blz']) || !is_numeric($post['heidelpay']['su_blz'])){
        Mage::throwException($this->_getHelper('heidelpay')->__('Please enter bankcode.'));
      }
      if (empty($post['heidelpay']['su_holder'])){
        Mage::throwException($this->_getHelper('heidelpay')->__('Please enter owner.'));
      }
      $this->getCheckout()->setHeidelpaySUAccountNumber($post['heidelpay']['su_kto']);
      $this->getCheckout()->setHeidelpaySUBankCode($post['heidelpay']['su_blz']);
      $this->getCheckout()->setHeidelpaySUHolder($post['heidelpay']['su_holder']);

      $onlyGP = $this->getConfigDataPayment('hpdd', 'modulemode') == 'ONLYGP';
      $onlySU = $this->getConfigDataPayment('hpdd', 'modulemode') == 'ONLYSU';
      $onlyGPORSU = $this->getConfigDataPayment('hpdd', 'modulemode') == 'ONLYGPORSU';

      if ($onlySU || $onlyGPORSU){
        // Kontodaten merken
        $customer = $this->getQuote()->getCustomer();
        $customer->setHeidelpayLastBlz($post['heidelpay']['su_blz']);
        $customer->setHeidelpayLastKto($post['heidelpay']['su_kto']);
        $customer->setHeidelpayLastHolder($post['heidelpay']['su_holder']);
        $customer->save();
      }
    }
    */
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

