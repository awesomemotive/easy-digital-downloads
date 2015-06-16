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
 * OffAmazonPaymentsService_Model_ReverseProviderCreditRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonProviderCreditId: string</li>
 * <li>CreditReversalReferenceId: string</li>
 * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>CreditReversalNote: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_ReverseProviderCreditRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ReverseProviderCreditRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonProviderCreditId: string</li>
     * <li>CreditReversalReferenceId: string</li>
     * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>CreditReversalNote: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonProviderCreditId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CreditReversalReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CreditReversalAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'CreditReversalNote' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
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
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditRequest instance
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
     * Gets the value of the AmazonProviderCreditId property.
     * 
     * @return string AmazonProviderCreditId
     */
    public function getAmazonProviderCreditId() 
    {
        return $this->_fields['AmazonProviderCreditId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonProviderCreditId property.
     * 
     * @param string AmazonProviderCreditId
     * @return this instance
     */
    public function setAmazonProviderCreditId($value) 
    {
        $this->_fields['AmazonProviderCreditId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonProviderCreditId and returns this instance
     * 
     * @param string $value AmazonProviderCreditId
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditRequest instance
     */
    public function withAmazonProviderCreditId($value)
    {
        $this->setAmazonProviderCreditId($value);
        return $this;
    }


    /**
     * Checks if AmazonProviderCreditId is set
     * 
     * @return bool true if AmazonProviderCreditId  is set
     */
    public function isSetAmazonProviderCreditId()
    {
        return !is_null($this->_fields['AmazonProviderCreditId']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditRequest instance
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
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditRequest instance
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
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditRequest instance
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