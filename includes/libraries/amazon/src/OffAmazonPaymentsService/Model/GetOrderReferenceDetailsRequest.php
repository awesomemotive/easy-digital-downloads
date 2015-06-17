<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
 * OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonOrderReferenceId: string</li>
 * <li>SellerId: string</li>
 * <li>AddressConsentToken: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonOrderReferenceId: string</li>
     * <li>SellerId: string</li>
     * <li>AddressConsentToken: string</li>
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AmazonOrderReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressConsentToken' => array('FieldValue' => null, 'FieldType' => 'string')
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmazonOrderReferenceId property.
     * 
     * @return string AmazonOrderReferenceId
     */
    public function getAmazonOrderReferenceId() 
    {
        return $this->_fields['AmazonOrderReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonOrderReferenceId property.
     * 
     * @param string AmazonOrderReferenceId
     * @return this instance
     */
    public function setAmazonOrderReferenceId($value) 
    {
        $this->_fields['AmazonOrderReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonOrderReferenceId and returns this instance
     * 
     * @param string $value AmazonOrderReferenceId
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest instance
     */
    public function withAmazonOrderReferenceId($value)
    {
        $this->setAmazonOrderReferenceId($value);
        return $this;
    }


    /**
     * Checks if AmazonOrderReferenceId is set
     * 
     * @return bool true if AmazonOrderReferenceId  is set
     */
    public function isSetAmazonOrderReferenceId()
    {
        return !is_null($this->_fields['AmazonOrderReferenceId']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest instance
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
     * Gets the value of the AddressConsentToken property.
     *
     * @return string AddressConsentToken
     */
    public function getAddressConsentToken()
    {
    	return $this->_fields['AddressConsentToken']['FieldValue'];
    }
    
    /**
     * Sets the value of the AddressConsentToken property.
     *
     * @param string AddressConsentToken
     * @return this instance
     */
    public function setAddressConsentToken($value)
    {
    	$this->_fields['AddressConsentToken']['FieldValue'] = $value;
    	return $this;
    }
    
    /**
     * Sets the value of the AddressConsentToken and returns this instance
     *
     * @param string $value AddressConsentToken
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest instance
     */
    public function withAddressConsentToken($value)
    {
    	$this->setAddressConsentToken($value);
    	return $this;
    }
    
    
    /**
     * Checks if AddressConsentToken is set
     *
     * @return bool true if AddressConsentToken is set
     */
    public function isSetAddressConsentToken()
    {
    	return !is_null($this->_fields['AddressConsentToken']['FieldValue']);
    }

}
?>