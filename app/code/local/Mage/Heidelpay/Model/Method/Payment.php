<?php

class Mage_Heidelpay_Model_Method_Payment extends Mage_Payment_Model_Method_Abstract
{
    /*{{{Vars*/
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'payment';
    protected $_order;
    protected $_moduleMode = 'DIRECT';
    protected $version = '14.01.08';

    /**
     * Availability options
     */
    /*
      protected $_isGateway               = true;
      protected $_canAuthorize            = true;
      protected $_canCapture              = true;
      protected $_canCapturePartial       = false;
      protected $_canRefund               = false;
      protected $_canVoid                 = false;
      protected $_canUseInternal          = false;
      protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;
     */
    protected $_isGateway = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_paymentMethod = 'abstract';
    protected $_defaultLocale = 'en';

    /**
     * Default language
     */
    protected $_localeDefault = 'en';

    /**
     * Payment specific data
     */
    protected $_payment_url;
    protected $_request = array();
    protected $_availablePayments = array('XC', 'CC', 'DD', 'DC', 'VA', 'OT', 'IV', 'PP', 'UA', 'PC');
    protected $_allowCurrencyCode = array('AED', 'AFA', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZM', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CUP', 'CVE', 'CYP', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EEK', 'EGP', 'ERN', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GGP', 'GHC', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF', 'IDR', 'ILS', 'IMP', 'INR', 'IQD', 'IRR', 'ISK', 'JEP', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD', 'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LTL', 'LVL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO', 'MTL', 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZM', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PTS', 'PYG', 'QAR', 'RON', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDD', 'SEK', 'SGD', 'SHP', 'SIT', 'SKK', 'SLL', 'SOS', 'SPL', 'SRD', 'STD', 'SVC', 'SYP', 'SZL', 'THB', 'TJS', 'TMM', 'TND', 'TOP', 'TRL', 'TRY', 'TTD', 'TVD', 'TWD', 'TZS', 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU', 'XCD', 'XDR', 'XOF', 'XPD', 'XPF', 'XPT', 'YER', 'ZAR', 'ZMK', 'ZWD');
    protected $actualPaymethod;
    /*}}}*/

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() /*{{{*/
    {
        return Mage::getSingleton('checkout/session');
    }/*}}}*/

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() /*{{{*/
    {
        return $this->getCheckout()->getQuote();
    }/*}}}*/

    /**
     * Get heidelpay session namespace
     *
     * @return Mage_Heidelpay_Model_Session
     */
    public function getSession() /*{{{*/
    {
        return Mage::getSingleton('core/session');
        return Mage::getSingleton('heidelpay/session');
    }/*}}}*/

    /**
     * Get singleton with heidelpay order transaction information
     *
     * @return Mage_Heidelpay_Model_Method_Hppayment
     */
    public function getHPPayment() /*{{{*/
    {
        return Mage::getSingleton('heidelpay/method_payment');
    }/*}}}*/

    /**
     * Validate payment method information object
     *
     * @param   Varien_Object $info
     * @return  Mage_Heidelpay_Model_Method_Payment
     */
    public function validate() /*{{{*/
    {
        parent::validate();
        return $this;
    }

    /*}}}*/

    public function getOrder($useRG = false) /*{{{*/
    {
        if ($useRG)
            return Mage::getModel('sales/order');

        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $order = $paymentInfo->getOrder();
            #echo '<pre>'.print_r($order, 1).'</pre>';
            $incID = $order->getRealOrderId();
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($incID);
        }
        return $this->_order;
    }/*}}}*/

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType() /*{{{*/
    {
        return $this->_formBlockType;
    }/*}}}*/

    /**
     * Retirve block type for display method information
     *
     * @return string
     */
    public function getInfoBlockType() /*{{{*/
    {
        return $this->_infoBlockType;
    }/*}}}*/

    /**
     * Return true if the method can be used at this time
     *
     * @return bool
     */
    public function isAvailable($quote = null) /*{{{*/
    {
        # Allowed Currency
        $currency_code = $this->getQuote()->getQuoteCurrencyCode();
        if (!empty($currency_code) && !in_array($currency_code, $this->_allowCurrencyCode)) return false;

        // MangirKart
        if ($this->getCode() == 'hpmk' && $currency_code != 'TRY') {
            return false; // MangirKart geht nur mit TRY
        }
        $billing = $this->getQuote()->getBillingAddress();
        if ($this->getCode() == 'hpmk' && $billing->getCountry() != 'TR') {
            //return false; // MangirKart geht nur in TR
        }

        $shipping = $this->getQuote()->getShippingAddress();
        if ($this->getCode() == 'hpbs') {
            // BillSafe erwartet gleiche Shipping und Billing Adresse
            if ($billing->getFirstname() != $shipping->getFirstname()) return false;
            if ($billing->getLastname() != $shipping->getLastname()) return false;
            if ($billing->getStreet() != $shipping->getStreet()) return false;
            if ($billing->getPostcode() != $shipping->getPostcode()) return false;
            if ($billing->getCity() != $shipping->getCity()) return false;
            if ($billing->getCountry() != $shipping->getCountry()) return false;
        }

        # Minimum and maximum amount
        $totals = $this->getQuote()->getTotals();
        if (!isset($totals['grand_total'])) return false;

        $amount = sprintf('%1.2f', $totals['grand_total']->getData('value'));
        $amount = $amount * 100;
        $minamount = $this->getConfigData('min_amount');
        $maxamount = $this->getConfigData('max_amount');
        if (is_numeric($minamount) && $minamount > 0 && $minamount > $amount) return false;
        if (is_numeric($maxamount) && $maxamount > 0 && $maxamount < $amount) return false;
        return parent::isAvailable($quote);
    }/*}}}*/

    /**
     * get redirect URL after response
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl() /*{{{*/
    {
        return Mage::getUrl('heidelpay/payment/redirect', array('_secure' => true));

        #$url = Mage::getUrl('heidelpay/payment/iframe', array('_secure' => true));
        #return $url;
    }

    /*}}}*/

    public function getCheckoutRedirectUrl() /*{{{*/
    {
        #$url = Mage::getUrl('heidelpay/payment/iframe', array('_secure' => true));
        #return $url;
    }

    /*}}}*/

    public function handleRegister() /*{{{*/
    {
        $debug = false;
        $payCode = $this->actualPaymethod;

        // prepare data
        $amount = number_format($this->getQuote()->grand_total, 2, '.', '');
        $billing = $this->getQuote()->getBillingAddress();
        $customer = $this->getQuote()->getCustomer();
        $currency = $this->getQuote()->store_currency_code;
        $street = $billing->getStreet();
        $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
        $valid = array('XC', 'CC', 'DD', 'DC'); // valid payment methods
        if (!in_array($this->actualPaymethod, $valid)) throw new Exception('Invalid Payment Mode given for Register');
        if ($this->actualPaymethod == 'XC') $payCode = 'CC'; // Special f�r weiteren CC Brand
        if (is_array($locale) && !empty($locale))
            $language = $locale[0];
        else
            $language = $this->getDefaultLocale();

        $userId = $customer->getId();
        $orderId = $this->getOrder(true)->getIncrementId() . '-' . $userId;
        $email = $customer->email;
        if (empty($email)) $email = $billing->getEmail();

        $userData = array(
            'company' => $billing->getCompany(),
            'firstname' => $billing->getFirstname(),
            'lastname' => $billing->getLastname(),
            'salutation' => 'MR',
            'street' => $street[0],
            'zip' => $billing->getPostcode(),
            'city' => $billing->getCity(),
            'country' => $billing->getCountry(),
            'email' => $email,
            'ip' => $this->getOrder(true)->getRemoteIp(),
        );
        if (empty($userData['ip'])) $userData['ip'] = $_SERVER['REMOTE_ADDR']; // Falls IP Leer, dann aus dem Server holen
        $payMethod = 'RG';
        $data = $this->prepareData($orderId, $amount, $currency, $payCode, $userData, $language, $payMethod);
        if ($debug) echo '<pre>' . print_r($data, 1) . '</pre>';
        $res = $this->doRequest($data);
        if ($debug) echo 'URL: ' . $this->url;
        if ($debug) echo '<pre>resp(' . print_r($this->response, 1) . ')</pre>';
        if ($debug) echo '<pre>' . print_r($res, 1) . '</pre>';
        $res = $this->parseResult($res);
        if ($debug) echo '<pre>' . print_r($res, 1) . '</pre>';
        $processingresult = $res['result'];
        $redirectURL = $res['url'];
        $base = Mage::getUrl('heidelpay/payment/error/', array('_secure' => true));
        $src = $base . "?payment_error=hp" . strtolower($this->actualPaymethod) . '&error=' . $res['all']['PROCESSING.RETURN'] . '&' . session_name() . '=' . session_id();
        if ($processingresult == "ACK" && strstr($redirectURL, "http")) {
            $src = $redirectURL;
        } else {
            echo '<script type="text/javascript">alert("Heidelpay ' . $payCode . ' Error: ' . $res['all']['PROCESSING.RETURN'] . '");</script>';
        }
        if ($debug) {
            echo $src;
            exit();
        }
        return $src;
    }

    /*}}}*/

    public function handleDebit() /*{{{*/
    {
        $debug = false;
        $payCode = $this->actualPaymethod;
        $actModule = 'hp' . strtolower($payCode);
        $ACT_MOD_MODE = $this->getConfigData('modulemode');
        if (!$ACT_MOD_MODE) $ACT_MOD_MODE = 'AFTER';
        if ($actModule == 'hpdd') $ACT_MOD_MODE = 'DIRECT'; // Lastschrift immer DIRECT
        if ($actModule == 'hppp') $ACT_MOD_MODE = 'DIRECT'; // Vorkasse immer DIRECT
        if ($actModule == 'hpiv') $ACT_MOD_MODE = 'DIRECT'; // Rechnung immer DIRECT
        if ($actModule == 'hpmk') $ACT_MOD_MODE = 'DIRECT'; // MangirKart immer DIRECT
        $ACT_PAY_MODE = $this->getConfigData('bookingmode');

        // set transaction ID for order process
        $this->getOrder()->getPayment()->getMethodInstance()->setTransactionId($this->getOrder()->getRealOrderId());

        // prepare data
        $billing = $this->getOrder()->getBillingAddress();

        // Immer in der Basisw�hrung des Shops abrechnen
        //$amount		= number_format($this->getOrder()->getBaseGrandTotal(), 2, '.', '');
        //$currency	= $this->getOrder()->getBaseCurrencyCode();
        // in der aktuell ausgew�hlten W�hrung abrechnen
        $amount = number_format($this->getOrder()->getGrandTotal(), 2, '.', '');
        $currency = $this->getOrder()->getOrderCurrencyCode();

        #echo '<pre>'.print_r($this->getOrder(), 1).'</pre>';

        $street = $billing->getStreet();
        $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
        $valid = array('XC', 'CC', 'DD', 'DC', 'OT', 'GP', 'SU', 'IV', 'IDL', 'EPS', 'PPAL', 'PP', 'MK', 'BS', 'BP'); // valid payment methods
        if (!in_array($this->actualPaymethod, $valid)) throw new Exception('Invalid Payment Mode given. ' . $this->actualPaymethod);
        if ($this->actualPaymethod == 'XC') $payCode = 'CC'; // Special f�r weiteren CC Brand
        if ($this->actualPaymethod == 'MK') $payCode = 'PC'; // Special f�r MangirKart
        if ($this->actualPaymethod == 'BS') $payCode = 'IV'; // Special f�r BillSafe
        if ($this->actualPaymethod == 'BP') $payCode = 'PP'; // Special f�r BarPay
        if (is_array($locale) && !empty($locale))
            $language = $locale[0];
        else
            $language = $this->getDefaultLocale();

        $userId = $this->getOrder()->getCustomerId();
        $orderId = $this->getOrder()->getPayment()->getMethodInstance()->getTransactionId();
        $insertId = $orderId;
        $orderId .= '-' . $userId;

        $userData = array(
            'userid' => $userId,
            'company' => $billing->getCompany(),
            'firstname' => $billing->getFirstname(),
            'lastname' => $billing->getLastname(),
            'salutation' => 'MR', #($order->customer['gender']=='f' ? 'MRS' : 'MR'),
            'street' => $street[0],
            'zip' => $billing->getPostcode(),
            'city' => $billing->getCity(),
            'country' => $billing->getCountry(),
            'email' => $this->getOrder()->getCustomerEmail(),
            'ip' => $this->getOrder()->getRemoteIp(),
        );
        if (empty($userData['ip'])) $userData['ip'] = $_SERVER['REMOTE_ADDR']; // Falls IP Leer, dann aus dem Server holen

        if ($debug) echo 'UniqueId: ' . $this->getSession()->getHpUniqueId();

        $capture = false;
        if ($ACT_MOD_MODE == 'DIRECT') $capture = true;
        // Special CC Reuse
        if ($this->getSession()->getHpUniqueId() != '') {
            $capture = true;
        } else {
            $capture = false; // Wenn keine Unique ID hinterlegt oder Kunde die Daten nicht erneut benutzen m�chte, dann doch kein Capture
        }
        $payMethod = $ACT_PAY_MODE;
        $changePayType = array('GP', 'SU', 'IDL', 'EPS');
        if (in_array(strtoupper($payCode), $changePayType)) $payCode = 'OT';
        if (strtoupper($payCode == 'PPAL')) $payCode = 'VA';
        if (empty($payMethod)) $payMethod = 'DB';
        if ($payCode == 'OT' && $payMethod == 'DB') $payMethod = 'PA';

        if (in_array(strtoupper($payCode), array('OT', 'PP', 'IV'))) {
            if ($payMethod == 'DB') $payMethod = 'PA'; // Rechnung und Vorkasse immer PA
            $capture = true; // Rechnung und Vorkasse immer ohne IFrame
        }

        // Payment Request zusammenschrauben
        $data = $this->prepareData($orderId, $amount, $currency, $payCode, $userData, $language, $payMethod, $capture, $this->getSession()->getHpUniqueId());
        if ($debug) echo '<pre>' . print_r($data, 1) . '</pre>';
        // Mit Payment kommunizieren
        $res = $this->doRequest($data);
        if ($debug) echo '<pre>resp(' . print_r($this->response, 1) . ')</pre>';
        if ($debug) echo '<pre>' . print_r($res, 1) . '</pre>';
        // Payment Antwort auswerten
        $res = $this->parseResult($res);
        if ($debug) echo '<pre>' . print_r($res, 1) . '</pre>';

        // PNO Meldung Special Hack bei PROCESSING.RETURN.CODE=100.400.110
        if (strpos($res['all']['PROCESSING.RETURN.CODE'], '100.400.110') !== false) {
            $res['all']['PROCESSING.RETURN'] = html_entity_decode($this->_getHelper('heidelpay')->__('HP_PNO_ERROR'));
        }

        // IFrame erstmal leeren
        $this->getSession()->setHeidelpayIframe(false);
        $authType = '';
        // 3D Secure
        $post = Mage::app()->getRequest()->getParams();
        if (isset($res['all']['PROCESSING.STATUS.CODE']) && isset($res['all']['PROCESSING.RETURN.CODE']) && $res['all']['PROCESSING.STATUS.CODE'] == '80' && $res['all']['PROCESSING.RETURN.CODE'] == '000.200.000' && $res['all']['PROCESSING.REASON.CODE'] == '00') {
            $authType = '3DSecure';
            $src = $res['all']['PROCESSING.REDIRECT.URL'];
            // 3D Iframe zusammenbauen
            if ($this->actualPaymethod == 'BS') {
                $hpIframe = '<div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 9998"></div>'
                    . '<div style="position: absolute; top: 0; left: 0; z-index: 9999"><iframe src="' . $src . '" allowtransparency="true" frameborder="0" width="925" height="800" name="heidelpay_frame"></iframe></div>';
                header('Location: ' . $src);
                exit();
            } else {
                $hpIframe = '<iframe src="about:blank" allowtransparency="true" frameborder="0" width="400" height="600" name="heidelpay_frame"></iframe>';
                $hpIframe .= '<form method="post" action="' . $src . '" target="heidelpay_frame" id="heidelpay_form">';
                $hpIframe .= '<input type="hidden" name="TermUrl" value="' . $res['all']['PROCESSING.REDIRECT.PARAMETER.TermUrl'] . '">';
                $hpIframe .= '<input type="hidden" name="PaReq" value="' . $res['all']['PROCESSING.REDIRECT.PARAMETER.PaReq'] . '">';
                $hpIframe .= '<input type="hidden" name="MD" value="' . $res['all']['PROCESSING.REDIRECT.PARAMETER.MD'] . '">';
                $hpIframe .= '</form>';
                $hpIframe .= '<script type="text/javascript">document.getElementById("heidelpay_form").submit();</script>';
            }
            // IFrame setzen
            $this->getSession()->setHeidelpayIframe($hpIframe);
            // Letzte Post Daten merken
            $this->getSession()->setHpLastPost($post);
            // Postdaten wieder auslesen zur Kontrolle
            $lastPost = $this->getSession()->getHpLastPost();
            // Wenn keine Postdaten gemerkt, dann Fake Postdaten anlegen
            if (empty($lastPost)) $this->getSession()->setHpLastPost(array('hp' => 1));
            if ($debug) echo '<pre>' . print_r($this->getSession()->getHpLastPost(), 1) . '</pre>';
            // Ab zum 3D Secure
            if (!$debug) header('Location: ' . Mage::getUrl('heidelpay/payment/threeDSecure/', array('_secure' => true)));
            exit();
        } else if ($ACT_MOD_MODE == 'AFTER') {
            // Letzte Post Daten merken
            $this->getSession()->setHpLastPost($post);
        }
        $processingresult = $res['result'];
        $redirectURL = $res['url'];
        // Standard auf Fehlerseite
        $base = Mage::getUrl('heidelpay/payment/error/', array('_secure' => true));
        $src = $base . '?payment_error=' . $actModule;
        // Fehlerfall
        if ($processingresult != "ACK") {
            // Status und Kommentar setzen
            $order = $this->getOrder();
            $payment = $order->getPayment()->getMethodInstance();
            $order->setState($payment->getErrorState());
            $order->addStatusToHistory($payment->getErrorState(), 'Error: ' . $res['all']['PROCESSING.RETURN'], $order->getCustomerNoteNotify());
            $order->save();

            $src .= '&error=' . $res['all']['PROCESSING.RETURN'] . '&' . session_name() . '=' . session_id();
#	  $src.= '&pm='.$res['all']['CRITERION.PAYMETHOD'].'&error='.$res['all']['PROCESSING.RETURN'].'&'.session_name().'='.session_id();
            if (!$debug) header('Location: ' . $src);
            if ($debug) echo $src;
            exit();
            // Redirect
        } else if ($processingresult == "ACK" && strstr($redirectURL, "http")) {
            $src = $redirectURL;
            // Redirect auf Fremdsystem
        } else if (!empty($res['all']['PROCESSING.REDIRECT.URL'])) {
            // IDeal und OT Best�tigungs Seite
            if ($this->actualPaymethod == 'SU') {
                $src = $res['all']['PROCESSING.REDIRECT.URL'];
                $form = '<form method="post" action="' . $res['all']['PROCESSING_REDIRECT_URL'] . '" id="hpSURedirectForm">';
                foreach ($res['all'] AS $k => $v) {
                    if (strpos($k, 'PROCESSING_REDIRECT_PARAMETER_') !== false) {
                        $form .= '<input name="' . preg_replace('/PROCESSING_REDIRECT_PARAMETER_/', '', $k) . '" value="' . utf8_encode($v) . '" type="hidden">';
                    }
                }
                #$form.= '<input type="submit"><br>';
                $form .= '</form><script type="text/javascript">document.getElementById("hpSURedirectForm").submit();</script>';
                $this->getSession()->setHeidelpaySURedirectForm($form);

                #echo '<pre>'.print_r($form, 1).'</pre>'; exit();
                $src = Mage::getUrl('heidelpay/payment/suform/', array('_secure' => true));
            } else {
                $src = $res['all']['PROCESSING.REDIRECT.URL'];
                if (!$debug) header('Location: ' . $src);
                if (!$debug) exit();
            }
        } else if ($processingresult == "ACK") {
            // Danke Seite
            if ($debug) echo 'ActModMode: ' . $ACT_MOD_MODE . '<br>';
            if ($ACT_MOD_MODE == 'DIRECT') {
                $shortid = $res['all']['IDENTIFICATION.SHORTID'];
                $uniqueid = $res['all']['IDENTIFICATION.UNIQUEID'];
                // Order Object
                $order = $this->getOrder();
                if ($debug) echo 'OrderId: ' . $orderId . '<br>';
                if (!empty($orderId)) {
                    $order->loadByIncrementId($orderId);
                    #echo '<pre>'.print_r($order, 1).'</pre>'; exit();
                    // Payment Object
                    $payment = $order->getPayment()->getMethodInstance();
                    #echo '<pre>'.print_r($payment, 1).'</pre>'; exit();
                    // fill order
                    if ($order->canInvoice()) {
                        $convertor = Mage::getModel('sales/convert_order');
                        $invoice = $convertor->toInvoice($order);
                        foreach ($order->getAllItems() as $orderItem) {
                            if (!$orderItem->getQtyToInvoice()) {
                                continue;
                            }
                            $item = $convertor->itemToInvoiceItem($orderItem);
                            $item->setQty($orderItem->getQtyToInvoice());
                            $invoice->addItem($item);
                        }

                        $invoice->collectTotals();
                        $invoice->register()->capture();
                        Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder())
                            ->save();
                        $invoice->sendEmail(true, 'Short ID: ' . $shortid); // Rechnung versenden // NEW 18.04.2012
                    }
                    $order->setCustomerNote('Short ID: ' . $shortid); // Kommentar auch in EMail
                    $order->setState($payment->getPaymentState());
                    $order->addStatusToHistory($payment->getPaymentState(), 'Short ID: ' . $shortid, $order->getCustomerNoteNotify());
                    if ($debug) echo 'PayMethod: ' . $payMethod . '<br>';
                    if ($debug) echo 'AuthType: ' . $authType . '<br>';
                    if (strpos($payMethod, 'PA') !== false && $authType != '3DSecure') {
                        // TransID f�r PIXI speichern
                        $order->getPayment()->setLastTransId($uniqueid);
                        if ($debug) echo 'UniqueId: ' . $uniqueid . '<br>';
                    }
                    $order->save();
                }
            }

            // Speichern der Vorkasse und Rechnungsinformationen in der Bestellung und EMail
            if (in_array(strtoupper($payCode), array('PP', 'IV'))) {
                $hpPayinfos = array(
                    'CONNECTOR_ACCOUNT_BANK' => $res['all']['CONNECTOR_ACCOUNT_BANK'],
                    'CONNECTOR_ACCOUNT_BIC' => $res['all']['CONNECTOR_ACCOUNT_BIC'],
                    'CONNECTOR_ACCOUNT_COUNTRY' => $res['all']['CONNECTOR_ACCOUNT_COUNTRY'],
                    'CONNECTOR_ACCOUNT_HOLDER' => $res['all']['CONNECTOR_ACCOUNT_HOLDER'],
                    'CONNECTOR_ACCOUNT_IBAN' => $res['all']['CONNECTOR_ACCOUNT_IBAN'],
                    'CONNECTOR_ACCOUNT_NUMBER' => $res['all']['CONNECTOR_ACCOUNT_NUMBER'],
                    'PRESENTATION_AMOUNT' => $res['all']['PRESENTATION_AMOUNT'],
                    'PRESENTATION_CURRENCY' => $res['all']['PRESENTATION_CURRENCY'],
                    'IDENTIFICATION_SHORTID' => $res['all']['IDENTIFICATION_SHORTID'],
                );
                $repl = array(
                    '{AMOUNT}' => $hpPayinfos['PRESENTATION_AMOUNT'],
                    '{CURRENCY}' => $hpPayinfos['PRESENTATION_CURRENCY'],
                    '{ACC_COUNTRY}' => $hpPayinfos['CONNECTOR_ACCOUNT_COUNTRY'],
                    '{ACC_OWNER}' => $hpPayinfos['CONNECTOR_ACCOUNT_HOLDER'],
                    '{ACC_NUMBER}' => $hpPayinfos['CONNECTOR_ACCOUNT_NUMBER'],
                    '{ACC_BANKCODE}' => $hpPayinfos['CONNECTOR_ACCOUNT_BANK'],
                    '{ACC_BIC}' => $hpPayinfos['CONNECTOR_ACCOUNT_BIC'],
                    '{ACC_IBAN}' => $hpPayinfos['CONNECTOR_ACCOUNT_IBAN'],
                    '{SHORTID}' => $hpPayinfos['IDENTIFICATION_SHORTID'],
                );

                $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
                if (is_array($locale) && !empty($locale))
                    $language = $locale[0];
                else
                    $language = $this->getDefaultLocale();

                define('HP_SUCCESS_PREPAID', $this->_getHelper('heidelpay')->__('HP_SUCCESS_PREPAID'));
                $prePaidData = nl2br(strtr(HP_SUCCESS_PREPAID, $repl));
                if ($debug) echo $prePaidData;

                // BarPay
                if (!empty($res['all']['CRITERION_BARPAY_PAYCODE_URL'])) {
                    $prePaidData = preg_replace('/{LINK}/', $res['all']['CRITERION_BARPAY_PAYCODE_URL'], $this->_getHelper('heidelpay')->__('HP_BARPAY_DOWNLOAD'));
                    #error_log($this->_getHelper('heidelpay')->__('HP_BARPAY_DOWNLOAD'), 3, 'd:\LOGS\magento.log');
                }

                #$this->getSession()->setHpPayinfos($hpPayinfos);
                $order = $this->getOrder()->setCustomerNote($prePaidData);
                #$payment = $order->getPayment()->getMethodInstance();
                $order->addStatusToHistory($order->getStatus(), $prePaidData, $order->getCustomerNoteNotify());
                $order->save();
            }

            $src = Mage::getUrl('heidelpay/payment/success/', array('_secure' => true));
            if (!$debug) echo '<html><head><script type="text/javascript">window.location.replace("' . $src . '");</script></head><body></body></html>'; // Alternative per Javascript um Back Button zu umgehen.
            //if (!$debug) header('Location: '.$src);
            if (!$debug) exit();
        }
        if ($debug) echo 'Src: ' . $src . '<br>';
        if ($debug) exit();
        return $src;
    }

    /*}}}*/

    public function prepareData($orderId, $amount, $currency, $payCode, $userData, $lang, $mode = 'DB', $capture = false, $uniqueId = NULL) /*{{{*/
    {
        $payCode = strtoupper($payCode);
        $amount = sprintf('%1.2f', $amount);
        $currency = strtoupper($currency);
        $userData = $this->encodeData($userData);
        $this->pageURL = Mage::getDesign()->getSkinUrl('images/heidelpay/', array('_secure' => true));

        $parameters['SECURITY.SENDER'] = $this->getSettingData('security_sender');
        $parameters['USER.LOGIN'] = $this->getSettingData('user_id');
        $parameters['USER.PWD'] = $this->getSettingData('user_pwd');
        $parameters['TRANSACTION.CHANNEL'] = $this->getConfigData('channel');
        $parameters['TRANSACTION.MODE'] = $this->getConfigData('transactionmode');
        $parameters['REQUEST.VERSION'] = "1.0";
        $parameters['CRITERION.PAYMETHOD'] = 'hp' . strtolower($payCode);

        if (isset($userData['userid'])) {
            $parameters['IDENTIFICATION.SHOPPERID'] = $userData['userid'];
        } else {
            $parameters['IDENTIFICATION.SHOPPERID'] = 0;
        }

        $parameters['IDENTIFICATION.TRANSACTIONID'] = $orderId;

        if ($capture) {
            $parameters['FRONTEND.ENABLED'] = "false";
            if (!empty($uniqueId)) {
                $parameters['ACCOUNT.REGISTRATION'] = $uniqueId;
            }
        } else {
            $parameters['FRONTEND.ENABLED'] = "true";
        }
        $parameters['FRONTEND.REDIRECT_TIME'] = "0";
        $parameters['FRONTEND.POPUP'] = "false";
        $parameters['FRONTEND.MODE'] = "DEFAULT";
        $parameters['FRONTEND.LANGUAGE'] = $lang;
        $parameters['FRONTEND.LANGUAGE_SELECTOR'] = "true";
        $parameters['FRONTEND.ONEPAGE'] = "true";
        if ($mode == 'RG') {
            $parameters['FRONTEND.NEXTTARGET'] = "location.href";
        } else {
            $parameters['FRONTEND.NEXTTARGET'] = "top.location.href";
        }
        $parameters['FRONTEND.CSS_PATH'] = $this->pageURL . "heidelpay_style.css";
        $parameters['FRONTEND.RETURN_ACCOUNT'] = "true";

        if ($this->actualPaymethod == 'TP') {
            $parameters['CRITERION.THOMEPAY_EMB'] = "1";
            $parameters['FRONTEND.HEIGHT'] = "700";
            #$parameters['ACCOUNT.NUMBER']         = $this->getCheckout()->getHeidelpayTPAccountNumber();
            #$parameters['ACCOUNT.BANK']           = $this->getCheckout()->getHeidelpayTPBankCode();
            #$parameters['FRONTEND.ENABLED']       = "false";
        } else if ($this->actualPaymethod == 'SU') {
            $parameters['FRONTEND.HEIGHT'] = "700";
            $parameters['ACCOUNT.NUMBER'] = $this->getCheckout()->getHeidelpaySUAccountNumber();
            $parameters['ACCOUNT.BANK'] = $this->getCheckout()->getHeidelpaySUBankCode();
            $parameters['ACCOUNT.HOLDER'] = $this->getCheckout()->getHeidelpaySUHolder();
            #$parameters['FRONTEND.ENABLED']       = "false";
        } else if ($this->actualPaymethod == 'IDL' or $this->actualPaymethod == 'EPS') {
            #$parameters['ACCOUNT.NUMBER']         = $this->getCheckout()->getHeidelpayIDLAccountNumber();
            #$parameters['ACCOUNT.BANK']           = $this->getCheckout()->getHeidelpayIDLBankCode();
            #$parameters['ACCOUNT.HOLDER']         = $this->getCheckout()->getHeidelpayIDLHolder();
            #$parameters['ACCOUNT.COUNTRY']        = $_SESSION['hpIdealData']['onlineTransferCountry'];
            #$parameters['ACCOUNT.BANKNAME']       = $_SESSION['hpIdealData']['onlineTransferInstitute'];
            $parameters['FRONTEND.ENABLED'] = "true";
        } else if ($this->actualPaymethod == 'DD') {
            if ($this->getCheckout()->getHeidelpayDDAccountNumber() != '') {
                $parameters['ACCOUNT.NUMBER'] = $this->getCheckout()->getHeidelpayDDAccountNumber();
            }
            if ($this->getCheckout()->getHeidelpayDDBankCode() != '') {
                $parameters['ACCOUNT.BANK'] = $this->getCheckout()->getHeidelpayDDBankCode();
            }
            $parameters['ACCOUNT.IBAN'] = $this->getCheckout()->getHeidelpayDDIban();
            $parameters['ACCOUNT.BIC'] = $this->getCheckout()->getHeidelpayDDBic();
            $parameters['ACCOUNT.HOLDER'] = $this->getCheckout()->getHeidelpayDDHolder();
            $parameters['FRONTEND.ENABLED'] = "false";
        } else if ($this->actualPaymethod == 'GP') {
            $parameters['ACCOUNT.NUMBER'] = $this->getCheckout()->getHeidelpayGPAccountNumber();
            $parameters['ACCOUNT.BANK'] = $this->getCheckout()->getHeidelpayGPBankCode();
            $parameters['ACCOUNT.HOLDER'] = $this->getCheckout()->getHeidelpayGPHolder();
            #$parameters['FRONTEND.ENABLED']       = "false";
        } else if ($this->actualPaymethod == 'PPAL') {
            $parameters['ACCOUNT.BRAND'] = 'PAYPAL';
            #} else if ($this->actualPaymethod == 'XC'){
            #  $parameters['ACCOUNT.BRAND']          = 'VISA';
        } else if ($this->actualPaymethod == 'BS') {
            $parameters['PAYMENT.CODE'] = "IV.PA";
            $parameters['ACCOUNT.BRAND'] = "BILLSAFE";
            $parameters['FRONTEND.ENABLED'] = "false";

            $order = $this->getOrder();
            $bsParams = $this->getBillsafeBasket($order);
            $parameters = array_merge($parameters, $bsParams);

        } else if ($this->actualPaymethod == 'BP') {
            $parameters['PAYMENT.CODE'] = "PP.PA";
            $parameters['ACCOUNT.BRAND'] = "BARPAY";
            $parameters['FRONTEND.ENABLED'] = "false";
            // Return URL CRITERION.BARPAY_PAYCODE_URL
        } else if ($this->actualPaymethod == 'MK') {
            $parameters['PAYMENT.CODE'] = "PC.PA";
            $parameters['ACCOUNT.BRAND'] = "MANGIRKART";
            $parameters['FRONTEND.ENABLED'] = "false";
        }

        foreach ($this->_availablePayments as $key => $value) {
            if ($value != $payCode) {
                $parameters["FRONTEND.PM." . (string)($key + 1) . ".METHOD"] = $value;
                $parameters["FRONTEND.PM." . (string)($key + 1) . ".ENABLED"] = "false";
            }
        }

        // Wenn der Payment Code noch nicht gesetzt wurde
        if (empty($parameters['PAYMENT.CODE'])) {
            $parameters['PAYMENT.CODE'] = $payCode . "." . $mode;
        }
        $parameters['FRONTEND.RESPONSE_URL'] = Mage::getUrl('heidelpay/payment/response', array('_secure' => true, 'actPM' => $this->actualPaymethod));
        $parameters['CRITERION.RESPONSE_URL'] = $parameters['FRONTEND.RESPONSE_URL'];
        #echo $parameters['FRONTEND.RESPONSE_URL'];

        $parameters['NAME.COMPANY'] = trim($userData['company']);
        $parameters['NAME.GIVEN'] = trim($userData['firstname']);
        $parameters['NAME.FAMILY'] = trim($userData['lastname']);
        $parameters['NAME.SALUTATION'] = $userData['salutation'];
        $parameters['ADDRESS.STREET'] = $userData['street'];
        $parameters['ADDRESS.ZIP'] = $userData['zip'];
        $parameters['ADDRESS.CITY'] = $userData['city'];
        $parameters['ADDRESS.COUNTRY'] = $userData['country'];
        $parameters['CONTACT.EMAIL'] = $userData['email'];
        $parameters['CONTACT.IP'] = $userData['ip'];
        $parameters['PRESENTATION.AMOUNT'] = $amount; // 99.00
        $parameters['PRESENTATION.CURRENCY'] = $currency; // EUR
        $parameters['PRESENTATION.USAGE'] = $orderId; // ge�ndert 19.06.2012
        $parameters['ACCOUNT.COUNTRY'] = $userData['country'];

        $imagePath = Mage::getDesign()->getSkinUrl('images/heidelpay/');

        $lang = strtolower($lang);
        $filename_f = 'forward.png';
        $filename_b = 'back.png';
        $filename_frg = 'forward.jpg';

        if ($lang != 'en') {
            $front = 'forward_' . $lang . '.png';
            $back = 'back_' . $lang . '.png';
            $frontrg = 'forward_' . $lang . '.jpg';

            if (@GetImageSize($imagePath . $front)) {
                $filename_f = $front;
            }
            if (@GetImageSize($imagePath . $back)) {
                $filename_b = $back;
            }
            if (@GetImageSize($imagePath . $frontrg)) {
                $filename_frg = $frontrg;
            }
        }

        if ($mode != 'RG') {
            $parameters['FRONTEND.BUTTON.1.NAME'] = 'PAY';
            $parameters['FRONTEND.BUTTON.1.TYPE'] = 'IMAGE';
            $parameters['FRONTEND.BUTTON.1.LINK'] = $imagePath . $filename_f;
            $parameters['FRONTEND.BUTTON.2.NAME'] = 'CANCEL';
            $parameters['FRONTEND.BUTTON.2.TYPE'] = 'IMAGE';
            $parameters['FRONTEND.BUTTON.2.LINK'] = $imagePath . $filename_b;
        } else {
            $parameters['FRONTEND.BUTTON.1.NAME'] = 'PAY';
            $parameters['FRONTEND.BUTTON.1.TYPE'] = 'IMAGE';
            $parameters['FRONTEND.BUTTON.1.LINK'] = $imagePath . $filename_frg;
            $parameters['FRONTEND.BUTTON.2.NAME'] = 'CANCEL';
            $parameters['FRONTEND.BUTTON.2.TYPE'] = 'IMAGE';
            $parameters['FRONTEND.BUTTON.2.LINK'] = $imagePath . 'space.gif';
        }

        $parameters['SHOP.TYPE'] = 'Magento ' . Mage::getVersion();
        $parameters['SHOPMODULE.VERSION'] = $this->version;


#	$_SESSION[]  - aktives payment


        return $parameters;
    }

    /*}}}*/

    public function getBillsafeBasket($order) /*{{{*/
    {
        $items = $order->getAllItems();
        if ($items) {
            $i = 0;
            foreach ($items as $item) {
                $i++;
                $prefix = 'CRITERION.POS_' . sprintf('%02d', $i);
                $parameters[$prefix . '.POSITION'] = $i;
                $parameters[$prefix . '.QUANTITY'] = (int)$item->getQtyOrdered();
                $parameters[$prefix . '.UNIT'] = 'Stk.'; // Liter oder so
                $parameters[$prefix . '.AMOUNT_UNIT'] = round($item->getPrice() * 100);
                $parameters[$prefix . '.AMOUNT'] = round($item->getRowTotal() * 100);
                $parameters[$prefix . '.TEXT'] = $item->getName();
                $parameters[$prefix . '.COL1'] = 'SKU:' . $item->getSku();
                //$parameters[$prefix.'.COL2'] 						= '';
                //$parameters[$prefix.'.COL3'] 						= '';
                //$parameters[$prefix.'.COL4'] 						= '';
                $parameters[$prefix . '.ARTICLE_NUMBER'] = $item->getProductId();
                $parameters[$prefix . '.PERCENT_VAT'] = sprintf('%1.2f', $item->getTaxPercent());
                $parameters[$prefix . '.ARTICLE_TYPE'] = 'goods'; // "goods" (Versandartikel), "shipment" (Versandkosten) oder "voucher" (Gutschein/Rabatt)
            }
        }
        if ($this->getShippingNetPrice($order) > 0) {
            $i++;
            $prefix = 'CRITERION.POS_' . sprintf('%02d', $i);
            $parameters[$prefix . '.POSITION'] = $i;
            $parameters[$prefix . '.QUANTITY'] = '1';
            $parameters[$prefix . '.UNIT'] = 'Stk.'; // Liter oder so
            $parameters[$prefix . '.AMOUNT_UNIT'] = round($this->getShippingNetPrice($order) * 100);
            $parameters[$prefix . '.AMOUNT'] = round($this->getShippingNetPrice($order) * 100);
            $parameters[$prefix . '.TEXT'] = 'Shipping';
            //$parameters[$prefix.'.COL1'] 						= 'SKU:'.$item->getSku();
            //$parameters[$prefix.'.COL2'] 						= '';
            //$parameters[$prefix.'.COL3'] 						= '';
            //$parameters[$prefix.'.COL4'] 						= '';
            $parameters[$prefix . '.ARTICLE_NUMBER'] = '0';
            $parameters[$prefix . '.PERCENT_VAT'] = $this->getShippingTaxPercent($order);
            $parameters[$prefix . '.ARTICLE_TYPE'] = 'shipment'; // "goods" (Versandartikel), "shipment" (Versandkosten) oder "voucher" (Gutschein/Rabatt)
        }
        if ($order->getDiscountAmount() > 0) {
            $i++;
            $prefix = 'CRITERION.POS_' . sprintf('%02d', $i);
            $parameters[$prefix . '.POSITION'] = $i;
            $parameters[$prefix . '.QUANTITY'] = '1';
            $parameters[$prefix . '.UNIT'] = 'Stk.'; // Liter oder so
            $parameters[$prefix . '.AMOUNT_UNIT'] = round($order->getDiscountAmount() * 100);
            $parameters[$prefix . '.AMOUNT'] = round($order->getDiscountAmount() * 100);
            $parameters[$prefix . '.TEXT'] = 'Voucher';
            //$parameters[$prefix.'.COL1'] 						= 'SKU:'.$item->getSku();
            //$parameters[$prefix.'.COL2'] 						= '';
            //$parameters[$prefix.'.COL3'] 						= '';
            //$parameters[$prefix.'.COL4'] 						= '';
            $parameters[$prefix . '.ARTICLE_NUMBER'] = '0';
            $parameters[$prefix . '.PERCENT_VAT'] = '0.00';
            $parameters[$prefix . '.ARTICLE_TYPE'] = 'voucher'; // "goods" (Versandartikel), "shipment" (Versandkosten) oder "voucher" (Gutschein/Rabatt)
        }

        /*
        2. Order Item
    Attributes:
    parent_id
    quote_item_id
    product_id
    sku
    image
    name
    description
    qty_ordered
    qty_backordered
    qty_canceled
    qty_shipped
    qty_returned
    price
    cost
    discount_percent
    discount_amount
    tax_percent
    tax_amount
    row_total
    row_weight
    applied_rule_ids
        */
        return $parameters;
    }

    /*}}}*/

    protected function getShippingTaxPercent($order) /*{{{*/
    {
        $tax = ($order->getShippingTaxAmount() * 100) / $order->getShippingAmount();
        return $this->format(round($tax));
    }/*}}}*/

    /**
     * Calculates shipping price
     *
     * @param Varien_Object $order
     * @return float
     */
    protected function getShippingNetPrice($order)
    {
        $shippingTax = $order->getShippingTaxAmount();
        $price = $order->getShippingInclTax() - $shippingTax;
        $price -= $order->getShippingRefunded();
        $price -= $order->getShippingCanceled();
        return $price;
    }

    /**
     * Formats given number according to billsafe standard
     *
     * @param integer|float $number
     * @return string
     */
    private function format($number)
    {
        return number_format($number, 2, '.', '');
    }

    public function encodeData($data) /*{{{*/
    {
        $tmp = array();
        foreach ($data AS $k => $v) {
            $tmp[$k] = $v;
            if (!$this->isUTF8($v)) $tmp[$k] = utf8_encode($v);
        }
        return $tmp;
    }

    /*}}}*/

    public function isUTF8($string) /*{{{*/
    {
        if (is_array($string)) {
            $enc = implode('', $string);
            return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
        } else {
            return (utf8_encode(utf8_decode($string)) == $string);
        }
    }/*}}}*/

    // Pr�ft den IST Zustand auf dem Server
    public function isHTTPS() /*{{{*/
    {
        if (strpos($_SERVER['HTTP_HOST'], '.local') === false) {
            if (!isset($_SERVER['HTTPS']) || (strtolower($_SERVER['HTTPS']) != 'on' && $_SERVER['HTTPS'] != '1')) {
                return false;
            }
        } else {
            // Local
            return false;
        }
        return true;
    }

    /*}}}*/

    public function doRequest($data) /*{{{*/
    {
        $url = $this->getSettingData('demo_url');
        if ($this->getConfigData('transactionmode') == 'LIVE') {
            $url = $this->getSettingData('live_url');
        }
        $this->url = $url;

        // Erstellen des Strings f�r die Daten�bermittlung
        $result = '';
        foreach (array_keys($data) AS $key) {
            $data[$key] = utf8_decode($data[$key]);
            $$key = $data[$key];
            $$key = urlencode($$key);
            $$key .= "&";
            $var = strtoupper($key);
            $value = $$key;
            $result .= "$var=$value";
        }
        $strPOST = stripslashes($result);

        // pr�fen ob CURL existiert
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $strPOST);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_USERAGENT, "php ctpepost");

            $this->response = curl_exec($ch);
            $this->error = curl_error($ch);

            #echo '<pre>'.print_r($this->error, 1).'</pre>';
            curl_close($ch);

            $res = $this->response;
            if (!$this->response && $this->error) {
                $res = 'PROCESSING.RESULT=NOK&PROCESSING.RETURN=' . $this->error;
            }

        } else {
            $msg = urlencode('Curl Fehler');
            $res = 'PROCESSING.RESULT=NOK&PROCESSING.RETURN=' . $msg;
        }

        return $res;
    }

    /*}}}*/

    public function parseResult($curlresultURL) /*{{{*/
    {
        $r_arr = explode("&", $curlresultURL);
        foreach ($r_arr AS $buf) {
            $temp = urldecode($buf);
            if (strpos($buf, '=') !== false) {
                list($postatt, $postvar) = explode('=', $temp, 2);
                $returnvalue[$postatt] = $postvar;
            }
        }
        $processingresult = '';
        if (isset($returnvalue['PROCESSING.RESULT'])) $processingresult = $returnvalue['PROCESSING.RESULT'];
        if (empty($processingresult)) $processingresult = $returnvalue['POST.VALIDATION'];

        $redirectURL = '';
        if (isset($returnvalue['FRONTEND.REDIRECT_URL'])) $redirectURL = $returnvalue['FRONTEND.REDIRECT_URL'];

        if (!isset($returnvalue['PROCESSING.RETURN']) && !empty($returnvalue['POST.VALIDATION'])) {
            $returnvalue['PROCESSING.RETURN'] = 'Errorcode: ' . $returnvalue['POST.VALIDATION'];
        }
        ksort($returnvalue);
        return array('result' => $processingresult, 'url' => $redirectURL, 'all' => $returnvalue);
    }

    /*}}}*/

    public function getPaymentState() /*{{{*/
    {
        return $this->getConfigData('payment_status') ? $this->getConfigData('payment_status') : Mage_Sales_Model_Order::STATE_PROCESSING;
    }

    /*}}}*/

    public function getOrderState() /*{{{*/
    {
        return $this->getConfigData('order_status') ? $this->getConfigData('order_status') : Mage_Sales_Model_Order::STATE_PROCESSING;
    }

    /*}}}*/

    public function getCancelState() /*{{{*/
    {
        return $this->getConfigData('cancel_status') ? $this->getConfigData('cancel_status') : Mage_Sales_Model_Order::STATE_CANCELED;
    }

    /*}}}*/

    public function getErrorState() /*{{{*/
    {
        return $this->getConfigData('error_status') ? $this->getConfigData('error_status') : Mage_Sales_Model_Order::STATE_CLOSED;
    }

    /*}}}*/

    public function getWaitState() /*{{{*/
    {
        return $this->getConfigData('wait_status') ? $this->getConfigData('wait_status') : Mage_Sales_Model_Order::STATE_PROCESSING;
    }

    /*}}}*/

    protected function _getBookingMode($type) /*{{{*/
    {
        return $this->getConfigData('bookingmode' . $type) ? $this->getConfigData('bookingmode' . $type) : $this->_default_bookingmode;
    }

    /*}}}*/

    protected function _getPaymentMode() /*{{{*/
    {
        return $this->getConfigData('paymentmode') ? $this->getConfigData('paymentmode') : $this->_default_paymentmode;
    }/*}}}*/

    /**
     * Retrieve information from payment configuration
     *
     * @param   string $field
     * @return  mixed
     */
    public function getConfigData($field, $storeId = null) /*{{{*/
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        #var_dump($storeId);
        $path = 'payment/' . $this->getCode() . '/' . $field;
        $value = Mage::getStoreConfig($path, $storeId);
        if (!$value) {
            $path = 'heidelpay/' . $this->getHPPayment()->getCode() . '/' . $field;
            $value = Mage::getStoreConfig($path, $storeId);
        }
        return $value;
    }/*}}}*/

    /**
     * Retrieve information from payment configuration
     *
     * @param   string $payment
     * @param   string $field
     * @return  mixed
     */
    public function getConfigDataPayment($payment, $field, $storeId = null) /*{{{*/
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/' . $payment . '/' . $field;
        $value = Mage::getStoreConfig($path, $storeId);
        return $value;
    }

    /*}}}*/

    public function getSettingData($field, $storeId = null) /*{{{*/
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        #var_dump($storeId);
        $path = 'heidelpay/settings/' . $field;
        $value = Mage::getStoreConfig($path, $storeId);
        return $value;
    }/*}}}*/

    /**
     * Retrieve payment method title for Admin
     *
     * @return string
     */
    public function getAdminTitle() /*{{{*/
    {
        return 'Heidelpay';
    }
    /*}}}*/
}
