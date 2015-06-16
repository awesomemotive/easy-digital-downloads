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
 * OffAmazonPaymentsService_Model_OrderReferenceAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderTotal: OffAmazonPaymentsService_Model_OrderTotal</li>
 * <li>SellerNote: string</li>
 * <li>PlatformId: string</li>
 * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_OrderReferenceAttributes extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_OrderReferenceAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>OrderTotal: OffAmazonPaymentsService_Model_OrderTotal</li>
     * <li>SellerNote: string</li>
     * <li>PlatformId: string</li>
     * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'OrderTotal' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderTotal'),

        'PlatformId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'SellerNote' => array('FieldValue' => null, 'FieldType' => 'string'),

        'SellerOrderAttributes' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_SellerOrderAttributes'),

        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the OrderTotal.
     * 
     * @return OrderTotal OrderTotal
     */
    public function getOrderTotal() 
    {
        return $this->_fields['OrderTotal']['FieldValue'];
    }

    /**
     * Sets the value of the OrderTotal.
     * 
     * @param OrderTotal OrderTotal
     * @return void
     */
    public function setOrderTotal($value) 
    {
        $this->_fields['OrderTotal']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderTotal  and returns this instance
     * 
     * @param OrderTotal $value OrderTotal
     * @return OffAmazonPaymentsService_Model_OrderReferenceAttributes instance
     */
    public function withOrderTotal($value)
    {
        $this->setOrderTotal($value);
        return $this;
    }


    /**
     * Checks if OrderTotal  is set
     * 
     * @return bool true if OrderTotal property is set
     */
    public function isSetOrderTotal()
    {
        return !is_null($this->_fields['OrderTotal']['FieldValue']);

    }

    /**
     * Gets the value of the PlatformId property.
     * 
     * @return string PlatformId
     */
    public function getPlatformId() 
    {
        return $this->_fields['PlatformId']['FieldValue'];
    }

    /**
     * Sets the value of the PlatformId property.
     * 
     * @param string PlatformId
     * @return this instance
     */
    public function setPlatformId($value) 
    {
        $this->_fields['PlatformId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the PlatformId and returns this instance
     * 
     * @param string $value PlatformId
     * @return OffAmazonPaymentsService_Model_OrderReferenceAttributes instance
     */
    public function withPlatformId($value)
    {
        $this->setPlatformId($value);
        return $this;
    }
    
    /**
     * Checks if PlatformId is set
     *
     * @return bool true if PlatformId  is set
     */
    public function isSetPlatformId()
    {
    	return !is_null($this->_fields['PlatformId']['FieldValue']);
    }

    /**
     * Gets the value of the SellerNote property.
     * 
     * @return string SellerNote
     */
    public function getSellerNote() 
    {
        return $this->_fields['SellerNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerNote property.
     * 
     * @param string SellerNote
     * @return this instance
     */
    public function setSellerNote($value) 
    {
        $this->_fields['SellerNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerNote and returns this instance
     * 
     * @param string $value SellerNote
     * @return OffAmazonPaymentsService_Model_OrderReferenceAttributes instance
     */
    public function withSellerNote($value)
    {
        $this->setSellerNote($value);
        return $this;
    }


    /**
     * Checks if SellerNote is set
     * 
     * @return bool true if SellerNote  is set
     */
    public function isSetSellerNote()
    {
        return !is_null($this->_fields['SellerNote']['FieldValue']);
    }

    /**
     * Gets the value of the SellerOrderAttributes.
     * 
     * @return SellerOrderAttributes SellerOrderAttributes
     */
    public function getSellerOrderAttributes() 
    {
        return $this->_fields['SellerOrderAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerOrderAttributes.
     * 
     * @param SellerOrderAttributes SellerOrderAttributes
     * @return void
     */
    public function setSellerOrderAttributes($value) 
    {
        $this->_fields['SellerOrderAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerOrderAttributes  and returns this instance
     * 
     * @param SellerOrderAttributes $value SellerOrderAttributes
     * @return OffAmazonPaymentsService_Model_OrderReferenceAttributes instance
     */
    public function withSellerOrderAttributes($value)
    {
        $this->setSellerOrderAttributes($value);
        return $this;
    }


    /**
     * Checks if SellerOrderAttributes  is set
     * 
     * @return bool true if SellerOrderAttributes property is set
     */
    public function isSetSellerOrderAttributes()
    {
        return !is_null($this->_fields['SellerOrderAttributes']['FieldValue']);

    }




}
?>