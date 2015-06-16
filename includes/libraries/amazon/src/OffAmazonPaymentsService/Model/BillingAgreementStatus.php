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
 * OffAmazonPaymentsService_Model_BillingAgreementStatus
 * 
 * Properties:
 * <ul>
 * 
 * <li>State: string</li>
 * <li>LastUpdatedTimestamp: string</li>
 * <li>ReasonCode: string</li>
 * <li>ReasonDescription: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_BillingAgreementStatus extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_BillingAgreementStatus
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>State: string</li>
     * <li>LastUpdatedTimestamp: string</li>
     * <li>ReasonCode: string</li>
     * <li>ReasonDescription: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'State' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'LastUpdatedTimestamp' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'ReasonCode' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'ReasonDescription' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the State property.
     * 
     * @return string State
     */
    public function getState ()
    {
        return $this->_fields['State']['FieldValue'];
    }

    /**
     * Sets the value of the State property.
     * 
     * @param string State
     * @return this instance
     */
    public function setState ($value)
    {
        $this->_fields['State']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the State and returns this instance
     * 
     * @param string $value State
     * @return OffAmazonPaymentsService_Model_BillingAgreementStatus instance
     */
    public function withState ($value)
    {
        $this->setState($value);
        return $this;
    }

    /**
     * Checks if State is set
     * 
     * @return bool true if State  is set
     */
    public function isSetState ()
    {
        return ! is_null($this->_fields['State']['FieldValue']);
    }

    /**
     * Gets the value of the LastUpdatedTimestamp property.
     * 
     * @return string LastUpdatedTimestamp
     */
    public function getLastUpdatedTimestamp ()
    {
        return $this->_fields['LastUpdatedTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the LastUpdatedTimestamp property.
     * 
     * @param string LastUpdatedTimestamp
     * @return this instance
     */
    public function setLastUpdatedTimestamp ($value)
    {
        $this->_fields['LastUpdatedTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the LastUpdatedTimestamp and returns this instance
     * 
     * @param string $value LastUpdatedTimestamp
     * @return OffAmazonPaymentsService_Model_BillingAgreementStatus instance
     */
    public function withLastUpdatedTimestamp ($value)
    {
        $this->setLastUpdatedTimestamp($value);
        return $this;
    }

    /**
     * Checks if LastUpdatedTimestamp is set
     * 
     * @return bool true if LastUpdatedTimestamp  is set
     */
    public function isSetLastUpdatedTimestamp ()
    {
        return ! is_null($this->_fields['LastUpdatedTimestamp']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_BillingAgreementStatus instance
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

    /**
     * Gets the value of the ReasonDescription property.
     * 
     * @return string ReasonDescription
     */
    public function getReasonDescription ()
    {
        return $this->_fields['ReasonDescription']['FieldValue'];
    }

    /**
     * Sets the value of the ReasonDescription property.
     * 
     * @param string ReasonDescription
     * @return this instance
     */
    public function setReasonDescription ($value)
    {
        $this->_fields['ReasonDescription']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReasonDescription and returns this instance
     * 
     * @param string $value ReasonDescription
     * @return OffAmazonPaymentsService_Model_BillingAgreementStatus instance
     */
    public function withReasonDescription ($value)
    {
        $this->setReasonDescription($value);
        return $this;
    }

    /**
     * Checks if ReasonDescription is set
     * 
     * @return bool true if ReasonDescription  is set
     */
    public function isSetReasonDescription ()
    {
        return ! is_null($this->_fields['ReasonDescription']['FieldValue']);
    }
}
?>