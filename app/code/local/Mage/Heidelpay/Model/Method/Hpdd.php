<?php
class Mage_Heidelpay_Model_Method_Hpdd extends Mage_Heidelpay_Model_Method_Payment
{
	/**
	* unique internal payment method identifier
	*    
	* @var string [a-z0-9_]   
	**/
	protected $_code = 'hpdd';
	protected $_formBlockType = 'payment/form';
	protected $_infoBlockType = 'heidelpay/info_payment';
	
	public function getLightbox($descArea = false){	
		$info = '';
		$sepamode = $this->getConfigData('sepamode');

		if ($descArea){
			$info.= '<table>';		
			if(is_int(strpos($sepamode, 'both'))){
				$info .= "<script type='text/javascript'>
					var iban_switch = document.getElementById('iban_switch');
					
					var accNr 		= document.getElementsByName('heidelpay[dd_kto]')[0];
					var accBank 	= document.getElementsByName('heidelpay[dd_blz]')[0];
					var accIban 	= document.getElementsByName('heidelpay[dd_iban]')[0];
					var accBic 	= document.getElementsByName('heidelpay[dd_bic]')[0];

					if(iban_switch.value == 'iban'){ iban(); }
					if(iban_switch.value == 'noiban'){ noiban(); }

					iban_switch.onchange = function(){
						if(this.value == 'iban'){ iban(); }
						if(this.value == 'noiban'){ noiban(); }
					}
					
					function iban(){
						accNr.parentNode.parentNode.style.display = 'none';
						accBank.parentNode.parentNode.style.display = 'none';
						accIban.parentNode.parentNode.style.display = 'table-row';
						accBic.parentNode.parentNode.style.display = 'table-row';
					}						
					function noiban(){
						accNr.parentNode.parentNode.style.display = 'table-row';
						accBank.parentNode.parentNode.style.display = 'table-row';
						accIban.parentNode.parentNode.style.display = 'none';
						accBic.parentNode.parentNode.style.display = 'none';
					}
				</script>";
							
				$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Account information').':</td><td>				
				<select name="heidelpay[dd_sepaSwitch]" id="iban_switch">					
					<option value="iban">'.$this->_getHelper('heidelpay')->__('IBAN & BIC').'</option>
					<option value="noiban">'.$this->_getHelper('heidelpay')->__('Account no. & Bank no.').'</option>
				</select>				
				</td></tr>';
			}
			if(is_int(strpos($sepamode, 'classic')) || is_int(strpos($sepamode, 'both')) ){
				$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Account No').':</td><td><input type="text" name="heidelpay[dd_kto]" value=""></td></tr>';
				$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Bankcode').':</td><td><input type="text" name="heidelpay[dd_blz]" value=""></td></tr>';		
			}
			if(is_int(strpos($sepamode, 'iban')) || is_int(strpos($sepamode, 'both')) ){
				$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('IBAN').':</td><td><input type="text" name="heidelpay[dd_iban]" value=""></td></tr>';
				$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('BIC').':</td><td><input type="text" name="heidelpay[dd_bic]" value=""></td></tr>';				
			}
			
			$info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Owner').':</td><td><input type="text" name="heidelpay[dd_holder]" value="'.$this->getQuote()->getBillingAddress()->getFirstname().' '.$this->getQuote()->getBillingAddress()->getLastname().'"></td></tr>';
			$info.= '</table>';
		} 
		return $info;
	}

	public function getHeidelpayIFrameUrl(){
		$this->actualPaymethod = 'DD';
		#$src = $this->handleRegister();
		$src = $this->handleDebit();
		return $src;
	}
	
	/**
   * Retrieve block type for method form generation
   *
   * @return string
   */
	public function getFormBlockType(){
		$this->_formBlockType = 'heidelpay/form_directdebit';
		return $this->_formBlockType;
	}

	public function validate(){
		parent::validate();
		$post = Mage::app()->getRequest()->getParams();

		if($post['payment']['method'] == $this->_code){
			if(count($post['heidelpay']) > 3){		
				if( ($post['heidelpay']['dd_sepaSwitch'] == 'iban' && (($post['heidelpay']['dd_iban'] == '') || ($post['heidelpay']['dd_bic'] == ''))) || 
				($post['heidelpay']['dd_sepaSwitch'] == 'noiban' && (($post['heidelpay']['dd_kto'] == '') || ($post['heidelpay']['dd_blz'] == ''))) || 
				($post['heidelpay']['dd_holder'] == '') ){
					Mage::throwException($this->_getHelper('heidelpay')->__('Direct Debit data not correct or incomplete'));
				}
			}else{
				foreach($post['heidelpay'] as $key => $value){
					if($value == ''){
						Mage::throwException($this->_getHelper('heidelpay')->__('Direct Debit data not correct or incomplete'));
					}
				}
			}
			$this->getCheckout()->setHeidelpayDDAccountNumber($post['heidelpay']['dd_kto']);
			$this->getCheckout()->setHeidelpayDDBankCode($post['heidelpay']['dd_blz']);
			$this->getCheckout()->setHeidelpayDDIban($post['heidelpay']['dd_iban']);
			$this->getCheckout()->setHeidelpayDDBic($post['heidelpay']['dd_bic']);
			$this->getCheckout()->setHeidelpayDDHolder($post['heidelpay']['dd_holder']);
		}
	return $this;
	}

	/**
	 * Retrieve payment method title
	 *
	 * @return string
	 */
	public function getTitle(){
		return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
	/**
	 * Retrieve payment method title for Admin
	 *
	 * @return string
	 */
	public function getAdminTitle(){
	  return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
}