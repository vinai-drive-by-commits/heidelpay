<?php
class Mage_Heidelpay_Model_Method_Hpbs extends Mage_Heidelpay_Model_Method_Payment
{  
	/**
	* unique internal payment method identifier
	*    
	* @var string [a-z0-9_]   
	**/
	protected $_code = 'hpbs';
	protected $_formBlockType = 'payment/form';
	protected $_infoBlockType = 'heidelpay/info_payment';
	
  public function getLightbox($descArea = false)
  {
    $info = '';
    $info.= '<!--BillSAFE start-->
<noscript>  <a title="Ihre Vorteile" href="http://www.billsafe.de/special/payment-info" target="_blank">  <img src="https://images.billsafe.de/image/image/id/191997712fbe" style="border:0"/>
  </a></noscript><a id="billsafeAdvantagesImageLink" title="Ihre Vorteile" href="#" style="display: none;" onclick="openPopup();"><img src="https://images.billsafe.de/image/image/id/191997712fbe" style="border:0"/></a><script type="text/javascript">  var link = document.getElementById(\'billsafeAdvantagesImageLink\');
  link.style.display = \'inline\';  var openPopup = function(){    var myWindow = window.open(\'http://www.billsafe.de/special/payment-info\', \'BillSAFE\', \'width=520,height=600,left=300,top=100,scrollbars=yes\');    myWindow.focus();  };</script><!--BillSAFE end-->';
    if ($descArea){
      /*
      $info.= '<table>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Account No').':</td><td><input type="text" name="heidelpay[su_kto]" value=""></td></tr>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Bankcode').':</td><td><input type="text" name="heidelpay[su_blz]" value=""></td></tr>';
      $info.= '<tr><td>'.$this->_getHelper('heidelpay')->__('Owner').':</td><td><input type="text" name="heidelpay[su_holder]" value=""></td></tr>';
      $info.= '</table>';
			*/
    }
    return $info;
  }

  public function getHeidelpayIFrameUrl()
  {
    $this->actualPaymethod = 'BS';
    $src = $this->handleDebit();
    return $src;
  }
	
  public function getFormBlockType()
  {
  	$this->_formBlockType = 'heidelpay/form_billsafe';
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
		$params = Mage::app()->getRequest()->getParams();
		#echo '<pre>'.print_r($params,1).'</pre>';
		if (!empty($params['invoice_id'])){
			#echo $params['invoice_id'];
			$invoice = Mage::getModel('sales/order_invoice')
								 ->load($params['invoice_id']);
			#echo '<pre>'.print_r($invoice,1).'</pre>';
			#echo '<pre>'.print_r($invoice->getData(),1).'</pre>';
			#echo '<br>'.$invoice->getOrderIncrementId($params['invoice_id']).'<br>';
			$order = $invoice->getOrder();
			//$order->getInvoiceCollection();
			#echo '<pre>'.print_r($order,1).'</pre>';
			#echo '<pre>'.print_r($order->getData('customer_note'),1).'</pre>';
			#exit();
			#$order = Mage::getModel('sales/order');
			#$orderId = $invoice->getOrderId();
			#echo $orderId; exit();
      #if (!empty($orderId)){
      #  $order->loadByIncrementId($orderId);
      #}
      $note = $order->getCustomerNote();
      //$note = preg_replace('/[\r\n]*/', "\n", $note);
      if ($params['come_from'] == 'invoice' || !empty($params['order_id'])){
      	//$note = nl2br($note);
      }
			//return $this->_getHelper('heidelpay')->__($this->getConfigData('title').' '.$note);
		}
		return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
	public function getAdminTitle()
	{
	  return $this->_getHelper('heidelpay')->__($this->getConfigData('title'));
	}
	
}

