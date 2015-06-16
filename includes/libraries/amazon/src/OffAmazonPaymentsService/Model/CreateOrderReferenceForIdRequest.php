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
require_once 'OffAmazonPaymentsService/Model.php';

/**
 * OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>Id: string</li>
 * <li>SellerId: string</li>
 * <li>IdType: string</li>
 * <li>InheritShippingAddress: bool</li>
 * <li>ConfirmNow: bool</li>
 * <li>OrderReferenceAttributes: OffAmazonPaymentsService_Model_OrderReferenceAttributes</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Id: string</li>
     * <li>SellerId: string</li>
     * <li>IdType: string</li>
     * <li>InheritShippingAddress: bool</li>
     * <li>ConfirmNow: bool</li>
     * <li>OrderReferenceAttributes: OffAmazonPaymentsService_Model_OrderReferenceAttributes</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'Id' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'IdType' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'InheritShippingAddress' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
            ),
            'ConfirmNow' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
            ),
            
            'OrderReferenceAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_OrderReferenceAttributes'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the Id property.
     * 
     * @return string Id
     */
    public function getId ()
    {
        return $this->_fields['Id']['FieldValue'];
    }

    /**
     * Sets the value of the Id property.
     * 
     * @param string Id
     * @return this instance
     */
    public function setId ($value)
    {
        $this->_fields['Id']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Id and returns this instance
     * 
     * @param string $value Id
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withId ($value)
    {
        $this->setId($value);
        return $this;
    }

    /**
     * Checks if Id is set
     * 
     * @return bool true if Id  is set
     */
    public function isSetId ()
    {
        return ! is_null($this->_fields['Id']['FieldValue']);
    }

    /**
     * Gets the value of the SellerId property.
     * 
     * @return string SellerId
     */
    public function getSellerId ()
    {
        return $this->_fields['SellerId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerId property.
     * 
     * @param string SellerId
     * @return this instance
     */
    public function setSellerId ($value)
    {
        $this->_fields['SellerId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerId and returns this instance
     * 
     * @param string $value SellerId
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withSellerId ($value)
    {
        $this->setSellerId($value);
        return $this;
    }

    /**
     * Checks if SellerId is set
     * 
     * @return bool true if SellerId  is set
     */
    public function isSetSellerId ()
    {
        return ! is_null($this->_fields['SellerId']['FieldValue']);
    }

    /**
     * Gets the value of the IdType property.
     * 
     * @return string IdType
     */
    public function getIdType ()
    {
        return $this->_fields['IdType']['FieldValue'];
    }

    /**
     * Sets the value of the IdType property.
     * 
     * @param string IdType
     * @return this instance
     */
    public function setIdType ($value)
    {
        $this->_fields['IdType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the IdType and returns this instance
     * 
     * @param string $value IdType
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withIdType ($value)
    {
        $this->setIdType($value);
        return $this;
    }

    /**
     * Checks if IdType is set
     * 
     * @return bool true if IdType  is set
     */
    public function isSetIdType ()
    {
        return ! is_null($this->_fields['IdType']['FieldValue']);
    }

    /**
     * Gets the value of the InheritShippingAddress property.
     * 
     * @return bool InheritShippingAddress
     */
    public function getInheritShippingAddress ()
    {
        return $this->_fields['InheritShippingAddress']['FieldValue'];
    }

    /**
     * Sets the value of the InheritShippingAddress property.
     * 
     * @param bool InheritShippingAddress
     * @return this instance
     */
    public function setInheritShippingAddress ($value)
    {
        $this->_fields['InheritShippingAddress']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the InheritShippingAddress and returns this instance
     * 
     * @param bool $value InheritShippingAddress
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withInheritShippingAddress ($value)
    {
        $this->setInheritShippingAddress($value);
        return $this;
    }

    /**
     * Checks if InheritShippingAddress is set
     * 
     * @return bool true if InheritShippingAddress  is set
     */
    public function isSetInheritShippingAddress ()
    {
        return ! is_null($this->_fields['InheritShippingAddress']['FieldValue']);
    }

    /**
     * Gets the value of the ConfirmNow property.
     * 
     * @return bool ConfirmNow
     */
    public function getConfirmNow ()
    {
        return $this->_fields['ConfirmNow']['FieldValue'];
    }

    /**
     * Sets the value of the ConfirmNow property.
     * 
     * @param bool ConfirmNow
     * @return this instance
     */
    public function setConfirmNow ($value)
    {
        $this->_fields['ConfirmNow']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ConfirmNow and returns this instance
     * 
     * @param bool $value ConfirmNow
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withConfirmNow ($value)
    {
        $this->setConfirmNow($value);
        return $this;
    }

    /**
     * Checks if ConfirmNow is set
     * 
     * @return bool true if ConfirmNow  is set
     */
    public function isSetConfirmNow ()
    {
        return ! is_null($this->_fields['ConfirmNow']['FieldValue']);
    }

    /**
     * Gets the value of the OrderReferenceAttributes.
     * 
     * @return OrderReferenceAttributes OrderReferenceAttributes
     */
    public function getOrderReferenceAttributes ()
    {
        return $this->_fields['OrderReferenceAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the OrderReferenceAttributes.
     * 
     * @param OrderReferenceAttributes OrderReferenceAttributes
     * @return void
     */
    public function setOrderReferenceAttributes ($value)
    {
        $this->_fields['OrderReferenceAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderReferenceAttributes  and returns this instance
     * 
     * @param OrderReferenceAttributes $value OrderReferenceAttributes
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest instance
     */
    public function withOrderReferenceAttributes ($value)
    {
        $this->setOrderReferenceAttributes($value);
        return $this;
    }

    /**
     * Checks if OrderReferenceAttributes  is set
     * 
     * @return bool true if OrderReferenceAttributes property is set
     */
    public function isSetOrderReferenceAttributes ()
    {
        return ! is_null($this->_fields['OrderReferenceAttributes']['FieldValue']);
    }
}
?>