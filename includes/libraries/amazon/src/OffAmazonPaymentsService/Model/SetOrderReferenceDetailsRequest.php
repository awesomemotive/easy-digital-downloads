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
 * OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonOrderReferenceId: string</li>
 * <li>OrderReferenceAttributes: OffAmazonPaymentsService_Model_OrderReferenceAttributes</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonOrderReferenceId: string</li>
     * <li>OrderReferenceAttributes: OffAmazonPaymentsService_Model_OrderReferenceAttributes</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonOrderReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'OrderReferenceAttributes' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderReferenceAttributes'),

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
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest instance
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
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest instance
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
     * Gets the value of the OrderReferenceAttributes.
     * 
     * @return OrderReferenceAttributes OrderReferenceAttributes
     */
    public function getOrderReferenceAttributes() 
    {
        return $this->_fields['OrderReferenceAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the OrderReferenceAttributes.
     * 
     * @param OrderReferenceAttributes OrderReferenceAttributes
     * @return void
     */
    public function setOrderReferenceAttributes($value) 
    {
        $this->_fields['OrderReferenceAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderReferenceAttributes  and returns this instance
     * 
     * @param OrderReferenceAttributes $value OrderReferenceAttributes
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest instance
     */
    public function withOrderReferenceAttributes($value)
    {
        $this->setOrderReferenceAttributes($value);
        return $this;
    }


    /**
     * Checks if OrderReferenceAttributes  is set
     * 
     * @return bool true if OrderReferenceAttributes property is set
     */
    public function isSetOrderReferenceAttributes()
    {
        return !is_null($this->_fields['OrderReferenceAttributes']['FieldValue']);

    }




}
?>