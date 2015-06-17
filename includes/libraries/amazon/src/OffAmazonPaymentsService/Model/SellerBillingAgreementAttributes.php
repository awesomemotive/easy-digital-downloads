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
 * OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerBillingAgreementId: string</li>
 * <li>StoreName: string</li>
 * <li>CustomInformation: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerBillingAgreementId: string</li>
     * <li>StoreName: string</li>
     * <li>CustomInformation: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'SellerBillingAgreementId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'StoreName' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'CustomInformation' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the SellerBillingAgreementId property.
     * 
     * @return string SellerBillingAgreementId
     */
    public function getSellerBillingAgreementId ()
    {
        return $this->_fields['SellerBillingAgreementId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerBillingAgreementId property.
     * 
     * @param string SellerBillingAgreementId
     * @return this instance
     */
    public function setSellerBillingAgreementId ($value)
    {
        $this->_fields['SellerBillingAgreementId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerBillingAgreementId and returns this instance
     * 
     * @param string $value SellerBillingAgreementId
     * @return OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes instance
     */
    public function withSellerBillingAgreementId ($value)
    {
        $this->setSellerBillingAgreementId($value);
        return $this;
    }

    /**
     * Checks if SellerBillingAgreementId is set
     * 
     * @return bool true if SellerBillingAgreementId  is set
     */
    public function isSetSellerBillingAgreementId ()
    {
        return ! is_null($this->_fields['SellerBillingAgreementId']['FieldValue']);
    }

    /**
     * Gets the value of the StoreName property.
     * 
     * @return string StoreName
     */
    public function getStoreName ()
    {
        return $this->_fields['StoreName']['FieldValue'];
    }

    /**
     * Sets the value of the StoreName property.
     * 
     * @param string StoreName
     * @return this instance
     */
    public function setStoreName ($value)
    {
        $this->_fields['StoreName']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the StoreName and returns this instance
     * 
     * @param string $value StoreName
     * @return OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes instance
     */
    public function withStoreName ($value)
    {
        $this->setStoreName($value);
        return $this;
    }

    /**
     * Checks if StoreName is set
     * 
     * @return bool true if StoreName  is set
     */
    public function isSetStoreName ()
    {
        return ! is_null($this->_fields['StoreName']['FieldValue']);
    }

    /**
     * Gets the value of the CustomInformation property.
     * 
     * @return string CustomInformation
     */
    public function getCustomInformation ()
    {
        return $this->_fields['CustomInformation']['FieldValue'];
    }

    /**
     * Sets the value of the CustomInformation property.
     * 
     * @param string CustomInformation
     * @return this instance
     */
    public function setCustomInformation ($value)
    {
        $this->_fields['CustomInformation']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CustomInformation and returns this instance
     * 
     * @param string $value CustomInformation
     * @return OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes instance
     */
    public function withCustomInformation ($value)
    {
        $this->setCustomInformation($value);
        return $this;
    }

    /**
     * Checks if CustomInformation is set
     * 
     * @return bool true if CustomInformation  is set
     */
    public function isSetCustomInformation ()
    {
        return ! is_null($this->_fields['CustomInformation']['FieldValue']);
    }
}
?>