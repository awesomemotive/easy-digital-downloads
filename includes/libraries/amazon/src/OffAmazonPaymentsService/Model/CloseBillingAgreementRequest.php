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
 * OffAmazonPaymentsService_Model_CloseBillingAgreementRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonBillingAgreementId: string</li>
 * <li>SellerId: string</li>
 * <li>ClosureReason: string</li>
 * <li>ReasonCode: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_CloseBillingAgreementRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_CloseBillingAgreementRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonBillingAgreementId: string</li>
     * <li>SellerId: string</li>
     * <li>ClosureReason: string</li>
     * <li>ReasonCode: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'AmazonBillingAgreementId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'ClosureReason' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'ReasonCode' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmazonBillingAgreementId property.
     * 
     * @return string AmazonBillingAgreementId
     */
    public function getAmazonBillingAgreementId ()
    {
        return $this->_fields['AmazonBillingAgreementId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonBillingAgreementId property.
     * 
     * @param string AmazonBillingAgreementId
     * @return this instance
     */
    public function setAmazonBillingAgreementId ($value)
    {
        $this->_fields['AmazonBillingAgreementId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonBillingAgreementId and returns this instance
     * 
     * @param string $value AmazonBillingAgreementId
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementRequest instance
     */
    public function withAmazonBillingAgreementId ($value)
    {
        $this->setAmazonBillingAgreementId($value);
        return $this;
    }

    /**
     * Checks if AmazonBillingAgreementId is set
     * 
     * @return bool true if AmazonBillingAgreementId  is set
     */
    public function isSetAmazonBillingAgreementId ()
    {
        return ! is_null($this->_fields['AmazonBillingAgreementId']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementRequest instance
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
     * Gets the value of the ClosureReason property.
     * 
     * @return string ClosureReason
     */
    public function getClosureReason ()
    {
        return $this->_fields['ClosureReason']['FieldValue'];
    }

    /**
     * Sets the value of the ClosureReason property.
     * 
     * @param string ClosureReason
     * @return this instance
     */
    public function setClosureReason ($value)
    {
        $this->_fields['ClosureReason']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ClosureReason and returns this instance
     * 
     * @param string $value ClosureReason
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementRequest instance
     */
    public function withClosureReason ($value)
    {
        $this->setClosureReason($value);
        return $this;
    }

    /**
     * Checks if ClosureReason is set
     * 
     * @return bool true if ClosureReason  is set
     */
    public function isSetClosureReason ()
    {
        return ! is_null($this->_fields['ClosureReason']['FieldValue']);
    }

    /**
     * Gets the value of the ReasonCode property.
     * 
     * @return string ReasonCode
     */
    public function getReasonCode ()
    {
        return $this->_fields['ReasonCode']['FieldValue'];
    }

    /**
     * Sets the value of the ReasonCode property.
     * 
     * @param string ReasonCode
     * @return this instance
     */
    public function setReasonCode ($value)
    {
        $this->_fields['ReasonCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReasonCode and returns this instance
     * 
     * @param string $value ReasonCode
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementRequest instance
     */
    public function withReasonCode ($value)
    {
        $this->setReasonCode($value);
        return $this;
    }

    /**
     * Checks if ReasonCode is set
     * 
     * @return bool true if ReasonCode  is set
     */
    public function isSetReasonCode ()
    {
        return ! is_null($this->_fields['ReasonCode']['FieldValue']);
    }
}
?>