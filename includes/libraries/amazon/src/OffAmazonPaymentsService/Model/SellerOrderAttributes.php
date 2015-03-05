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
 * OffAmazonPaymentsService_Model_SellerOrderAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerOrderId: string</li>
 * <li>StoreName: string</li>
 * <li>OrderItemCategories: OffAmazonPaymentsService_Model_OrderItemCategories</li>
 * <li>CustomInformation: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_SellerOrderAttributes extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_SellerOrderAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerOrderId: string</li>
     * <li>StoreName: string</li>
     * <li>OrderItemCategories: OffAmazonPaymentsService_Model_OrderItemCategories</li>
     * <li>CustomInformation: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerOrderId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'StoreName' => array('FieldValue' => null, 'FieldType' => 'string'),

        'OrderItemCategories' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderItemCategories'),

        'CustomInformation' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the SellerOrderId property.
     * 
     * @return string SellerOrderId
     */
    public function getSellerOrderId() 
    {
        return $this->_fields['SellerOrderId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerOrderId property.
     * 
     * @param string SellerOrderId
     * @return this instance
     */
    public function setSellerOrderId($value) 
    {
        $this->_fields['SellerOrderId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerOrderId and returns this instance
     * 
     * @param string $value SellerOrderId
     * @return OffAmazonPaymentsService_Model_SellerOrderAttributes instance
     */
    public function withSellerOrderId($value)
    {
        $this->setSellerOrderId($value);
        return $this;
    }


    /**
     * Checks if SellerOrderId is set
     * 
     * @return bool true if SellerOrderId  is set
     */
    public function isSetSellerOrderId()
    {
        return !is_null($this->_fields['SellerOrderId']['FieldValue']);
    }

    /**
     * Gets the value of the StoreName property.
     * 
     * @return string StoreName
     */
    public function getStoreName() 
    {
        return $this->_fields['StoreName']['FieldValue'];
    }

    /**
     * Sets the value of the StoreName property.
     * 
     * @param string StoreName
     * @return this instance
     */
    public function setStoreName($value) 
    {
        $this->_fields['StoreName']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the StoreName and returns this instance
     * 
     * @param string $value StoreName
     * @return OffAmazonPaymentsService_Model_SellerOrderAttributes instance
     */
    public function withStoreName($value)
    {
        $this->setStoreName($value);
        return $this;
    }


    /**
     * Checks if StoreName is set
     * 
     * @return bool true if StoreName  is set
     */
    public function isSetStoreName()
    {
        return !is_null($this->_fields['StoreName']['FieldValue']);
    }

    /**
     * Gets the value of the OrderItemCategories.
     * 
     * @return OrderItemCategories OrderItemCategories
     */
    public function getOrderItemCategories() 
    {
        return $this->_fields['OrderItemCategories']['FieldValue'];
    }

    /**
     * Sets the value of the OrderItemCategories.
     * 
     * @param OrderItemCategories OrderItemCategories
     * @return void
     */
    public function setOrderItemCategories($value) 
    {
        $this->_fields['OrderItemCategories']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderItemCategories  and returns this instance
     * 
     * @param OrderItemCategories $value OrderItemCategories
     * @return OffAmazonPaymentsService_Model_SellerOrderAttributes instance
     */
    public function withOrderItemCategories($value)
    {
        $this->setOrderItemCategories($value);
        return $this;
    }


    /**
     * Checks if OrderItemCategories  is set
     * 
     * @return bool true if OrderItemCategories property is set
     */
    public function isSetOrderItemCategories()
    {
        return !is_null($this->_fields['OrderItemCategories']['FieldValue']);

    }

    /**
     * Gets the value of the CustomInformation property.
     * 
     * @return string CustomInformation
     */
    public function getCustomInformation() 
    {
        return $this->_fields['CustomInformation']['FieldValue'];
    }

    /**
     * Sets the value of the CustomInformation property.
     * 
     * @param string CustomInformation
     * @return this instance
     */
    public function setCustomInformation($value) 
    {
        $this->_fields['CustomInformation']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CustomInformation and returns this instance
     * 
     * @param string $value CustomInformation
     * @return OffAmazonPaymentsService_Model_SellerOrderAttributes instance
     */
    public function withCustomInformation($value)
    {
        $this->setCustomInformation($value);
        return $this;
    }


    /**
     * Checks if CustomInformation is set
     * 
     * @return bool true if CustomInformation  is set
     */
    public function isSetCustomInformation()
    {
        return !is_null($this->_fields['CustomInformation']['FieldValue']);
    }




}
?>