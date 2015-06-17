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
 * OffAmazonPaymentsService_Model_AuthorizeRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonOrderReferenceId: string</li>
 * <li>AuthorizationReferenceId: string</li>
 * <li>AuthorizationAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>SellerAuthorizationNote: string</li>
 * <li>OrderItemCategories: OffAmazonPaymentsService_Model_OrderItemCategories</li>
 * <li>TransactionTimeout: int</li>
 * <li>CaptureNow: bool</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_AuthorizeRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_AuthorizeRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonOrderReferenceId: string</li>
     * <li>AuthorizationReferenceId: string</li>
     * <li>AuthorizationAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>SellerAuthorizationNote: string</li>
     * <li>OrderItemCategories: OffAmazonPaymentsService_Model_OrderItemCategories</li>
     * <li>TransactionTimeout: int</li>
     * <li>CaptureNow: bool</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonOrderReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AuthorizationReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'AuthorizationAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'SellerAuthorizationNote' => array('FieldValue' => null, 'FieldType' => 'string'),

        'OrderItemCategories' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderItemCategories'),

        'TransactionTimeout' => array('FieldValue' => null, 'FieldType' => 'int'),
        'CaptureNow' => array('FieldValue' => null, 'FieldType' => 'bool'),
        'SoftDescriptor' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderCreditList' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_ProviderCreditList'),
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
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
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
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
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
     * Gets the value of the AuthorizationReferenceId property.
     * 
     * @return string AuthorizationReferenceId
     */
    public function getAuthorizationReferenceId() 
    {
        return $this->_fields['AuthorizationReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationReferenceId property.
     * 
     * @param string AuthorizationReferenceId
     * @return this instance
     */
    public function setAuthorizationReferenceId($value) 
    {
        $this->_fields['AuthorizationReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AuthorizationReferenceId and returns this instance
     * 
     * @param string $value AuthorizationReferenceId
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withAuthorizationReferenceId($value)
    {
        $this->setAuthorizationReferenceId($value);
        return $this;
    }


    /**
     * Checks if AuthorizationReferenceId is set
     * 
     * @return bool true if AuthorizationReferenceId  is set
     */
    public function isSetAuthorizationReferenceId()
    {
        return !is_null($this->_fields['AuthorizationReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the AuthorizationAmount.
     * 
     * @return Price AuthorizationAmount
     */
    public function getAuthorizationAmount() 
    {
        return $this->_fields['AuthorizationAmount']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationAmount.
     * 
     * @param Price AuthorizationAmount
     * @return void
     */
    public function setAuthorizationAmount($value) 
    {
        $this->_fields['AuthorizationAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationAmount  and returns this instance
     * 
     * @param Price $value AuthorizationAmount
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withAuthorizationAmount($value)
    {
        $this->setAuthorizationAmount($value);
        return $this;
    }


    /**
     * Checks if AuthorizationAmount  is set
     * 
     * @return bool true if AuthorizationAmount property is set
     */
    public function isSetAuthorizationAmount()
    {
        return !is_null($this->_fields['AuthorizationAmount']['FieldValue']);

    }

    /**
     * Gets the value of the SellerAuthorizationNote property.
     * 
     * @return string SellerAuthorizationNote
     */
    public function getSellerAuthorizationNote() 
    {
        return $this->_fields['SellerAuthorizationNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerAuthorizationNote property.
     * 
     * @param string SellerAuthorizationNote
     * @return this instance
     */
    public function setSellerAuthorizationNote($value) 
    {
        $this->_fields['SellerAuthorizationNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerAuthorizationNote and returns this instance
     * 
     * @param string $value SellerAuthorizationNote
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withSellerAuthorizationNote($value)
    {
        $this->setSellerAuthorizationNote($value);
        return $this;
    }


    /**
     * Checks if SellerAuthorizationNote is set
     * 
     * @return bool true if SellerAuthorizationNote  is set
     */
    public function isSetSellerAuthorizationNote()
    {
        return !is_null($this->_fields['SellerAuthorizationNote']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
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
     * Gets the value of the TransactionTimeout property.
     * 
     * @return int TransactionTimeout
     */
    public function getTransactionTimeout() 
    {
        return $this->_fields['TransactionTimeout']['FieldValue'];
    }

    /**
     * Sets the value of the TransactionTimeout property.
     * 
     * @param int TransactionTimeout
     * @return this instance
     */
    public function setTransactionTimeout($value) 
    {
        $this->_fields['TransactionTimeout']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TransactionTimeout and returns this instance
     * 
     * @param int $value TransactionTimeout
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withTransactionTimeout($value)
    {
        $this->setTransactionTimeout($value);
        return $this;
    }


    /**
     * Checks if TransactionTimeout is set
     * 
     * @return bool true if TransactionTimeout  is set
     */
    public function isSetTransactionTimeout()
    {
        return !is_null($this->_fields['TransactionTimeout']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureNow property.
     * 
     * @return bool CaptureNow
     */
    public function getCaptureNow() 
    {
        return $this->_fields['CaptureNow']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureNow property.
     * 
     * @param bool CaptureNow
     * @return this instance
     */
    public function setCaptureNow($value) 
    {
        $this->_fields['CaptureNow']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CaptureNow and returns this instance
     * 
     * @param bool $value CaptureNow
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withCaptureNow($value)
    {
        $this->setCaptureNow($value);
        return $this;
    }


    /**
     * Checks if CaptureNow is set
     * 
     * @return bool true if CaptureNow  is set
     */
    public function isSetCaptureNow()
    {
        return !is_null($this->_fields['CaptureNow']['FieldValue']);
    }

    /**
     * Gets the value of the SoftDescriptor property.
     * 
     * @return string SoftDescriptor
     */
    public function getSoftDescriptor() 
    {
        return $this->_fields['SoftDescriptor']['FieldValue'];
    }

    /**
     * Sets the value of the SoftDescriptor property.
     * 
     * @param string SoftDescriptor
     * @return this instance
     */
    public function setSoftDescriptor($value) 
    {
        $this->_fields['SoftDescriptor']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SoftDescriptor and returns this instance
     * 
     * @param string $value SoftDescriptor
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withSoftDescriptor($value)
    {
        $this->setSoftDescriptor($value);
        return $this;
    }


    /**
     * Checks if SoftDescriptor is set
     * 
     * @return bool true if SoftDescriptor  is set
     */
    public function isSetSoftDescriptor()
    {
        return !is_null($this->_fields['SoftDescriptor']['FieldValue']);
    }
    
    /**
     * Gets the value of the ProviderCreditList.
     *
     * @return ProviderCreditList ProviderCreditList
     */
    public function getProviderCreditList()
    {
    	return $this->_fields['ProviderCreditList']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditList.
     *
     * @param ProviderCreditList ProviderCreditList
     * @return void
     */
    public function setProviderCreditList($value)
    {
    	$this->_fields['ProviderCreditList']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the ProviderCreditList  and returns this instance
     *
     * @param ProviderCreditList $value ProviderCreditList
     * @return OffAmazonPaymentsService_Model_AuthorizeRequest instance
     */
    public function withProviderCreditList($value)
    {
    	$this->setProviderCreditList($value);
    	return $this;
    }
    
    
    /**
     * Checks if ProviderCreditList  is set
     *
     * @return bool true if ProviderCreditList property is set
     */
    public function isSetProviderCreditList()
    {
    	return !is_null($this->_fields['ProviderCreditList']['FieldValue']);
    
    }
}
?>