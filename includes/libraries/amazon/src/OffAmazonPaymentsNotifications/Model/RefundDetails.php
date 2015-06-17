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

require_once 'OffAmazonPayments/Model.php';  

/**
 * OffAmazonPaymentsNotifications_Model_RefundDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonRefundId: string</li>
 * <li>RefundReferenceId: string</li>
 * <li>RefundType: string</li>
 * <li>RefundAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>FeeRefunded: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>CreationTimestamp: string</li>
 * <li>RefundStatus: OffAmazonPaymentsNotifications_Model_Status</li>
 * <li>ProviderCreditReversalSummaryList: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_RefundDetails extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_RefundDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonRefundId: string</li>
     * <li>RefundReferenceId: string</li>
     * <li>RefundType: string</li>
     * <li>RefundAmount: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>FeeRefunded: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>CreationTimestamp: string</li>
     * <li>RefundStatus: OffAmazonPaymentsNotifications_Model_Status</li>
     * <li>ProviderCreditReversalSummaryList: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
            'AmazonRefundId' => array(
                 'FieldValue' => null, 
                 'FieldType' => 'string'
            ),
            'RefundReferenceId' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'RefundType' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'RefundAmount' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            'FeeRefunded' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            'CreationTimestamp' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'RefundStatus' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Status'
            ),
            'ProviderCreditReversalSummaryList' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_ProviderCreditReversalSummaryList'
            ),
            'SoftDescriptor' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            )
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
        return $this->fields['AmazonRefundId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonRefundId property.
     * 
     * @param string $value AmazonRefundId
     * 
     * @return this instance
     */
    public function setAmazonRefundId($value) 
    {
        $this->fields['AmazonRefundId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonRefundId and returns this instance
     * 
     * @param string $value AmazonRefundId
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['AmazonRefundId']['FieldValue']);
    }

    /**
     * Gets the value of the RefundReferenceId property.
     * 
     * @return string RefundReferenceId
     */
    public function getRefundReferenceId() 
    {
        return $this->fields['RefundReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the RefundReferenceId property.
     * 
     * @param string $value RefundReferenceId
     * 
     * @return this instance
     */
    public function setRefundReferenceId($value) 
    {
        $this->fields['RefundReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the RefundReferenceId and returns this instance
     * 
     * @param string $value RefundReferenceId
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['RefundReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the RefundType property.
     * 
     * @return string RefundType
     */
    public function getRefundType() 
    {
        return $this->fields['RefundType']['FieldValue'];
    }

    /**
     * Sets the value of the RefundType property.
     * 
     * @param string $value RefundType
     * 
     * @return this instance
     */
    public function setRefundType($value) 
    {
        $this->fields['RefundType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the RefundType and returns this instance
     * 
     * @param string $value RefundType
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['RefundType']['FieldValue']);
    }

    /**
     * Gets the value of the RefundAmount.
     * 
     * @return Price RefundAmount
     */
    public function getRefundAmount() 
    {
        return $this->fields['RefundAmount']['FieldValue'];
    }

    /**
     * Sets the value of the RefundAmount.
     * 
     * @param Price $value RefundAmount
     * 
     * @return void
     */
    public function setRefundAmount($value) 
    {
        $this->fields['RefundAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundAmount  and returns this instance
     * 
     * @param Price $value RefundAmount
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['RefundAmount']['FieldValue']);

    }

    /**
     * Gets the value of the FeeRefunded.
     * 
     * @return Price FeeRefunded
     */
    public function getFeeRefunded() 
    {
        return $this->fields['FeeRefunded']['FieldValue'];
    }

    /**
     * Sets the value of the FeeRefunded.
     * 
     * @param Price $value FeeRefunded
     * 
     * @return void
     */
    public function setFeeRefunded($value) 
    {
        $this->fields['FeeRefunded']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the FeeRefunded  and returns this instance
     * 
     * @param Price $value FeeRefunded
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['FeeRefunded']['FieldValue']);

    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp() 
    {
        return $this->fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string $value CreationTimestamp
     * 
     * @return this instance
     */
    public function setCreationTimestamp($value) 
    {
        $this->fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the RefundStatus.
     * 
     * @return Status RefundStatus
     */
    public function getRefundStatus() 
    {
        return $this->fields['RefundStatus']['FieldValue'];
    }

    /**
     * Sets the value of the RefundStatus.
     * 
     * @param Status $value RefundStatus
     * 
     * @return void
     */
    public function setRefundStatus($value) 
    {
        $this->fields['RefundStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundStatus  and returns this instance
     * 
     * @param Status $value RefundStatus
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['RefundStatus']['FieldValue']);

    }

    /**
     * Gets the value of the SoftDescriptor property.
     * 
     * @return string SoftDescriptor
     */
    public function getSoftDescriptor() 
    {
        return $this->fields['SoftDescriptor']['FieldValue'];
    }

    /**
     * Sets the value of the SoftDescriptor property.
     * 
     * @param string $value SoftDescriptor
     * 
     * @return this instance
     */
    public function setSoftDescriptor($value) 
    {
        $this->fields['SoftDescriptor']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SoftDescriptor and returns this instance
     * 
     * @param string $value SoftDescriptor
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundDetails instance
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
        return !is_null($this->fields['SoftDescriptor']['FieldValue']);
    }
    
    /**
     * Gets the value of the ProviderCreditReversalSummaryList.
     *
     * @return ProviderCreditReversalSummaryList ProviderCreditReversalSummaryList
     */
    public function getProviderCreditReversalSummaryList()
    {
    	return $this->fields['ProviderCreditReversalSummaryList']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditReversalSummaryList.
     *
     * @param ProviderCreditReversalSummaryList ProviderCreditReversalSummaryList
     * @return void
     */
    public function setProviderCreditReversalSummaryList($value)
    {
    	$this->fields['ProviderCreditReversalSummaryList']['FieldValue'] = $value;
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
    	return !is_null($this->fields['ProviderCreditReversalSummaryList']['FieldValue']);
    
    }
}
?>