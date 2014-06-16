<?php

class Mage_Heidelpay_PaymentController extends Mage_Core_Controller_Front_Action
{
    protected $_sendNewOrderEmail = TRUE;
    protected $_invoiceOrderEmail = TRUE;
    protected $_order = NULL;
    protected $_paymentInst = NULL;

    public $importantPPFields = array(
        'PRESENTATION_AMOUNT',
        'PRESENTATION_CURRENCY',
        'CONNECTOR_ACCOUNT_COUNTRY',
        'CONNECTOR_ACCOUNT_HOLDER',
        'CONNECTOR_ACCOUNT_NUMBER',
        'CONNECTOR_ACCOUNT_BANK',
        'CONNECTOR_ACCOUNT_BIC',
        'CONNECTOR_ACCOUNT_IBAN',
        'IDENTIFICATION_SHORTID',
    );

    protected function _getHelper()
    {
        return Mage::helper('heidelpay');
    }

    // Spezial f�r Multishops
    public function _xredirect($target, $secure = array()) /*{{{*/
    {
        $protocol = 'http://';
        if ($this->isHTTPS()) $protocol = 'https://';
        header('Location: ' . $protocol . $_SERVER['HTTP_HOST'] . '/' . $target);
        exit();
    }

    /*}}}*/

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

    protected function _expireAjax() /*{{{*/
    {
        if (!$this->getCheckout()->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit;
        }
    }/*}}}*/

    /**
     * Get singleton with HP order transaction information
     *
     * @return Mage_Heidelpay_Model_Method_payment
     */
    public function getHPPayment() /*{{{*/
    {
        return Mage::getSingleton('heidelpay/method_payment');
    }/*}}}*/

    /**
     * Get order model
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() /*{{{*/
    {
        return Mage::getModel('sales/order');
    }/*}}}*/

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
     * Get hp session namespace
     *
     * @return Mage_Heidelpay_Model_Session
     */
    public function getSession() /*{{{*/
    {
        return Mage::getSingleton('core/session');
        #return Mage::getSingleton('heidelpay/session');
    }/*}}}*/

    /**
     * Get HP errors namespace
     *
     * @return Mage_Heidelpay_Model_Session
     */
    public function getErrors() /*{{{*/
    {
        return Mage::getSingleton('heidelpay/errors');
    }/*}}}*/

    /**
     * iframe return from hp payment
     */
    public function iframeAction() /*{{{*/
    {
        $this->_loadCheckoutObjects();

        // set quote to active
        if ($quoteId = $this->getCheckout()->getQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
            }
        }

        $order = $this->getOrder();
        $session = $this->getCheckout();
        $order->loadByIncrementId($session->getLastRealOrderId());

        $payment = $order->getPayment()->getMethodInstance();

        $ACT_MOD_MODE = $payment->getConfigData('modulemode');
        if (!$ACT_MOD_MODE) $ACT_MOD_MODE = 'AFTER';

        $this->loadLayout();
        $iframe = $payment->getHeidelpayIFrameUrl();

        // Status und Kommentar setzen
        $order->setState($payment->getOrderState());
        $order->addStatusToHistory($payment->getOrderState(), 'IFrame: ' . $iframe, $order->getCustomerNoteNotify());
        $order->save();

        $this->getLayout()->getBlock('heidelpay_iframe')->setHPIframeUrl($iframe);
        Mage::dispatchEvent('heidelpay_payment_controller_iframe_action');
        $this->renderLayout();
    }/*}}}*/

    /**
     * Load quote and order objects from session
     */
    protected function _loadCheckoutObjects() /*{{{*/
    {
        // load quote
        $this->getCheckout()->setQuoteId($this->getCheckout()->getHeidelpayQuoteId(false));

        // load order
        $this->_order = Mage::getModel('sales/order');
        $this->_order->loadByIncrementId($this->getCheckout()->getHeidelpayLastRealOrderId(false));
    }/*}}}*/

    /**
     * lightbox return from hp payment
     */
    public function lightboxAction() /*{{{*/
    {
        $order = $this->getOrder();
        $session = $this->getCheckout();
        $order->loadByIncrementId($session->getLastRealOrderId());
        $payment = $order->getPayment()->getMethodInstance();
        $this->loadLayout();
        $iframe = $payment->getHeidelpayIFrameUrl();
        $this->getLayout()->getBlock('heidelpay_lightbox')->setHPIframeUrl($iframe);
        Mage::dispatchEvent('heidelpay_payment_controller_lightbox_action');
        $this->renderLayout();
    }/*}}}*/

    /**
     * successful return from Heidelpay payment
     */
    public function successAction() /*{{{*/
    {
        try {
            // load quote and order
            $this->_loadCheckoutObjects();

            // if order is canceled
            if ($this->_order->getStatus() == $this->getHPPayment()->getCancelState()) {
                $this->cancelAction();
                return;
            }

            // Derzeit auskommentiert, da es scheinbar den verschiedenen Magento Versionen immer andere Werte gibt
            /*
            // Check if Status is paid
            $payment = $this->_order->getPayment()->getMethodInstance();
            // echo $payment->getPaymentState().' != '.$this->_order->getStatus().' != '.$this->_order->getState().' != '.$this->getHPPayment()->getPaymentState(); return;
            if ($this->_order->getStatus() != $payment->getPaymentState()) {
              Mage::throwException($this->_getHelper()->__('Sorry, your payment has not been confirmed by the payment provider.'));
            }
            */

            $this->getCheckout()->getQuote()->setIsActive(false)->save();
            $this->getCheckout()->clear();
            Mage::dispatchEvent('heidelpay_payment_controller_success_action');
            //send confirmation email to customer
            if ($this->_order->getId()) $this->_order->sendNewOrderEmail();

            // payment is okay. show success page.
            $this->getCheckout()->setLastSuccessQuoteId($this->_order->getQuoteId());
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
            return;
        } catch (Mage_Core_Exception $e) {
            $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->_redirect('checkout/cart', array('_secure' => true));
    }/*}}}*/

    /**
     * cancel return from hp payment
     */
    public function cancelAction() /*{{{*/
    {
        try {
            // load quote and order
            $this->_loadCheckoutObjects();

            // cancel order
            if ($this->_order->canCancel()) {
                $this->_order->cancel();
                $this->_order->addStatusToHistory($this->getHPPayment()->getCancelState(), $this->_getHelper()->__('The transaction has been canceled.'));
                $this->_order->save();
            }

            $this->restock(Mage::app()->getRequest()->getParam('pc'));

            // set quote to active
            if ($quoteId = $this->getCheckout()->getQuoteId()) {
                $quote = Mage::getModel('sales/quote')->load($quoteId);
                if ($quote->getId()) {
                    $quote->setIsActive(true)->save();
                }
            }

            // add error message
            $this->getCheckout()->addError($this->_getHelper()->__('The order has been canceled.'));

            $order = $this->getOrder();
            $order->load($this->getCheckout()->getLastOrderId());
            if ($order->getId()) {
                $order->setState($this->getHPPayment()->getCancelState());
                $order->addStatusToHistory($this->getHPPayment()->getCancelState());
                $order->save();
            }

            $this->getSession()->setHpUniqueId('');

            Mage::dispatchEvent('heidelpay_payment_controller_cancel_action');
        } catch (Mage_Core_Exception $e) {
            $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
        } catch (Exception $e) {
            Mage::logException($e);
        }

        // redirect customer to cart
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

    /*}}}*/

    public function errorAction() /*{{{*/
    {
        try {
            // load quote and order
            $this->_loadCheckoutObjects();


            if ($this->_order->getPayment() !== false) {
                $payment = $this->_order->getPayment()->getMethodInstance();
            } else {
                $payment = $this->getHPPayment();
            }

            $this->restock(Mage::app()->getRequest()->getParam('pc'));

            $params = Mage::app()->getRequest()->getParams();
            Mage::log("Heidelpay - errorAction: getParams() ");
            Mage::log($params);
            if (isset($params['error'])) {
                $errormsg = utf8_encode($params['error']);
            } else {
                $errormsg = $this->_getHelper()->__('An error occured during the payment process.');
            }
            //error

            Mage::log("Heidelpay - errorAction: setPaymentMethod " . $payment->getCode());

            //load Quote and set active
            if ($quoteId = $this->getCheckout()->getLastQuoteId()) {

                Mage::log("Heidelpay - errorAction: old quoteId " . $this->getCheckout()->getQuoteId());
                Mage::log("Heidelpay - errorAction: last quoteId " . $quoteId);

                Mage::log("Heidelpay - errorAction: checkout/session->clear()");
                $quote = Mage::getModel('sales/quote')->load($quoteId);
                Mage::log("Heidelpay - errorAction: creating new quote " . $quote->getId());
                $quote->setReservedOrderId(NULL)->setIsActive(true)->save();

                Mage::log("Heidelpay - errorAction: new quoteId " . $this->getCheckout()->getQuoteId());
            }

            // cancel order
            if ($this->_order->canCancel()) {
                Mage::log("Heidelpay - errorAction: this->_order->cancel()");
                $this->_order->cancel();
                Mage::log("Heidelpay - errorAction: addStatusToHistory " . $payment->getErrorState());
                $this->_order->addStatusToHistory($payment->getErrorState(), $errormsg);
                $this->_order->save();
            }

            // add error message
            $this->getCheckout()->addError($errormsg);

            Mage::dispatchEvent('heidelpay_payment_controller_error_action');
        } catch (Mage_Core_Exception $e) {
            $this->getCheckout()->addError($this->_getHelper()->__($e->getMessage()));
        } catch (Exception $e) {
            Mage::logException($e);
        }

        // redirect customer to cart
        if (isset($params['isiframe']) && $params['isiframe'] == 1) {
            $url = Mage::getUrl('checkout/cart/', array('_secure' => true));
            echo "<html><head><script type=\"text/javascript\">";
            echo "if (top.frames.length != 0) top.location.href='" . $url . "';";
            echo "else window.location.href = '" . $url . "';";
            echo "</script></head><body></body></html>";
        } else {
            $this->_redirect('checkout/cart', array('_secure' => true));
        }

    }/*}}}*/

    /**
     * redirect return from Heidelpay payment (iframe)
     */
    public function redirectAction() /*{{{*/
    {
        try {
            $session = $this->getCheckout();

            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($session->getLastRealOrderId());

            #echo '<pre>'.print_r($order, 1).'</pre>';
            if (!$order->getId()) {
                Mage::throwException('No order for processing found');
            }

            if ($order->getPayment() !== false) {
                $payment = $order->getPayment()->getMethodInstance();
            } else {
                $payment = $this->getHPPayment();
            }

            if ($order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    $payment->getWaitState(),
                    $this->_getHelper()->__('Customer was redirected to Payment IFrame.')
                )->save();
            }

            if ($session->getQuoteId() && $session->getLastSuccessQuoteId()) {
                $session->setHeidelpayQuoteId($session->getQuoteId());
                $session->setHeidelpayLastSuccessQuoteId($session->getLastSuccessQuoteId());
                $session->setHeidelpayLastRealOrderId($session->getLastRealOrderId());
                $session->getQuote()->setIsActive(true)->save();
                $session->clear();
            }

            Mage::dispatchEvent('hp_payment_controller_redirect_action');

            $payment = $order->getPayment()->getMethodInstance()->getCode();
            #echo '<pre>'.print_r($payment, 1).'</pre>';
            if ($payment == 'hpsu') {
                $this->_redirect('heidelpay/payment/suform', array('_secure' => true));
            } else {
                $this->_redirect('heidelpay/payment/iframe', array('_secure' => true));
            }
            return;
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_redirect('checkout/cart', array('_secure' => true));
        }
    }/*}}}*/

    /**
     * iframe return from hp payment
     */
    public function suformAction() /*{{{*/
    {
        $this->_loadCheckoutObjects();

        // set quote to active
        if ($quoteId = $this->getCheckout()->getQuoteId()) {
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            if ($quote->getId()) {
                $quote->setIsActive(true)->save();
            }
        }

        $order = $this->getOrder();
        $session = $this->getCheckout();
        $order->loadByIncrementId($session->getLastRealOrderId());

        $payment = $order->getPayment()->getMethodInstance();

        $ACT_MOD_MODE = $payment->getConfigData('modulemode');
        if (!$ACT_MOD_MODE) $ACT_MOD_MODE = 'AFTER';

        $this->loadLayout();
        $iframe = $payment->getHeidelpayIFrameUrl();
        $form = $this->getSession()->getHeidelpaySURedirectForm();

        $this->getLayout()->getBlock('heidelpay_suform')->setHPSUFormData($form);
        Mage::dispatchEvent('heidelpay_payment_controller_suform_action');
        $this->renderLayout();
    }/*}}}*/

    /**
     * response from Heidelpay payment
     */
    public function responseAction() /*{{{*/
    {
        $post = Mage::app()->getRequest()->getParams();
        #echo '<pre>'.print_r($post, 1).'</pre>';

        $returnvalue = '';
        if (isset($post['PROCESSING_RESULT'])) $returnvalue = $post['PROCESSING_RESULT'];
        if ($returnvalue) {
            $shortid = $orderId = $uniqueid = $authType = $statusCode = $processReturn = $frontendCancel = $payCode = $custId = '';
            $accBrand = $accExpMonth = $accExpYear = $accHolder = $accNumber = $accBank = $actPM = '';
            $conAccCountry = $conAccHolder = $conAccNumber = $conAccBank = $conAccBic = $conAccIban = $presAmount = $presCurrency = $pm = '';
            if (isset($post['IDENTIFICATION_SHORTID'])) $shortid = $post['IDENTIFICATION_SHORTID'];
            if (isset($post['IDENTIFICATION_TRANSACTIONID'])) $orderId = $post['IDENTIFICATION_TRANSACTIONID'];
            if (isset($post['IDENTIFICATION_UNIQUEID'])) $uniqueid = $post['IDENTIFICATION_UNIQUEID'];
            if (isset($post['AUTHENTICATION_TYPE'])) $authType = $post['AUTHENTICATION_TYPE'];
            if (isset($post['PROCESSING_STATUS_CODE'])) $statusCode = $post['PROCESSING_STATUS_CODE'];
            if (isset($post['PROCESSING_RETURN'])) $processReturn = $post['PROCESSING_RETURN'];
            if (isset($post['FRONTEND_REQUEST_CANCELLED'])) $frontendCancel = $post['FRONTEND_REQUEST_CANCELLED'];
            if (isset($post['PAYMENT_CODE'])) $payCode = $post['PAYMENT_CODE'];
            if (isset($post['ACCOUNT_BRAND'])) $accBrand = $post['ACCOUNT_BRAND'];
            if (isset($post['ACCOUNT_EXPIRY_MONTH'])) $accExpMonth = $post['ACCOUNT_EXPIRY_MONTH'];
            if (isset($post['ACCOUNT_EXPIRY_YEAR'])) $accExpYear = $post['ACCOUNT_EXPIRY_YEAR'];
            if (isset($post['ACCOUNT_HOLDER'])) $accHolder = $post['ACCOUNT_HOLDER'];
            if (isset($post['ACCOUNT_NUMBER'])) $accNumber = $post['ACCOUNT_NUMBER'];
            if (isset($post['ACCOUNT_BANK'])) $accBank = $post['ACCOUNT_BANK'];
            if (isset($post['CONNECTOR_ACCOUNT_COUNTRY'])) $conAccCountry = $post['CONNECTOR_ACCOUNT_COUNTRY'];
            if (isset($post['CONNECTOR_ACCOUNT_HOLDER'])) $conAccHolder = $post['CONNECTOR_ACCOUNT_HOLDER'];
            if (isset($post['CONNECTOR_ACCOUNT_NUMBER'])) $conAccNumber = $post['CONNECTOR_ACCOUNT_NUMBER'];
            if (isset($post['CONNECTOR_ACCOUNT_BANK'])) $conAccBank = $post['CONNECTOR_ACCOUNT_BANK'];
            if (isset($post['CONNECTOR_ACCOUNT_BIC'])) $conAccBic = $post['CONNECTOR_ACCOUNT_BIC'];
            if (isset($post['CONNECTOR_ACCOUNT_IBAN'])) $conAccIban = $post['CONNECTOR_ACCOUNT_IBAN'];
            if (isset($post['PRESENTATION_AMOUNT'])) $presAmount = $post['PRESENTATION_AMOUNT'];
            if (isset($post['PRESENTATION_CURRENCY'])) $presCurrency = $post['PRESENTATION_CURRENCY'];
            if (isset($post['CRITERION_PAYMETHOD'])) $pm = $post['CRITERION_PAYMETHOD'];
            if (isset($post['actPM'])) $actPM = $post['actPM'];

            $invoiceMailComment = 'Short ID: ' . $shortid;

            // PNO Meldung Special Hack bei PROCESSING.RETURN.CODE=100.400.110
            $returnCode = '';
            if (isset($post['PROCESSING_RETURN_CODE'])) $returnCode = $post['PROCESSING_RETURN_CODE'];
            if (strpos($returnCode, '100.400.110') !== false) {
                $processReturn = $this->_getHelper('heidelpay')->__('HP_PNO_ERROR');
            }

            // Order ID extrahieren
            if (strpos($orderId, '-') !== false) {
                $parts = explode('-', $orderId);
                $orderId = $parts[0];
                $custId = $parts[1];
            }

            // Order Object
            $order = $this->getOrder();
            if (!empty($orderId)) {
                $order->loadByIncrementId($orderId);
                // Payment Object # Change 25.05.2012
                if ($order->getPayment() !== false) {
                    $payment = $order->getPayment()->getMethodInstance();
                } else {
                    $payment = $this->getHPPayment();
                }
                #echo '<pre>'.print_r($payment, 1).'</pre>';
            }

            if ($payCode == 'IV.PA' && $post['ACCOUNT_BRAND'] == 'BILLSAFE') {
                $repl = array(
                    '{AMOUNT}' => $post['CRITERION_BILLSAFE_AMOUNT'],
                    '{CURRENCY}' => $post['CRITERION_BILLSAFE_CURRENCY'],
                    '{ACC_OWNER}' => $post['CRITERION_BILLSAFE_RECIPIENT'],
                    '{ACC_BANKNAME}' => $post['CRITERION_BILLSAFE_BANKNAME'],
                    '{ACC_NUMBER}' => $post['CRITERION_BILLSAFE_ACCOUNTNUMBER'],
                    '{ACC_BANKCODE}' => $post['CRITERION_BILLSAFE_BANKCODE'],
                    '{ACC_BIC}' => $post['CRITERION_BILLSAFE_BIC'],
                    '{ACC_IBAN}' => $post['CRITERION_BILLSAFE_IBAN'],
                    '{SHORTID}' => $post['CRITERION_BILLSAFE_REFERENCE'],
                    '{LEGALNOTE}' => $post['CRITERION_BILLSAFE_LEGALNOTE'],
                    '{NOTE}' => $post['CRITERION_BILLSAFE_NOTE'],
                );

                $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
                if (is_array($locale) && !empty($locale))
                    $language = $locale[0];
                else
                    $language = $this->getDefaultLocale();

                define('HP_SUCCESS_BILLSAFE', $this->_getHelper('heidelpay')->__('HP_SUCCESS_BILLSAFE'));

                $bsData = strtr(HP_SUCCESS_BILLSAFE, $repl);
                $bsData .= ' ' . $post['CRITERION_BILLSAFE_LEGALNOTE'] . ' ';
                //$bsData.= substr($post['CRITERION_BILLSAFE_NOTE'], 0, strlen($post['CRITERION_BILLSAFE_NOTE'])-11).' '.date('d.m.Y', mktime(0,0,0,date('m'),date('d')+$post['CRITERION_BILLSAFE_PERIOD'],date('Y'))).'.';
                $bsData .= preg_replace('/{DAYS}/', $post['CRITERION_BILLSAFE_PERIOD'], $this->_getHelper('heidelpay')->__('HP_LEGALNOTE_BILLSAFE'));
                $bsData = nl2br(htmlentities($bsData));
                $invoiceMailComment = $bsData;
                $order->setCustomerNote($bsData);
                $order->save();
            }

            $params = '';
            $prePaidData = '';
            // Vorkasse Sonderkrams
            if ($payCode == 'PP.PA') {
                $params .= '&pcode=' . $payCode . '&';
                foreach ($this->importantPPFields AS $k => $v) {
                    if (isset($post[$v])) $params .= $v . '=' . $post[$v] . '&';
                }
                $repl = array(
                    '{AMOUNT}' => $presAmount,
                    '{CURRENCY}' => $presCurrency,
                    '{ACC_COUNTRY}' => $conAccCountry,
                    '{ACC_OWNER}' => $conAccHolder,
                    '{ACC_NUMBER}' => $conAccNumber,
                    '{ACC_BANKCODE}' => $conAccBank,
                    '{ACC_BIC}' => $conAccBic,
                    '{ACC_IBAN}' => $conAccIban,
                    '{SHORTID}' => $shortid,
                );

                $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
                if (is_array($locale) && !empty($locale))
                    $language = $locale[0];
                else
                    $language = $this->getDefaultLocale();

                define('HP_SUCCESS_PREPAID', $this->_getHelper('heidelpay')->__('HP_SUCCESS_PREPAID'));

                $prePaidData = strtr(HP_SUCCESS_PREPAID, $repl);
                $invoiceMailComment = $prePaidData;
            }

            // Wenn die OT Zahlung nicht erfolgreich war, dann gespeicherte Kontodaten l�schen
            if (!strstr($returnvalue, "ACK") && strpos($payCode, 'OT') !== false) {
                if ($custId != "") {
                    $customer = Mage::getModel('customer/customer')->load($custId);
                    if ($customer->getEmail() != "") {
                        $customer->setHeidelpayLastBlz($accBank);
                        $customer->setHeidelpayLastKto($accNumber);
                        $customer->setHeidelpayLastHolder($accHolder);
                        $customer->save();
                    }
                }
            }

            #echo '<pre>'.print_r($order, 1).'</pre>'; exit();
            if (strstr($returnvalue, "ACK")) {
                if (strpos($payCode, 'RG') !== false) {
                    # Register
                } else {
                    if (!empty($orderId)) {
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
                            if ($this->_invoiceOrderEmail) $invoice->sendEmail(true, $invoiceMailComment); // Rechnung versenden
                        }

                        $order->setState($payment->getPaymentState());
                        $order->addStatusToHistory($payment->getPaymentState(), 'Short ID: ' . $shortid . ' ' . $invoiceMailComment, $order->getCustomerNoteNotify());
                        if (strpos($payCode, 'PA') !== false) { # Nur bei PA speichern.
                            // TransID f�r PIXI speichern
                            $order->getPayment()->setLastTransId($uniqueid);
                        }
                        // $order->getPayment()->registerCaptureNotification($presAmount);
                        $order->setCustomerNote($invoiceMailComment); // Kommentar auch in EMail
                        $order->save();
                    }
                }
                if ($statusCode == '90' && $authType == '3DSecure') {
                    #print $base."/index.php/default/Heidelpay/payment/afterThreeDSecure/";
                    echo Mage::getUrl('heidelpay/payment/afterThreeDSecure/', array('_secure' => true));
                } else {
                    if (strpos($payCode, 'RG') !== false) {
                        #echo '<pre>Custid: '.print_r($custId, 1).'</pre>';
                        if ($custId > 0) {
                            $customer = Mage::getModel('customer/customer')->load($custId);
                            #echo '<pre>'.print_r($customer, 1).'</pre>';
                            if (strpos($payCode, 'OT') !== false) {
                                $customer->setHeidelpayLastBlz($accBank);
                                $customer->setHeidelpayLastKto($accNumber);
                                $customer->setHeidelpayLastHolder($accHolder);
                            }
                            if (strpos($payCode, 'CC') !== false) {
                                if (strpos($actPM, 'XC') !== false) {
                                    $customer->setHeidelpayXcardUniqueId($uniqueid);
                                    $customer->setHeidelpayXcardPaymentType($payCode);
                                    $customer->setHeidelpayXcard($accNumber);
                                    $customer->setHeidelpayXcardValidUntil($accExpMonth . ' / ' . $accExpYear);
                                    $customer->setHeidelpayXcardBrand($accBrand);
                                    $customer->setHeidelpayXcardHolder($accHolder);
                                } else {
                                    $customer->setHeidelpayCcardUniqueId($uniqueid);
                                    $customer->setHeidelpayCcardPaymentType($payCode);
                                    $customer->setHeidelpayCcard($accNumber);
                                    $customer->setHeidelpayCcardValidUntil($accExpMonth . ' / ' . $accExpYear);
                                    $customer->setHeidelpayCcardBrand($accBrand);
                                    $customer->setHeidelpayCcardHolder($accHolder);
                                }
                            }
                            if (strpos($payCode, 'DC') !== false) {
                                $customer->setHeidelpayDcardUniqueId($uniqueid);
                                $customer->setHeidelpayDcardPaymentType($payCode);
                                $customer->setHeidelpayDcard($accNumber);
                                $customer->setHeidelpayDcardValidUntil($accExpMonth . ' / ' . $accExpYear);
                                $customer->setHeidelpayDcardBrand($accBrand);
                                $customer->setHeidelpayDcardHolder($accHolder);
                            }
                            $customer->save();
                        }
                        echo Mage::getUrl('heidelpay/payment/afterRegister/', array('_secure' => true)) . '?uniqueId=' . $uniqueid;
                    } else {
                        echo Mage::getUrl('heidelpay/payment/success/', array('_secure' => true)) . '?uniqueId=' . $uniqueid . $params;
                    }
                }
            } else if ($frontendCancel == 'true') {
                if (!empty($orderId)) {
                    $order->setState($payment->getCancelState());
                    $order->addStatusToHistory($payment->getCancelState(), 'Cancelled by User', $order->getCustomerNoteNotify());
                    $order->save();
                }
                // Bei CC und DC nur bei DIRECT die CANCEL Methode nutzen.
                //if (!empty($orderId) && (strpos($payCode, 'CC') !== false || strpos($payCode, 'DC') !== false) && $payment->getConfigData('bookingmode') == 'DIRECT'){
                if ((strpos($payCode, 'CC') !== false || strpos($payCode, 'XC') !== false || strpos($payCode, 'DC') !== false)) {
                    print Mage::getUrl('heidelpay/payment/cancel/', array('_secure' => true, 'error' => 'Cancelled by User')) . "?pc=" . $payCode;
                } else {
                    print Mage::getUrl('checkout/onepage/', array('_secure' => true));
                }
            } else {
                if (!empty($orderId)) {
                    $order->setState($payment->getErrorState());
                    $order->addStatusToHistory($payment->getErrorState(), utf8_encode($processReturn), $order->getCustomerNoteNotify());
                    $order->save();
                }
                if ($processReturn == 'Canceled by user') {
                    print Mage::getUrl('checkout/onepage/', array('_secure' => true));
                } else {
                    if (strpos($payCode, 'CC') !== false || strpos($payCode, 'XC') !== false || strpos($payCode, 'DC') !== false) {
                        $isIframe = 1;
                    } else {
                        $isIframe = 0;
                    }
                    print Mage::getUrl('heidelpay/payment/error/', array('_secure' => true)) . '?error=' . urlencode($processReturn) . "&isiframe=" . $isIframe . "&pc=" . $payCode;
                }
            }
        } else {
            echo 'FAIL';
        }
    }

    /*}}}*/

    public function afterRegisterAction() /*{{{*/
    {
        $request = Mage::app()->getRequest()->getParams();
        $this->getSession()->setHpUniqueId($request['uniqueId']);
        echo '<script>top.payment.save()</script>';
        return;
    }

    /*}}}*/

    public function threeDSecureAction() /*{{{*/
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('heidelpay_secure')->setHP3DIframe($this->getSession()->getHeidelpayIframe());
        Mage::dispatchEvent('heidelpay_payment_controller_threeDSecure_action');
        $this->renderLayout();
    }

    /*}}}*/

    public function afterThreeDSecureAction() /*{{{*/
    {
        echo '<script>top.location.href="' . Mage::getUrl('heidelpay/payment/success/', array('_secure' => true)) . '";</script>';
    }

    /*}}}*/

    private function restock($pay_code = NULL)
    {
        if (floatval(substr(Mage::getVersion(), 0, -4)) <= floatval('1.7')) {
            $order = $this->getOrder();
            $session = $this->getCheckout();
            $order->loadByIncrementId($session->getLastRealOrderId());
            $t_type = substr($pay_code, strpos($pay_code, '.') + 1);

            if (strtoupper($t_type) != 'RG') {
                if ($this->getSession()->getStockUpdated() != $session->getLastRealOrderId()) {
                    $items = $order->getAllItems();
                    if ($items) {
                        foreach ($items as $item) {
                            $quantity = $item->getQtyOrdered();
                            $product_id = $item->getProductId();
                            // load stock for product
                            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                            // set to old qty
                            $stock->setQty($stock->getQty() + $quantity)->setIsInStock(true);
                            $stock->save();
                        }
                    }
                    $this->getSession()->setStockUpdated($session->getLastRealOrderId());
                }
            }
        }
    }
}
