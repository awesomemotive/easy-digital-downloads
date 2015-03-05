<?php

/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */


/**
 *  @see OffAmazonPaymentsNotifications_Model
 */
require_once 'OffAmazonPayments/Model.php';  

    

/**
 * OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderCreditSummary: OffAmazonPaymentsNotifications_Model_ProviderCreditSummary</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ProviderCreditSummary: OffAmazonPaymentsNotifications_Model_ProviderCreditSummary</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'ProviderCreditSummary' => array('FieldValue' => array(), 'FieldType' => array('OffAmazonPaymentsNotifications_Model_ProviderCreditSummary')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ProviderCreditSummary.
     * 
     * @return array of ProviderCreditSummary ProviderCreditSummary
     */
    public function getProviderCreditSummary() 
    {
        return $this->fields['ProviderCreditSummary']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderCreditSummary.
     * 
     * @param mixed ProviderCreditSummary or an array of ProviderCreditSummary ProviderCreditSummary
     * @return this instance
     */
    public function setProviderCreditSummary($ProviderCreditSummary) 
    {
        if (!$this->_isNumericArray($ProviderCreditSummary)) {
            $ProviderCreditSummary =  array ($ProviderCreditSummary);    
        }
        $this->fields['ProviderCreditSummary']['FieldValue'] = $ProviderCreditSummary;
        return $this;
    }


    /**
     * Sets single or multiple values of ProviderCreditSummary list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withProviderCreditSummary($ProviderCreditSummary1, $ProviderCreditSummary2)</code>
     * 
     * @param ProviderCreditSummary  $providerCreditSummaryArgs one or more ProviderCreditSummary
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList  instance
     */
    public function withProviderCreditSummary($providerCreditSummaryArgs)
    {
        foreach (func_get_args() as $ProviderCreditSummary) {
            $this->fields['ProviderCreditSummary']['FieldValue'][] = $ProviderCreditSummary;
        }
        return $this;
    }   



    /**
     * Checks if ProviderCreditSummary list is non-empty
     * 
     * @return bool true if ProviderCreditSummary list is non-empty
     */
    public function isSetProviderCreditSummary()
    {
        return count ($this->fields['ProviderCreditSummary']['FieldValue']) > 0;
    }




}