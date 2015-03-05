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
 *  @see OffAmazonPaymentsService_Model
 */
require_once 'OffAmazonPaymentsService/Model.php';  

    

/**
 * OffAmazonPaymentsService_Model_GetProviderCreditDetailsResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderCreditDetails: OffAmazonPaymentsService_Model_ProviderCreditDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_GetProviderCreditDetailsResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_GetProviderCreditDetailsResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ProviderCreditDetails: OffAmazonPaymentsService_Model_ProviderCreditDetails</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'ProviderCreditDetails' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_ProviderCreditDetails'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ProviderCreditDetails.
     * 
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails ProviderCreditDetails
     */
    public function getProviderCreditDetails() 
    {
        return $this->_fields['ProviderCreditDetails']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderCreditDetails.
     * 
     * @param OffAmazonPaymentsService_Model_ProviderCreditDetails ProviderCreditDetails
     * @return void
     */
    public function setProviderCreditDetails($value) 
    {
        $this->_fields['ProviderCreditDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ProviderCreditDetails  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_ProviderCreditDetails $value ProviderCreditDetails
     * @return OffAmazonPaymentsService_Model_GetProviderCreditDetailsResult instance
     */
    public function withProviderCreditDetails($value)
    {
        $this->setProviderCreditDetails($value);
        return $this;
    }


    /**
     * Checks if ProviderCreditDetails  is set
     * 
     * @return bool true if ProviderCreditDetails property is set
     */
    public function isSetProviderCreditDetails()
    {
        return !is_null($this->_fields['ProviderCreditDetails']['FieldValue']);

    }




}