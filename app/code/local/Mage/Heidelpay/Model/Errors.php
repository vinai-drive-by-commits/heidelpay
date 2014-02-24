<?php

class Mage_Heidelpay_Model_Errors extends Mage_Core_Model_Abstract
{
    /**
     * Messages Payment Interface
     */
    private $_piMessages = array(
        '0' => 'Unknown error.',
        '1' => 'Parameter missing.',
        '2' => 'Wrong format.',
        '3' => 'Invalid Format.',
        '100' => 'An error occured while payment.',
        '101' => 'Project hold.',
        '102' => 'Project was not activated.',
        '103' => 'Invalid project type.',
        '104' => 'Wrong checksum.',
        '107' => 'Invalid payment.',
        '108' => 'Invalid URL.',
        '109' => 'Bank is not attend to Giropay.',
        '110' => 'Invalid payment info.',
        '111' => 'Customer has no standard payment.',
        '115' => 'Transfer of credit card information not allowed.',
        '116' => 'Request whithout HTTPS.',
        '117' => 'Minimum age not reached.',
        '118' => 'The stated amount is invalid.',
        '120' => 'CVV invalid.',
        '121' => 'Denial of credit card institute. Please contact you credit card institute.',
        '122' => 'Denial of credit card institute. Please contact you credit card institute.',
        '123' => 'Expiration date of credit card exceeded.',
        '124' => 'Unknown card type.',
        '199' => 'Denial of credit card institute. Please contact you credit card institute.',
        '200' => 'Paymethod is not allowed in this country.',
        '201' => 'Address check failed.',
        '202' => 'Customer already exists.',
        '203' => 'Service temporarily not available.',
        '204' => 'Payment data denied.',
        '205' => 'Customer data denied.',
        '206' => 'Customer data denied.',
        '207' => 'Customer already exists.',
        '208' => 'Customer already exists.',
        '209' => 'Unknown Payment.',
        '210' => 'The request could not be processed.',
        '211' => 'No unique ccard data.',
        '212' => 'Credit card number and expected number mismatch.',
        '213' => 'The booking amount is exceeding the allowed limit.',
        '214' => 'The transaction could not be completed because the system could not check for a double transaction attempt.',
        '215' => 'The transaction has already been completed before. It has not been attempted again.',
        '217' => 'Customer has open reminder.',
        '218' => 'The booking amount is exceeding the allowed limit.',
        '219' => 'There is no method of payment available for a booking with such an amount.',
        '220' => 'The reserved time is expired.',
        '221' => 'The given amount is higher than the available amount.',
        '222' => 'An error occured while deleting the reservation.',
        '223' => 'The reservation has been already closed.',
        '300' => 'CURL error.'
    );

    /**
     * Messages Whitelabel Interface
     */
    private $_wlMessages = array(
        '0' => 'Unknown error.',
        '1' => 'An error occured while payment.',
        '2' => 'Parameter missing.',
        '3' => 'Wrong format.',
        '4' => 'Payment could not be prepared.',
        '5' => 'Payment could not be finished.',
        '6' => 'The transaction was already send.'
    );

    /**
     * Field Mapping Payment Interface
     */
    private $_piFields = array(
        'hp_p' => 'Project ID',
        'hp_eu' => 'Amount',
        'hp_payment' => 'Paymethod',
        'cus_gender' => 'Nametitle',
        'cus_title' => 'Title',
        'cus_firstname' => 'Firstname',
        'cus_lastname' => 'Lastname',
        'cus_company' => 'Company',
        'cus_street' => 'Street',
        'cus_nr' => 'Streetnumber',
        'cus_pobox' => 'Pobox',
        'cus_extra' => 'Additional Address',
        'cus_zipcode' => 'Zipcode',
        'cus_city' => 'City',
        'cus_country' => 'Country',
        'cus_state' => 'State',
        'cus_dob' => 'Birthday',
        'cus_prephone' => 'Prephone',
        'cus_phone' => 'Phone',
        'cus_prephone_work' => 'Prephone (Work)',
        'cus_phone_work' => 'Phone (Work)',
        'cus_email' => 'Email',
        'cus_language' => 'Language',
        'cus_ip' => 'IP Address',
        'cus_owner' => 'Credit card owner, Account owner',
        'cus_account' => 'Bank account',
        'cus_bankcode' => 'Bankcode',
        'cus_cc_number' => 'Credit card number',
        'cus_cc_cvv' => 'Credit card CVV',
        'cus_cc_exp_month' => 'Credit Card Expiration (Month)',
        'cus_cc_exp_year' => 'Credit Card Expiration (Year)'
    );

    /**
     * Field Mapping Whitelabel Interface
     */
    private $_wlFields = array(
        'project' => 'Project ID',
        'amount' => 'Amount',
        'nameTitle' => 'Nametitle',
        'title' => 'Title',
        'firstName' => 'Firstname',
        'lastName' => 'Lastname',
        'company' => 'Company',
        'street' => 'Street',
        'nr' => 'Streetnumber',
        'pobox' => 'Pobox',
        'extra' => 'Additional Address',
        'zipcode' => 'Zipcode',
        'city' => 'City',
        'country' => 'Country',
        'state' => 'State',
        'dob' => 'Birthday',
        'prephone' => 'Prephone',
        'phone' => 'Phone',
        'email' => 'Email',
        'language' => 'Language',
        'ipaddr' => 'IP Address',
        'owner' => 'Credit card owner, Account owner',
        'account' => 'Bank account',
        'bankcode' => 'Bankcode',
        'number' => 'Credit card number',
        'cvv' => 'Credit card CVV',
        'exp_month' => 'Credit Card Expiration (Month)',
        'exp_year' => 'Credit Card Expiration (Year)'
    );

    /**
     * Array with error information
     */
    private $_error = array(
        array(
            'code' => '',
            'message' => '',
            'fields' => array()
        )
    );

    public function __construct()
    {
        $this->_init('hp/errors');
    }

    /**
     * Get error message text Payment Interface
     *
     * @param   int $code
     * @return  string Message
     */
    private function _getErrorTextPaymentInterface($code)
    {
        if (array_key_exists($code, $this->_piMessages)) {
            return $this->_piMessages[$code];
        }
        return $this->_piMessages['0'];
    }

    /**
     * Get error message text Whitelabel Interface
     *
     * @param   int $code
     * @return  string Message
     */
    private function _getErrorTextWhitelabelInterface($code)
    {
        if (array_key_exists($code, $this->_wlMessages)) {
            return $this->_wlMessages[$code];
        }
        return $this->_wlMessages['0'];
    }

    /**
     * Get error fields text Payment Interface
     *
     * @param   string $field
     * @return  string Message
     */
    private function _getErrorFieldsPaymentInterface($field)
    {
        if (array_key_exists($field, $this->_piFields)) {
            return $this->_piFields[$field];
        }
        return $field;
    }

    /**
     * Get error fields text Whitelabel Interface
     *
     * @param   string $field
     * @return  string Message
     */
    private function _getErrorFieldsWhitelabelInterface($field)
    {
        if (array_key_exists($field, $this->_wlFields)) {
            return $this->_wlFields[$field];
        }
        return $field;
    }

    /**
     * Get error information to display the customer
     *
     * @param   array $params
     * @return  array Error information
     */
    public function getErrorInformation($params)
    {
        if (!is_array($params) || empty($params)) {
            return $this->_getDefaultMessage();
        }

        if (isset($params['error'])) {
            $fields = array();
            if (isset($params['fields']) && !empty($params['fields'])) {
                $param_fields = explode(',', $params['fields']);
                foreach ($param_fields as $field) {
                    $fields[] = $this->_getErrorFieldsWhitelabelInterface($field);
                }
            }
            if (isset($params['exterror'])) {
                $codes = explode(',', $params['exterror']);
                foreach ($codes as $code) {
                    $this->_setErrorMessage($code, $this->_getErrorTextPaymentInterface($code), $fields);
                    $fields = array();
                }
            } else {
                $this->_setErrorMessage($params['error'], $this->_getErrorTextWhitelabelInterface($params['error']), $fields);
            }
        } else if (isset($params['code_1'])) {
            $messages = true;
            $i = 1;
            while ($messages) {
                if (isset($params['code_' . $i]) && !empty($params['code_' . $i])) {
                    $fields = array();
                    if (isset($params['param_' . $i]) && !empty($params['param_' . $i])) {
                        $fields[] = $this->_getErrorFieldsPaymentInterface($params['param_' . $i]);
                    }
                    $this->_setErrorMessage($params['code_' . $i], $this->_getErrorTextPaymentInterface($params['code_' . $i]), $fields);
                } else {
                    $messages = false;
                }
                $i++;
            }
        } else {
            return $this->_getDefaultMessage();
        }

        return $this->_getErrorMessage();
    }

    /**
     * Get default error message
     *
     * @return  array Message
     */
    private function _getDefaultMessage()
    {
        $this->_error[] = array(
            'code' => '0',
            'message' => $this->_getErrorTextPaymentInterface('0'),
            'fields' => array()
        );

        return $this->_error;
    }

    /**
     * Get default error message
     *
     * @param   int $code
     * @param   string $message
     * @param   array $fields
     * @return  true
     */
    private function _setErrorMessage($code, $message, $fields)
    {
        $this->_error[] = array(
            'code' => $code,
            'message' => $message,
            'fields' => $fields
        );
        return true;
    }

    /**
     * Get error message
     *
     * @return  array Message
     */
    private function _getErrorMessage()
    {
        return $this->_error;
    }
}
