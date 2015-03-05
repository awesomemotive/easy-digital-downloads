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
 * OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>SellerBillingAgreementId: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>SellerBillingAgreementId: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->fields = array(
            'SellerId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerBillingAgreementId' => array(
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
        return $this->fields['SellerBillingAgreementId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerBillingAgreementId property.
     * 
     * @param string SellerBillingAgreementId
     * @return this instance
     */
    public function setSellerBillingAgreementId ($value)
    {
        $this->fields['SellerBillingAgreementId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerBillingAgreementId and returns this instance
     * 
     * @param string $value SellerBillingAgreementId
     * @return OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes instance
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
        return ! is_null($this->fields['SellerBillingAgreementId']['FieldValue']);
    }

    /**
     * Gets the value of the SellerId property.
     * 
     * @return string SellerId
     */
    public function getSellerId ()
    {
        return $this->fields['SellerId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerId property.
     * 
     * @param string SellerId
     * @return this instance
     */
    public function setSellerId ($value)
    {
        $this->fields['SellerId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerId and returns this instance
     * 
     * @param string $value SellerId
     * @return OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes instance
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
        return ! is_null($this->fields['SellerId']['FieldValue']);
    }
}
?>