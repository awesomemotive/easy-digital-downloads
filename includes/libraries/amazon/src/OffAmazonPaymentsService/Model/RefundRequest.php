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
 * OffAmazonPaymentsService_Model_RefundRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonCaptureId: string</li>
 * <li>RefundReferenceId: string</li>
 * <li>RefundAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>SellerRefundNote: string</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_RefundRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_RefundRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonCaptureId: string</li>
     * <li>RefundReferenceId: string</li>
     * <li>RefundAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>SellerRefundNote: string</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonCaptureId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'RefundReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'RefundAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'SellerRefundNote' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SoftDescriptor' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderCreditReversalList' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_ProviderCreditReversalList'),
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
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
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
     * Gets the value of the AmazonCaptureId property.
     * 
     * @return string AmazonCaptureId
     */
    public function getAmazonCaptureId() 
    {
        return $this->_fields['AmazonCaptureId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonCaptureId property.
     * 
     * @param string AmazonCaptureId
     * @return this instance
     */
    public function setAmazonCaptureId($value) 
    {
        $this->_fields['AmazonCaptureId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonCaptureId and returns this instance
     * 
     * @param string $value AmazonCaptureId
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
     */
    public function withAmazonCaptureId($value)
    {
        $this->setAmazonCaptureId($value);
        return $this;
    }


    /**
     * Checks if AmazonCaptureId is set
     * 
     * @return bool true if AmazonCaptureId  is set
     */
    public function isSetAmazonCaptureId()
    {
        return !is_null($this->_fields['AmazonCaptureId']['FieldValue']);
    }

    /**
     * Gets the value of the RefundReferenceId property.
     * 
     * @return string RefundReferenceId
     */
    public function getRefundReferenceId() 
    {
        return $this->_fields['RefundReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the RefundReferenceId property.
     * 
     * @param string RefundReferenceId
     * @return this instance
     */
    public function setRefundReferenceId($value) 
    {
        $this->_fields['RefundReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the RefundReferenceId and returns this instance
     * 
     * @param string $value RefundReferenceId
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
     */
    public function withRefundReferenceId($value)
    {
        $this->setRefundReferenceId($value);
        return $this;
    }


    /**
     * Checks if RefundReferenceId is set
     * 
     * @return bool true if RefundReferenceId  is set
     */
    public function isSetRefundReferenceId()
    {
        return !is_null($this->_fields['RefundReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the RefundAmount.
     * 
     * @return Price RefundAmount
     */
    public function getRefundAmount() 
    {
        return $this->_fields['RefundAmount']['FieldValue'];
    }

    /**
     * Sets the value of the RefundAmount.
     * 
     * @param Price RefundAmount
     * @return void
     */
    public function setRefundAmount($value) 
    {
        $this->_fields['RefundAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundAmount  and returns this instance
     * 
     * @param Price $value RefundAmount
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
     */
    public function withRefundAmount($value)
    {
        $this->setRefundAmount($value);
        return $this;
    }


    /**
     * Checks if RefundAmount  is set
     * 
     * @return bool true if RefundAmount property is set
     */
    public function isSetRefundAmount()
    {
        return !is_null($this->_fields['RefundAmount']['FieldValue']);

    }

    /**
     * Gets the value of the SellerRefundNote property.
     * 
     * @return string SellerRefundNote
     */
    public function getSellerRefundNote() 
    {
        return $this->_fields['SellerRefundNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerRefundNote property.
     * 
     * @param string SellerRefundNote
     * @return this instance
     */
    public function setSellerRefundNote($value) 
    {
        $this->_fields['SellerRefundNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerRefundNote and returns this instance
     * 
     * @param string $value SellerRefundNote
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
     */
    public function withSellerRefundNote($value)
    {
        $this->setSellerRefundNote($value);
        return $this;
    }


    /**
     * Checks if SellerRefundNote is set
     * 
     * @return bool true if SellerRefundNote  is set
     */
    public function isSetSellerRefundNote()
    {
        return !is_null($this->_fields['SellerRefundNote']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
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
     * Gets the value of the ProviderCreditReversalList.
     *
     * @return ProviderCreditReversalList ProviderCreditReversalList
     */
    public function getProviderCreditReversalList()
    {
    	return $this->_fields['ProviderCreditReversalList']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditReversalList.
     *
     * @param ProviderCreditReversalList ProviderCreditReversalList
     * @return void
     */
    public function setProviderCreditReversalList($value)
    {
    	$this->_fields['ProviderCreditReversalList']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the ProviderCreditReversalList  and returns this instance
     *
     * @param ProviderCreditReversalList $value ProviderCreditReversalList
     * @return OffAmazonPaymentsService_Model_RefundRequest instance
     */
    public function withProviderCreditReversalList($value)
    {
    	$this->setProviderCreditReversalList($value);
    	return $this;
    }
    
    
    /**
     * Checks if ProviderCreditReversalList  is set
     *
     * @return bool true if ProviderCreditReversalList property is set
     */
    public function isSetProviderCreditReversalList()
    {
    	return !is_null($this->_fields['ProviderCreditReversalList']['FieldValue']);
    
    }

}
?>