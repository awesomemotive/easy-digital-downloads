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
 * OffAmazonPaymentsService_Model_RefundDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonRefundId: string</li>
 * <li>RefundReferenceId: string</li>
 * <li>SellerRefundNote: string</li>
 * <li>RefundType: string</li>
 * <li>RefundAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>FeeRefunded: OffAmazonPaymentsService_Model_Price</li>
 * <li>CreationTimestamp: string</li>
 * <li>RefundStatus: OffAmazonPaymentsService_Model_Status</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_RefundDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_RefundDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonRefundId: string</li>
     * <li>RefundReferenceId: string</li>
     * <li>SellerRefundNote: string</li>
     * <li>RefundType: string</li>
     * <li>RefundAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>FeeRefunded: OffAmazonPaymentsService_Model_Price</li>
     * <li>CreationTimestamp: string</li>
     * <li>RefundStatus: OffAmazonPaymentsService_Model_Status</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AmazonRefundId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'RefundReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'SellerRefundNote' => array('FieldValue' => null, 'FieldType' => 'string'),
        'RefundType' => array('FieldValue' => null, 'FieldType' => 'string'),

        'RefundAmount' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),


        'FeeRefunded' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Price'),

        'CreationTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),

        'RefundStatus' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Status'),

        'SoftDescriptor' => array('FieldValue' => null, 'FieldType' => 'string'),
        
        'ProviderCreditReversalSummaryList' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_ProviderCreditReversalSummaryList'),		
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the AmazonRefundId property.
     * 
     * @return string AmazonRefundId
     */
    public function getAmazonRefundId() 
    {
        return $this->_fields['AmazonRefundId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonRefundId property.
     * 
     * @param string AmazonRefundId
     * @return this instance
     */
    public function setAmazonRefundId($value) 
    {
        $this->_fields['AmazonRefundId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonRefundId and returns this instance
     * 
     * @param string $value AmazonRefundId
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
     */
    public function withAmazonRefundId($value)
    {
        $this->setAmazonRefundId($value);
        return $this;
    }


    /**
     * Checks if AmazonRefundId is set
     * 
     * @return bool true if AmazonRefundId  is set
     */
    public function isSetAmazonRefundId()
    {
        return !is_null($this->_fields['AmazonRefundId']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
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
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
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
     * Gets the value of the RefundType property.
     * 
     * @return string RefundType
     */
    public function getRefundType() 
    {
        return $this->_fields['RefundType']['FieldValue'];
    }

    /**
     * Sets the value of the RefundType property.
     * 
     * @param string RefundType
     * @return this instance
     */
    public function setRefundType($value) 
    {
        $this->_fields['RefundType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the RefundType and returns this instance
     * 
     * @param string $value RefundType
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
     */
    public function withRefundType($value)
    {
        $this->setRefundType($value);
        return $this;
    }


    /**
     * Checks if RefundType is set
     * 
     * @return bool true if RefundType  is set
     */
    public function isSetRefundType()
    {
        return !is_null($this->_fields['RefundType']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
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
     * Gets the value of the FeeRefunded.
     * 
     * @return Price FeeRefunded
     */
    public function getFeeRefunded() 
    {
        return $this->_fields['FeeRefunded']['FieldValue'];
    }

    /**
     * Sets the value of the FeeRefunded.
     * 
     * @param Price FeeRefunded
     * @return void
     */
    public function setFeeRefunded($value) 
    {
        $this->_fields['FeeRefunded']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the FeeRefunded  and returns this instance
     * 
     * @param Price $value FeeRefunded
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
     */
    public function withFeeRefunded($value)
    {
        $this->setFeeRefunded($value);
        return $this;
    }


    /**
     * Checks if FeeRefunded  is set
     * 
     * @return bool true if FeeRefunded property is set
     */
    public function isSetFeeRefunded()
    {
        return !is_null($this->_fields['FeeRefunded']['FieldValue']);

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
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
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
     * Gets the value of the RefundStatus.
     * 
     * @return Status RefundStatus
     */
    public function getRefundStatus() 
    {
        return $this->_fields['RefundStatus']['FieldValue'];
    }

    /**
     * Sets the value of the RefundStatus.
     * 
     * @param Status RefundStatus
     * @return void
     */
    public function setRefundStatus($value) 
    {
        $this->_fields['RefundStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundStatus  and returns this instance
     * 
     * @param Status $value RefundStatus
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
     */
    public function withRefundStatus($value)
    {
        $this->setRefundStatus($value);
        return $this;
    }


    /**
     * Checks if RefundStatus  is set
     * 
     * @return bool true if RefundStatus property is set
     */
    public function isSetRefundStatus()
    {
        return !is_null($this->_fields['RefundStatus']['FieldValue']);

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
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
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
     * Gets the value of the ProviderCreditReversalSummaryList.
     *
     * @return ProviderCreditReversalSummaryList ProviderCreditReversalSummaryList
     */
    public function getProviderCreditReversalSummaryList()
    {
    	return $this->_fields['ProviderCreditReversalSummaryList']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditReversalSummaryList.
     *
     * @param ProviderCreditReversalSummaryList ProviderCreditReversalSummaryList
     * @return void
     */
    public function setProviderCreditReversalSummaryList($value)
    {
    	$this->_fields['ProviderCreditReversalSummaryList']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the ProviderCreditReversalSummaryList  and returns this instance
     *
     * @param ProviderCreditReversalSummaryList $value ProviderCreditReversalSummaryList
     * @return OffAmazonPaymentsService_Model_RefundDetails instance
     */
    public function withProviderCreditReversalSummaryList($value)
    {
    	$this->setProviderCreditReversalSummaryList($value);
    	return $this;
    }
    
    
    /**
     * Checks if ProviderCreditReversalSummaryList  is set
     *
     * @return bool true if ProviderCreditReversalSummaryList property is set
     */
    public function isSetProviderCreditReversalSummaryList()
    {
    	return !is_null($this->_fields['ProviderCreditReversalSummaryList']['FieldValue']);
    
    }

}
?>