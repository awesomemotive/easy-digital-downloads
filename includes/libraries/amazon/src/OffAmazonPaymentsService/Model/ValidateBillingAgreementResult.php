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
 * OffAmazonPaymentsService_Model_ValidateBillingAgreementResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>ValidationResult: string</li>
 * <li>FailureReasonCode: string</li>
 * <li>BillingAgreementStatus: OffAmazonPaymentsService_Model_BillingAgreementStatus</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_ValidateBillingAgreementResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ValidateBillingAgreementResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ValidationResult: string</li>
     * <li>FailureReasonCode: string</li>
     * <li>BillingAgreementStatus: OffAmazonPaymentsService_Model_BillingAgreementStatus</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'ValidationResult' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'FailureReasonCode' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'BillingAgreementStatus' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAgreementStatus'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the ValidationResult property.
     * 
     * @return string ValidationResult
     */
    public function getValidationResult ()
    {
        return $this->_fields['ValidationResult']['FieldValue'];
    }

    /**
     * Sets the value of the ValidationResult property.
     * 
     * @param string ValidationResult
     * @return this instance
     */
    public function setValidationResult ($value)
    {
        $this->_fields['ValidationResult']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ValidationResult and returns this instance
     * 
     * @param string $value ValidationResult
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResult instance
     */
    public function withValidationResult ($value)
    {
        $this->setValidationResult($value);
        return $this;
    }

    /**
     * Checks if ValidationResult is set
     * 
     * @return bool true if ValidationResult  is set
     */
    public function isSetValidationResult ()
    {
        return ! is_null($this->_fields['ValidationResult']['FieldValue']);
    }

    /**
     * Gets the value of the FailureReasonCode property.
     * 
     * @return string FailureReasonCode
     */
    public function getFailureReasonCode ()
    {
        return $this->_fields['FailureReasonCode']['FieldValue'];
    }

    /**
     * Sets the value of the FailureReasonCode property.
     * 
     * @param string FailureReasonCode
     * @return this instance
     */
    public function setFailureReasonCode ($value)
    {
        $this->_fields['FailureReasonCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the FailureReasonCode and returns this instance
     * 
     * @param string $value FailureReasonCode
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResult instance
     */
    public function withFailureReasonCode ($value)
    {
        $this->setFailureReasonCode($value);
        return $this;
    }

    /**
     * Checks if FailureReasonCode is set
     * 
     * @return bool true if FailureReasonCode  is set
     */
    public function isSetFailureReasonCode ()
    {
        return ! is_null($this->_fields['FailureReasonCode']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementStatus.
     * 
     * @return BillingAgreementStatus BillingAgreementStatus
     */
    public function getBillingAgreementStatus ()
    {
        return $this->_fields['BillingAgreementStatus']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementStatus.
     * 
     * @param BillingAgreementStatus BillingAgreementStatus
     * @return void
     */
    public function setBillingAgreementStatus ($value)
    {
        $this->_fields['BillingAgreementStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementStatus  and returns this instance
     * 
     * @param BillingAgreementStatus $value BillingAgreementStatus
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResult instance
     */
    public function withBillingAgreementStatus ($value)
    {
        $this->setBillingAgreementStatus($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementStatus  is set
     * 
     * @return bool true if BillingAgreementStatus property is set
     */
    public function isSetBillingAgreementStatus ()
    {
        return ! is_null($this->_fields['BillingAgreementStatus']['FieldValue']);
    }
}
?>