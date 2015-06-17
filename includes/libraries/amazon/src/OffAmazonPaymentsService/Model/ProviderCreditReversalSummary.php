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
 * OffAmazonPaymentsService_Model_ProviderCreditReversalSummary
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderId: string</li>
 * <li>ProviderCreditReversalId: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_ProviderCreditReversalSummary extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ProviderCreditReversalSummary
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ProviderId: string</li>
     * <li>ProviderCreditReversalId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'ProviderId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderCreditReversalId' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ProviderId property.
     * 
     * @return string ProviderId
     */
    public function getProviderId() 
    {
        return $this->_fields['ProviderId']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderId property.
     * 
     * @param string ProviderId
     * @return this instance
     */
    public function setProviderId($value) 
    {
        $this->_fields['ProviderId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ProviderId and returns this instance
     * 
     * @param string $value ProviderId
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalSummary instance
     */
    public function withProviderId($value)
    {
        $this->setProviderId($value);
        return $this;
    }


    /**
     * Checks if ProviderId is set
     * 
     * @return bool true if ProviderId  is set
     */
    public function isSetProviderId()
    {
        return !is_null($this->_fields['ProviderId']['FieldValue']);
    }

    /**
     * Gets the value of the ProviderCreditReversalId property.
     * 
     * @return string ProviderCreditReversalId
     */
    public function getProviderCreditReversalId() 
    {
        return $this->_fields['ProviderCreditReversalId']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderCreditReversalId property.
     * 
     * @param string ProviderCreditReversalId
     * @return this instance
     */
    public function setProviderCreditReversalId($value) 
    {
        $this->_fields['ProviderCreditReversalId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ProviderCreditReversalId and returns this instance
     * 
     * @param string $value ProviderCreditReversalId
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalSummary instance
     */
    public function withProviderCreditReversalId($value)
    {
        $this->setProviderCreditReversalId($value);
        return $this;
    }


    /**
     * Checks if ProviderCreditReversalId is set
     * 
     * @return bool true if ProviderCreditReversalId  is set
     */
    public function isSetProviderCreditReversalId()
    {
        return !is_null($this->_fields['ProviderCreditReversalId']['FieldValue']);
    }




}