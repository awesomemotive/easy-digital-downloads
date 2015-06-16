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
 * OffAmazonPaymentsService_Model_ProviderCreditDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonProviderCreditId: string</li>
 * <li>SellerId: string</li>
 * <li>ProviderId: string</li>
 * <li>CreditReferenceId: string</li>
 * <li>CreditAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>CreditReversalIdList: OffAmazonPaymentsService_Model_IdList</li>
 * <li>CreationTimestamp: string</li>
 * <li>CreditStatus: OffAmazonPaymentsService_Model_Status</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_ProviderCreditDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ProviderCreditDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonProviderCreditId: string</li>
     * <li>SellerId: string</li>
     * <li>ProviderId: string</li>
     * <li>CreditReferenceId: string</li>
     * <li>CreditAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>CreditReversalAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>CreditReversalIdList: OffAmazonPaymentsService_Model_IdList</li>
     * <li>CreationTimestamp: string</li>
     * <li>CreditStatus: OffAmazonPaymentsService_Model_Status</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AmazonProviderCreditId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CreditReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CreditAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),


        'CreditReversalAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),


        'CreditReversalIdList' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_IdList'),

        'CreationTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CreditStatus' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Status'),

        );
        parent::__construct($data);
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
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
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
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
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
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
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
     * Gets the value of the CreditReferenceId property.
     * 
     * @return string CreditReferenceId
     */
    public function getCreditReferenceId() 
    {
        return $this->_fields['CreditReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReferenceId property.
     * 
     * @param string CreditReferenceId
     * @return this instance
     */
    public function setCreditReferenceId($value) 
    {
        $this->_fields['CreditReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreditReferenceId and returns this instance
     * 
     * @param string $value CreditReferenceId
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
     */
    public function withCreditReferenceId($value)
    {
        $this->setCreditReferenceId($value);
        return $this;
    }


    /**
     * Checks if CreditReferenceId is set
     * 
     * @return bool true if CreditReferenceId  is set
     */
    public function isSetCreditReferenceId()
    {
        return !is_null($this->_fields['CreditReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the CreditAmount.
     * 
     * @return Price CreditAmount
     */
    public function getCreditAmount() 
    {
        return $this->_fields['CreditAmount']['FieldValue'];
    }

    /**
     * Sets the value of the CreditAmount.
     * 
     * @param Price CreditAmount
     * @return void
     */
    public function setCreditAmount($value) 
    {
        $this->_fields['CreditAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CreditAmount  and returns this instance
     * 
     * @param Price $value CreditAmount
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
     */
    public function withCreditAmount($value)
    {
        $this->setCreditAmount($value);
        return $this;
    }


    /**
     * Checks if CreditAmount  is set
     * 
     * @return bool true if CreditAmount property is set
     */
    public function isSetCreditAmount()
    {
        return !is_null($this->_fields['CreditAmount']['FieldValue']);

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
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
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
     * Gets the value of the CreditReversalIdList.
     * 
     * @return IdList CreditReversalIdList
     */
    public function getCreditReversalIdList() 
    {
        return $this->_fields['CreditReversalIdList']['FieldValue'];
    }

    /**
     * Sets the value of the CreditReversalIdList.
     * 
     * @param IdList CreditReversalIdList
     * @return void
     */
    public function setCreditReversalIdList($value) 
    {
        $this->_fields['CreditReversalIdList']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CreditReversalIdList  and returns this instance
     * 
     * @param IdList $value CreditReversalIdList
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
     */
    public function withCreditReversalIdList($value)
    {
        $this->setCreditReversalIdList($value);
        return $this;
    }


    /**
     * Checks if CreditReversalIdList  is set
     * 
     * @return bool true if CreditReversalIdList property is set
     */
    public function isSetCreditReversalIdList()
    {
        return !is_null($this->_fields['CreditReversalIdList']['FieldValue']);

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
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
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
     * Gets the value of the CreditStatus.
     * 
     * @return Status CreditStatus
     */
    public function getCreditStatus() 
    {
        return $this->_fields['CreditStatus']['FieldValue'];
    }

    /**
     * Sets the value of the CreditStatus.
     * 
     * @param Status CreditStatus
     * @return void
     */
    public function setCreditStatus($value) 
    {
        $this->_fields['CreditStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CreditStatus  and returns this instance
     * 
     * @param Status $value CreditStatus
     * @return OffAmazonPaymentsService_Model_ProviderCreditDetails instance
     */
    public function withCreditStatus($value)
    {
        $this->setCreditStatus($value);
        return $this;
    }


    /**
     * Checks if CreditStatus  is set
     * 
     * @return bool true if CreditStatus property is set
     */
    public function isSetCreditStatus()
    {
        return !is_null($this->_fields['CreditStatus']['FieldValue']);

    }




}