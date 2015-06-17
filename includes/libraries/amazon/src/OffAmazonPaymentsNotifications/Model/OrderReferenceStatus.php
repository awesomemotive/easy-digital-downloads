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
 * OffAmazonPaymentsNotifications_Model_OrderReferenceStatus
 * 
 * Properties:
 * <ul>
 * 
 * <li>State: string</li>
 * <li>LastUpdateTimestamp: string</li>
 * <li>ReasonCode: string</li>
 * <li>ReasonDescription: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_OrderReferenceStatus extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_OrderReferenceStatus
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>State: string</li>
     * <li>LastUpdateTimestamp: string</li>
     * <li>ReasonCode: string</li>
     * <li>ReasonDescription: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'State' => array('FieldValue' => null, 'FieldType' => 'string'),
        'LastUpdateTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ReasonCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ReasonDescription' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the State property.
     * 
     * @return string State
     */
    public function getState() 
    {
        return $this->fields['State']['FieldValue'];
    }

    /**
     * Sets the value of the State property.
     * 
     * @param string State
     * @return this instance
     */
    public function setState($value) 
    {
        $this->fields['State']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the State and returns this instance
     * 
     * @param string $value State
     * @return OffAmazonPaymentsNotifications_Model_OrderReferenceStatus instance
     */
    public function withState($value)
    {
        $this->setState($value);
        return $this;
    }


    /**
     * Checks if State is set
     * 
     * @return bool true if State  is set
     */
    public function isSetState()
    {
        return !is_null($this->fields['State']['FieldValue']);
    }

    /**
     * Gets the value of the LastUpdateTimestamp property.
     * 
     * @return string LastUpdateTimestamp
     */
    public function getLastUpdateTimestamp() 
    {
        return $this->fields['LastUpdateTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the LastUpdateTimestamp property.
     * 
     * @param string LastUpdateTimestamp
     * @return this instance
     */
    public function setLastUpdateTimestamp($value) 
    {
        $this->fields['LastUpdateTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the LastUpdateTimestamp and returns this instance
     * 
     * @param string $value LastUpdateTimestamp
     * @return OffAmazonPaymentsNotifications_Model_OrderReferenceStatus instance
     */
    public function withLastUpdateTimestamp($value)
    {
        $this->setLastUpdateTimestamp($value);
        return $this;
    }


    /**
     * Checks if LastUpdateTimestamp is set
     * 
     * @return bool true if LastUpdateTimestamp  is set
     */
    public function isSetLastUpdateTimestamp()
    {
        return !is_null($this->fields['LastUpdateTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the ReasonCode property.
     * 
     * @return string ReasonCode
     */
    public function getReasonCode() 
    {
        return $this->fields['ReasonCode']['FieldValue'];
    }

    /**
     * Sets the value of the ReasonCode property.
     * 
     * @param string ReasonCode
     * @return this instance
     */
    public function setReasonCode($value) 
    {
        $this->fields['ReasonCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReasonCode and returns this instance
     * 
     * @param string $value ReasonCode
     * @return OffAmazonPaymentsNotifications_Model_OrderReferenceStatus instance
     */
    public function withReasonCode($value)
    {
        $this->setReasonCode($value);
        return $this;
    }


    /**
     * Checks if ReasonCode is set
     * 
     * @return bool true if ReasonCode  is set
     */
    public function isSetReasonCode()
    {
        return !is_null($this->fields['ReasonCode']['FieldValue']);
    }

    /**
     * Gets the value of the ReasonDescription property.
     * 
     * @return string ReasonDescription
     */
    public function getReasonDescription() 
    {
        return $this->fields['ReasonDescription']['FieldValue'];
    }

    /**
     * Sets the value of the ReasonDescription property.
     * 
     * @param string ReasonDescription
     * @return this instance
     */
    public function setReasonDescription($value) 
    {
        $this->fields['ReasonDescription']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReasonDescription and returns this instance
     * 
     * @param string $value ReasonDescription
     * @return OffAmazonPaymentsNotifications_Model_OrderReferenceStatus instance
     */
    public function withReasonDescription($value)
    {
        $this->setReasonDescription($value);
        return $this;
    }


    /**
     * Checks if ReasonDescription is set
     * 
     * @return bool true if ReasonDescription  is set
     */
    public function isSetReasonDescription()
    {
        return !is_null($this->fields['ReasonDescription']['FieldValue']);
    }
}
?>