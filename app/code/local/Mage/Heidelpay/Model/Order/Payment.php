<?php
class Mage_Heidelpay_Model_Order_Payment extends Mage_Sales_Model_Order_Payment
{
	/**
	 * Place payment information
	 *
	 * This method are colling when order will be place
	 *
	 * @return Mage_Sales_Model_Order_Payment
	 */
	public function place()
	{
		$methodInstance = $this->getMethodInstance();
		
		if (!($methodInstance instanceof Mage_Heidelpay_Model_Method_payment)) {
      return parent::place();
    }

    $this->setAmountOrdered($this->getOrder()->getTotalDue());
    $this->setBaseAmountOrdered($this->getOrder()->getBaseTotalDue());

    $this->setShippingAmount($this->getOrder()->getShippingAmount());
    $this->setBaseShippingAmount($this->getOrder()->getBaseShippingAmount());
    
    /**
     * validating payment method again
     */
    $methodInstance->validate();
    
    /**
		 * Authorize payment
		 */
		if ($action = $methodInstance->getConfigData('payment_action')) {
			switch ($action) {
        case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
			    $methodInstance->authorize($this, $this->getOrder()->getBaseTotalDue());
					$this->setAmountAuthorized($this->getOrder()->getTotalDue());
					$this->setBaseAmountAuthorized($this->getOrder()->getBaseTotalDue());
					break;
				default:
          break;
			}
		}
		
		$tmporder = Mage::getModel('sales/order');
		$tmporder->load($this->getOrder()->getId());
		$orderState = $tmporder->getData('state');
		$orderStatus = $tmporder->getData('status');
	
		$this->getOrder()->setState($orderState);
		$this->getOrder()->setData('status',$orderStatus);

    return $this;
	}
}
