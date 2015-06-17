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
 * OffAmazonPaymentsService_Model_CaptureRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonAuthorizationId: string</li>
 * <li>CaptureReferenceId: string</li>
 * <li>CaptureAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>SellerCaptureNote: string</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_CaptureRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_CaptureRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonAuthorizationId: string</li>
     * <li>CaptureReferenceId: string</li>
     * <li>CaptureAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>SellerCaptureNote: string</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonAuthorizationId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CaptureReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'CaptureAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'SellerCaptureNote' => array('FieldValue' => null, 'FieldType' => 'string'),
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
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
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
     * Gets the value of the AmazonAuthorizationId property.
     * 
     * @return string AmazonAuthorizationId
     */
    public function getAmazonAuthorizationId() 
    {
        return $this->_fields['AmazonAuthorizationId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonAuthorizationId property.
     * 
     * @param string AmazonAuthorizationId
     * @return this instance
     */
    public function setAmazonAuthorizationId($value) 
    {
        $this->_fields['AmazonAuthorizationId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonAuthorizationId and returns this instance
     * 
     * @param string $value AmazonAuthorizationId
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
     */
    public function withAmazonAuthorizationId($value)
    {
        $this->setAmazonAuthorizationId($value);
        return $this;
    }


    /**
     * Checks if AmazonAuthorizationId is set
     * 
     * @return bool true if AmazonAuthorizationId  is set
     */
    public function isSetAmazonAuthorizationId()
    {
        return !is_null($this->_fields['AmazonAuthorizationId']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureReferenceId property.
     * 
     * @return string CaptureReferenceId
     */
    public function getCaptureReferenceId() 
    {
        return $this->_fields['CaptureReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureReferenceId property.
     * 
     * @param string CaptureReferenceId
     * @return this instance
     */
    public function setCaptureReferenceId($value) 
    {
        $this->_fields['CaptureReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CaptureReferenceId and returns this instance
     * 
     * @param string $value CaptureReferenceId
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
     */
    public function withCaptureReferenceId($value)
    {
        $this->setCaptureReferenceId($value);
        return $this;
    }


    /**
     * Checks if CaptureReferenceId is set
     * 
     * @return bool true if CaptureReferenceId  is set
     */
    public function isSetCaptureReferenceId()
    {
        return !is_null($this->_fields['CaptureReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureAmount.
     * 
     * @return Price CaptureAmount
     */
    public function getCaptureAmount() 
    {
        return $this->_fields['CaptureAmount']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureAmount.
     * 
     * @param Price CaptureAmount
     * @return void
     */
    public function setCaptureAmount($value) 
    {
        $this->_fields['CaptureAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CaptureAmount  and returns this instance
     * 
     * @param Price $value CaptureAmount
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
     */
    public function withCaptureAmount($value)
    {
        $this->setCaptureAmount($value);
        return $this;
    }


    /**
     * Checks if CaptureAmount  is set
     * 
     * @return bool true if CaptureAmount property is set
     */
    public function isSetCaptureAmount()
    {
        return !is_null($this->_fields['CaptureAmount']['FieldValue']);

    }

    /**
     * Gets the value of the SellerCaptureNote property.
     * 
     * @return string SellerCaptureNote
     */
    public function getSellerCaptureNote() 
    {
        return $this->_fields['SellerCaptureNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerCaptureNote property.
     * 
     * @param string SellerCaptureNote
     * @return this instance
     */
    public function setSellerCaptureNote($value) 
    {
        $this->_fields['SellerCaptureNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerCaptureNote and returns this instance
     * 
     * @param string $value SellerCaptureNote
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
     */
    public function withSellerCaptureNote($value)
    {
        $this->setSellerCaptureNote($value);
        return $this;
    }


    /**
     * Checks if SellerCaptureNote is set
     * 
     * @return bool true if SellerCaptureNote  is set
     */
    public function isSetSellerCaptureNote()
    {
        return !is_null($this->_fields['SellerCaptureNote']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
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
     * @return OffAmazonPaymentsService_Model_CaptureRequest instance
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