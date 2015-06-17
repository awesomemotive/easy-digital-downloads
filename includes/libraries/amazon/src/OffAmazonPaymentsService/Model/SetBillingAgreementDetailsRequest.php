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
 * OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonBillingAgreementId: string</li>
 * <li>BillingAgreementAttributes: OffAmazonPaymentsService_Model_BillingAgreementAttributes</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonBillingAgreementId: string</li>
     * <li>BillingAgreementAttributes: OffAmazonPaymentsService_Model_BillingAgreementAttributes</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'SellerId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'AmazonBillingAgreementId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'BillingAgreementAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAgreementAttributes'
            )
        )
        ;
        parent::__construct($data);
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
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest instance
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
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest instance
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
     * Gets the value of the BillingAgreementAttributes.
     * 
     * @return BillingAgreementAttributes BillingAgreementAttributes
     */
    public function getBillingAgreementAttributes ()
    {
        return $this->_fields['BillingAgreementAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementAttributes.
     * 
     * @param BillingAgreementAttributes BillingAgreementAttributes
     * @return void
     */
    public function setBillingAgreementAttributes ($value)
    {
        $this->_fields['BillingAgreementAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementAttributes  and returns this instance
     * 
     * @param BillingAgreementAttributes $value BillingAgreementAttributes
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest instance
     */
    public function withBillingAgreementAttributes ($value)
    {
        $this->setBillingAgreementAttributes($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementAttributes  is set
     * 
     * @return bool true if BillingAgreementAttributes property is set
     */
    public function isSetBillingAgreementAttributes ()
    {
        return ! is_null($this->_fields['BillingAgreementAttributes']['FieldValue']);
    }
}
?>