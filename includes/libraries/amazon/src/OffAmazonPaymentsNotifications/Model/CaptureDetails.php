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
 * OffAmazonPaymentsNotifications_Model_CaptureDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonCaptureId: string</li>
 * <li>CaptureReferenceId: string</li>
 * <li>CaptureAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>RefundedAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>CaptureFee: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>IdList: OffAmazonPaymentsNotifications_Model_IdList</li>
 * <li>CreationTimestamp: string</li>
 * <li>CaptureStatus: OffAmazonPaymentsNotifications_Model_Status</li>
 * <li>SoftDescriptor: string</li>
 * <li>ProviderCreditSummaryList: OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_CaptureDetails 
    extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_CaptureDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonCaptureId: string</li>
     * <li>CaptureReferenceId: string</li>
     * <li>CaptureAmount: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>RefundedAmount: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>CaptureFee: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>IdList: OffAmazonPaymentsNotifications_Model_IdList</li>
     * <li>CreationTimestamp: string</li>
     * <li>CaptureStatus: OffAmazonPaymentsNotifications_Model_Status</li>
     * <li>ProviderCreditSummaryList: OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList</li>
 	 * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
            'AmazonCaptureId' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'CaptureReferenceId' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'CaptureAmount' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            'RefundedAmount' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            'CaptureFee' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            'IdList' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_IdList'
            ),
            'CreationTimestamp' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            ),
            'CaptureStatus' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Status'
            ),
            'ProviderCreditSummaryList' => array(
                'FieldValue' => null, 
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_ProviderCreditSummaryList'
            ),
            'SoftDescriptor' => array(
                'FieldValue' => null, 
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmazonCaptureId property.
     * 
     * @return string AmazonCaptureId
     */
    public function getAmazonCaptureId() 
    {
        return $this->fields['AmazonCaptureId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonCaptureId property.
     * 
     * @param string $value AmazonCaptureId
     * 
     * @return this instance
     */
    public function setAmazonCaptureId($value) 
    {
        $this->fields['AmazonCaptureId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonCaptureId and returns this instance
     * 
     * @param string $value AmazonCaptureId
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
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
        return !is_null($this->fields['AmazonCaptureId']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureReferenceId property.
     * 
     * @return string CaptureReferenceId
     */
    public function getCaptureReferenceId() 
    {
        return $this->fields['CaptureReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureReferenceId property.
     * 
     * @param string $value CaptureReferenceId
     * 
     * @return this instance
     */
    public function setCaptureReferenceId($value) 
    {
        $this->fields['CaptureReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CaptureReferenceId and returns this instance
     * 
     * @param string $value CaptureReferenceId
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
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
        return !is_null($this->fields['CaptureReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureAmount.
     * 
     * @return Price CaptureAmount
     */
    public function getCaptureAmount() 
    {
        return $this->fields['CaptureAmount']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureAmount.
     * 
     * @param Price $value CaptureAmount
     * 
     * @return void
     */
    public function setCaptureAmount($value) 
    {
        $this->fields['CaptureAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CaptureAmount  and returns this instance
     * 
     * @param Price $value CaptureAmount
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
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
        return !is_null($this->fields['CaptureAmount']['FieldValue']);

    }

    /**
     * Gets the value of the RefundedAmount.
     * 
     * @return Price RefundedAmount
     */
    public function getRefundedAmount() 
    {
        return $this->fields['RefundedAmount']['FieldValue'];
    }

    /**
     * Sets the value of the RefundedAmount.
     * 
     * @param Price $value RefundedAmount
     * 
     * @return void
     */
    public function setRefundedAmount($value) 
    {
        $this->fields['RefundedAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundedAmount  and returns this instance
     * 
     * @param Price $value RefundedAmount
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
     */
    public function withRefundedAmount($value)
    {
        $this->setRefundedAmount($value);
        return $this;
    }


    /**
     * Checks if RefundedAmount  is set
     * 
     * @return bool true if RefundedAmount property is set
     */
    public function isSetRefundedAmount()
    {
        return !is_null($this->fields['RefundedAmount']['FieldValue']);

    }

    /**
     * Gets the value of the CaptureFee.
     * 
     * @return Price CaptureFee
     */
    public function getCaptureFee() 
    {
        return $this->fields['CaptureFee']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureFee.
     * 
     * @param Price $value CaptureFee
     * 
     * @return void
     */
    public function setCaptureFee($value) 
    {
        $this->fields['CaptureFee']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CaptureFee  and returns this instance
     * 
     * @param Price $value CaptureFee
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
     */
    public function withCaptureFee($value)
    {
        $this->setCaptureFee($value);
        return $this;
    }


    /**
     * Checks if CaptureFee  is set
     * 
     * @return bool true if CaptureFee property is set
     */
    public function isSetCaptureFee()
    {
        return !is_null($this->fields['CaptureFee']['FieldValue']);

    }

    /**
     * Gets the value of the IdList.
     * 
     * @return IdList IdList
     */
    public function getIdList() 
    {
        return $this->fields['IdList']['FieldValue'];
    }

    /**
     * Sets the value of the IdList.
     * 
     * @param IdList $value IdList
     * 
     * @return void
     */
    public function setIdList($value) 
    {
        $this->fields['IdList']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the IdList  and returns this instance
     * 
     * @param IdList $value IdList
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
     */
    public function withIdList($value)
    {
        $this->setIdList($value);
        return $this;
    }


    /**
     * Checks if IdList  is set
     * 
     * @return bool true if IdList property is set
     */
    public function isSetIdList()
    {
        return !is_null($this->fields['IdList']['FieldValue']);

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
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
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
     * Gets the value of the CaptureStatus.
     * 
     * @return Status CaptureStatus
     */
    public function getCaptureStatus() 
    {
        return $this->fields['CaptureStatus']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureStatus.
     * 
     * @param Status $value CaptureStatus
     * 
     * @return void
     */
    public function setCaptureStatus($value) 
    {
        $this->fields['CaptureStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CaptureStatus  and returns this instance
     * 
     * @param Status $value CaptureStatus
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
     */
    public function withCaptureStatus($value)
    {
        $this->setCaptureStatus($value);
        return $this;
    }


    /**
     * Checks if CaptureStatus  is set
     * 
     * @return bool true if CaptureStatus property is set
     */
    public function isSetCaptureStatus()
    {
        return !is_null($this->fields['CaptureStatus']['FieldValue']);

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
     * @return OffAmazonPaymentsNotifications_Model_CaptureDetails instance
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
     * Gets the value of the ProviderCreditSummaryList.
     *
     * @return ProviderCreditSummaryList ProviderCreditSummaryList
     */
    public function getProviderCreditSummaryList()
    {
    	return $this->fields['ProviderCreditSummaryList']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditSummaryList.
     *
     * @param ProviderCreditSummaryList ProviderCreditSummaryList
     * @return void
     */
    public function setProviderCreditSummaryList($value)
    {
    	$this->fields['ProviderCreditSummaryList']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the ProviderCreditSummaryList  and returns this instance
     *
     * @param ProviderCreditSummaryList $value ProviderCreditSummaryList
     * @return OffAmazonPaymentsService_Model_CaptureDetails instance
     */
    public function withProviderCreditSummaryList($value)
    {
    	$this->setProviderCreditSummaryList($value);
    	return $this;
    }
    
    
    /**
     * Checks if ProviderCreditSummaryList  is set
     *
     * @return bool true if ProviderCreditSummaryList property is set
     */
    public function isSetProviderCreditSummaryList()
    {
    	return !is_null($this->fields['ProviderCreditSummaryList']['FieldValue']);
    
    }
}
?>