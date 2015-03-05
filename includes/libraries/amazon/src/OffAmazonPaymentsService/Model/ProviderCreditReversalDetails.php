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
 * OffAmazonPaymentsService_Model_ProviderCreditReversalDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonProviderCreditReversalId: string</li>
 * <li>SellerId: string</li>
 * <li>ProviderId: string</li>
 * <li>CreditReversalReferenceId: string</li>
 * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>CreationTimestamp: string</li>
 * <li>CreditReversalStatus: OffAmazonPaymentsService_Model_Status</li>
 * <li>CreditReversalNote: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_ProviderCreditReversalDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ProviderCreditReversalDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonProviderCreditReversalId: string</li>
     * <li>SellerId: string</li>
     * <li>ProviderId: string</li>
     * <li>CreditReversalReferenceId: string</li>
     * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>CreationTimestamp: string</li>
     * <li>CreditReversalStatus: OffAmazonPaymentsService_Model_Status</li>
     * <li>CreditReversalNote: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AmazonProviderCreditReversalId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CreditReversalReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CreditReversalAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'CreationTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CreditReversalStatus' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Status'),

        'CreditReversalNote' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the AmazonProviderCreditReversalId property.
     * 
     * @return string AmazonProviderCreditReversalId
     */
    public function getAmazonProviderCreditReversalId() 
    {
        return $this->_fields['AmazonProviderCreditReversalId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonProviderCreditReversalId property.
     * 
     * @param string AmazonProviderCreditReversalId
     * @return this instance
     */
    public function setAmazonProviderCreditReversalId($value) 
    {
        $this->_fields['AmazonProviderCreditReversalId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonProviderCreditReversalId and returns this instance
     * 
     * @param string $value AmazonProviderCreditReversalId
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withAmazonProviderCreditReversalId($value)
    {
        $this->setAmazonProviderCreditReversalId($value);
        return $this;
    }


    /**
     * Checks if AmazonProviderCreditReversalId is set
     * 
     * @return bool true if AmazonProviderCreditReversalId  is set
     */
    public function isSetAmazonProviderCreditReversalId()
    {
        return !is_null($this->_fields['AmazonProviderCreditReversalId']['FieldValue']);
    }

    /**
     * Gets the value of the SellerId property.
     * 
     * @return string SellerId
     */
    public function getSellerId() 
    {
        return $this->_fields['SellerId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerId property.
     * 
     * @param string SellerId
     * @return this instance
     */
    public function setSellerId($value) 
    {
        $this->_fields['SellerId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerId and returns this instance
     * 
     * @param string $value SellerId
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withSellerId($value)
    {
        $this->setSellerId($value);
        return $this;
    }


    /**
     * Checks if SellerId is set
     * 
     * @return bool true if SellerId  is set
     */
    public function isSetSellerId()
    {
        return !is_null($this->_fields['SellerId']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
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
     * Gets the value of the CreditReversalReferenceId property.
     * 
     * @return string CreditReversalReferenceId
     */
    public function getCreditReversalReferenceId() 
    {
        return $this->_fields['CreditReversalReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReversalReferenceId property.
     * 
     * @param string CreditReversalReferenceId
     * @return this instance
     */
    public function setCreditReversalReferenceId($value) 
    {
        $this->_fields['CreditReversalReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreditReversalReferenceId and returns this instance
     * 
     * @param string $value CreditReversalReferenceId
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withCreditReversalReferenceId($value)
    {
        $this->setCreditReversalReferenceId($value);
        return $this;
    }


    /**
     * Checks if CreditReversalReferenceId is set
     * 
     * @return bool true if CreditReversalReferenceId  is set
     */
    public function isSetCreditReversalReferenceId()
    {
        return !is_null($this->_fields['CreditReversalReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the CreditReversalAmount.
     * 
     * @return Price CreditReversalAmount
     */
    public function getCreditReversalAmount() 
    {
        return $this->_fields['CreditReversalAmount']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReversalAmount.
     * 
     * @param Price CreditReversalAmount
     * @return void
     */
    public function setCreditReversalAmount($value) 
    {
        $this->_fields['CreditReversalAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CreditReversalAmount  and returns this instance
     * 
     * @param Price $value CreditReversalAmount
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withCreditReversalAmount($value)
    {
        $this->setCreditReversalAmount($value);
        return $this;
    }


    /**
     * Checks if CreditReversalAmount  is set
     * 
     * @return bool true if CreditReversalAmount property is set
     */
    public function isSetCreditReversalAmount()
    {
        return !is_null($this->_fields['CreditReversalAmount']['FieldValue']);

    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp() 
    {
        return $this->_fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string CreationTimestamp
     * @return this instance
     */
    public function setCreationTimestamp($value) 
    {
        $this->_fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withCreationTimestamp($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }


    /**
     * Checks if CreationTimestamp is set
     * 
     * @return bool true if CreationTimestamp  is set
     */
    public function isSetCreationTimestamp()
    {
        return !is_null($this->_fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the CreditReversalStatus.
     * 
     * @return Status CreditReversalStatus
     */
    public function getCreditReversalStatus() 
    {
        return $this->_fields['CreditReversalStatus']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReversalStatus.
     * 
     * @param Status CreditReversalStatus
     * @return void
     */
    public function setCreditReversalStatus($value) 
    {
        $this->_fields['CreditReversalStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CreditReversalStatus  and returns this instance
     * 
     * @param Status $value CreditReversalStatus
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withCreditReversalStatus($value)
    {
        $this->setCreditReversalStatus($value);
        return $this;
    }


    /**
     * Checks if CreditReversalStatus  is set
     * 
     * @return bool true if CreditReversalStatus property is set
     */
    public function isSetCreditReversalStatus()
    {
        return !is_null($this->_fields['CreditReversalStatus']['FieldValue']);

    }

    /**
     * Gets the value of the CreditReversalNote property.
     * 
     * @return string CreditReversalNote
     */
    public function getCreditReversalNote() 
    {
        return $this->_fields['CreditReversalNote']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReversalNote property.
     * 
     * @param string CreditReversalNote
     * @return this instance
     */
    public function setCreditReversalNote($value) 
    {
        $this->_fields['CreditReversalNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreditReversalNote and returns this instance
     * 
     * @param string $value CreditReversalNote
     * @return OffAmazonPaymentsService_Model_ProviderCreditReversalDetails instance
     */
    public function withCreditReversalNote($value)
    {
        $this->setCreditReversalNote($value);
        return $this;
    }


    /**
     * Checks if CreditReversalNote is set
     * 
     * @return bool true if CreditReversalNote  is set
     */
    public function isSetCreditReversalNote()
    {
        return !is_null($this->_fields['CreditReversalNote']['FieldValue']);
    }




}