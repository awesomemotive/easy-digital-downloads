<?php

/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
 *  @see OffAmazonPaymentsNotifications_Model
 */
require_once 'OffAmazonPayments/Model.php';  

    

/**
 * OffAmazonPaymentsNotifications_Model_ProviderCreditSummary
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderSellerId: string</li>
 * <li>ProviderCreditId: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_ProviderCreditSummary extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditSummary
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ProviderSellerId: string</li>
     * <li>ProviderCreditId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'ProviderSellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ProviderCreditId' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ProviderSellerId property.
     * 
     * @return string ProviderSellerId
     */
    public function getProviderSellerId() 
    {
        return $this->fields['ProviderSellerId']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderSellerId property.
     * 
     * @param string ProviderSellerId
     * @return this instance
     */
    public function setProviderSellerId($value) 
    {
        $this->fields['ProviderSellerId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ProviderSellerId and returns this instance
     * 
     * @param string $value ProviderSellerId
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditSummary instance
     */
    public function withProviderSellerId($value)
    {
        $this->setProviderSellerId($value);
        return $this;
    }


    /**
     * Checks if ProviderSellerId is set
     * 
     * @return bool true if ProviderSellerId  is set
     */
    public function isSetProviderSellerId()
    {
        return !is_null($this->fields['ProviderSellerId']['FieldValue']);
    }

    /**
     * Gets the value of the ProviderCreditId property.
     * 
     * @return string ProviderCreditId
     */
    public function getProviderCreditId() 
    {
        return $this->fields['ProviderCreditId']['FieldValue'];
    }

    /**
     * Sets the value of the ProviderCreditId property.
     * 
     * @param string ProviderCreditId
     * @return this instance
     */
    public function setProviderCreditId($value) 
    {
        $this->fields['ProviderCreditId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ProviderCreditId and returns this instance
     * 
     * @param string $value ProviderCreditId
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditSummary instance
     */
    public function withProviderCreditId($value)
    {
        $this->setProviderCreditId($value);
        return $this;
    }


    /**
     * Checks if ProviderCreditId is set
     * 
     * @return bool true if ProviderCreditId  is set
     */
    public function isSetProviderCreditId()
    {
        return !is_null($this->fields['ProviderCreditId']['FieldValue']);
    }

}