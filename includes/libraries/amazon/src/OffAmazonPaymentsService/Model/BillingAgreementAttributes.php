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
 * OffAmazonPaymentsService_Model_BillingAgreementAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>PlatformId: string</li>
 * <li>SellerNote: string</li>
 * <li>SellerBillingAgreementAttributes: OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_BillingAgreementAttributes extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_BillingAgreementAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>PlatformId: string</li>
     * <li>SellerNote: string</li>
     * <li>SellerBillingAgreementAttributes: OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'PlatformId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerNote' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'SellerBillingAgreementAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the PlatformId property.
     * 
     * @return string PlatformId
     */
    public function getPlatformId ()
    {
        return $this->_fields['PlatformId']['FieldValue'];
    }

    /**
     * Sets the value of the PlatformId property.
     * 
     * @param string PlatformId
     * @return this instance
     */
    public function setPlatformId ($value)
    {
        $this->_fields['PlatformId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the PlatformId and returns this instance
     * 
     * @param string $value PlatformId
     * @return OffAmazonPaymentsService_Model_BillingAgreementAttributes instance
     */
    public function withPlatformId ($value)
    {
        $this->setPlatformId($value);
        return $this;
    }

    /**
     * Checks if PlatformId is set
     * 
     * @return bool true if PlatformId  is set
     */
    public function isSetPlatformId ()
    {
        return ! is_null($this->_fields['PlatformId']['FieldValue']);
    }

    /**
     * Gets the value of the SellerNote property.
     * 
     * @return string SellerNote
     */
    public function getSellerNote ()
    {
        return $this->_fields['SellerNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerNote property.
     * 
     * @param string SellerNote
     * @return this instance
     */
    public function setSellerNote ($value)
    {
        $this->_fields['SellerNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerNote and returns this instance
     * 
     * @param string $value SellerNote
     * @return OffAmazonPaymentsService_Model_BillingAgreementAttributes instance
     */
    public function withSellerNote ($value)
    {
        $this->setSellerNote($value);
        return $this;
    }

    /**
     * Checks if SellerNote is set
     * 
     * @return bool true if SellerNote  is set
     */
    public function isSetSellerNote ()
    {
        return ! is_null($this->_fields['SellerNote']['FieldValue']);
    }

    /**
     * Gets the value of the SellerBillingAgreementAttributes.
     * 
     * @return SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     */
    public function getSellerBillingAgreementAttributes ()
    {
        return $this->_fields['SellerBillingAgreementAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes.
     * 
     * @param SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     * @return void
     */
    public function setSellerBillingAgreementAttributes ($value)
    {
        $this->_fields['SellerBillingAgreementAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes  and returns this instance
     * 
     * @param SellerBillingAgreementAttributes $value SellerBillingAgreementAttributes
     * @return OffAmazonPaymentsService_Model_BillingAgreementAttributes instance
     */
    public function withSellerBillingAgreementAttributes ($value)
    {
        $this->setSellerBillingAgreementAttributes($value);
        return $this;
    }

    /**
     * Checks if SellerBillingAgreementAttributes  is set
     * 
     * @return bool true if SellerBillingAgreementAttributes property is set
     */
    public function isSetSellerBillingAgreementAttributes ()
    {
        return ! is_null($this->_fields['SellerBillingAgreementAttributes']['FieldValue']);
    }
}
?>