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
 * OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderCreditReversalSummary: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummary</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ProviderCreditReversalSummary: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummary</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'ProviderCreditReversalSummary' => array('FieldValue' => array(), 'FieldType' => array('OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummary')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ProviderCreditReversalSummary.
     * 
     * @return array of ProviderCreditReversalSummary ProviderCreditReversalSummary
     */
    public function getProviderCreditReversalSummary() 
    {
        return $this->fields['ProviderCreditReversalSummary']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderCreditReversalSummary.
     * 
     * @param mixed ProviderCreditReversalSummary or an array of ProviderCreditReversalSummary ProviderCreditReversalSummary
     * @return this instance
     */
    public function setProviderCreditReversalSummary($ProviderCreditReversalSummary) 
    {
        if (!$this->_isNumericArray($ProviderCreditReversalSummary)) {
            $ProviderCreditReversalSummary =  array ($ProviderCreditReversalSummary);    
        }
        $this->fields['ProviderCreditReversalSummary']['FieldValue'] = $ProviderCreditReversalSummary;
        return $this;
    }


    /**
     * Sets single or multiple values of ProviderCreditReversalSummary list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withProviderCreditReversalSummary($ProviderCreditReversalSummary1, $ProviderCreditReversalSummary2)</code>
     * 
     * @param ProviderCreditReversalSummary  $providerCreditReversalSummaryArgs one or more ProviderCreditReversalSummary
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList  instance
     */
    public function withProviderCreditReversalSummary($providerCreditReversalSummaryArgs)
    {
        foreach (func_get_args() as $ProviderCreditReversalSummary) {
            $this->fields['ProviderCreditReversalSummary']['FieldValue'][] = $ProviderCreditReversalSummary;
        }
        return $this;
    }   



    /**
     * Checks if ProviderCreditReversalSummary list is non-empty
     * 
     * @return bool true if ProviderCreditReversalSummary list is non-empty
     */
    public function isSetProviderCreditReversalSummary()
    {
        return count ($this->fields['ProviderCreditReversalSummary']['FieldValue']) > 0;
    }




}