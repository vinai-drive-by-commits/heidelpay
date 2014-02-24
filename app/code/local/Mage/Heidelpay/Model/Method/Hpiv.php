<?php

class Mage_Heidelpay_Model_Method_Hpiv extends Mage_Heidelpay_Model_Method_Payment
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     **/
    protected $_code = 'hpiv';
    protected $_formBlockType = 'payment/form';
    protected $_infoBlockType = 'heidelpay/info_payment';

    public function getLightbox($descArea = false)
    {
        $info = '';
        return $info;
    }

    public function getHeidelpayIFrameUrl()
    {
        $this->actualPaymethod = 'IV';
        $src = $this->handleDebit();
        return $src;
    }

    public function getFormBlockType()
    {
        $this->_formBlockType = 'heidelpay/form_invoice';
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

