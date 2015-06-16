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
 * OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonProviderCreditId: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonProviderCreditId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonProviderCreditId' => array('FieldValue' => null, 'FieldType' => 'string'),
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
     * @return OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest instance
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
     * @return OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest instance
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




}