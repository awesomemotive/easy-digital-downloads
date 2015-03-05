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
 * OffAmazonPaymentsNotifications_Model_BillingAgreement
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonBillingAgreementId: string</li>
 * <li>SellerBillingAgreementAttributes: 
 *     OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes</li>
 * <li>BillingAgreementStatus: 
 *     OffAmazonPaymentsNotifications_Model_BillingAgreementStatus</li>
 * <li>CreationTimestamp: string</li>
 * <li>BillingAgreementLimits: 
 *     OffAmazonPaymentsNotifications_Model_BillingAgreementLimits</li>
 * <li>BillingAgreementConsent: bool</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_BillingAgreement extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_BillingAgreement
     *
     * @param mixed $data DOMElement or Associative Array to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>AmazonBillingAgreementId: string</li>
     * <li>SellerBillingAgreementAttributes: 
     *     OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes</li>
     * <li>BillingAgreementStatus: 
     *     OffAmazonPaymentsNotifications_Model_BillingAgreementStatus</li>
     * <li>CreationTimestamp: string</li>
     * <li>BillingAgreementLimits: 
     *     OffAmazonPaymentsNotifications_Model_BillingAgreementLimits</li>
     * <li>BillingAgreementConsent: bool</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->fields = array(
            'AmazonBillingAgreementId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerBillingAgreementAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes'
            ),
            'BillingAgreementStatus' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_BillingAgreementStatus'
            ),
            'CreationTimestamp' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'BillingAgreementLimits' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_BillingAgreementLimits'
            ),
            'BillingAgreementConsent' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
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
        return $this->fields['AmazonBillingAgreementId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonBillingAgreementId property.
     * 
     * @param string AmazonBillingAgreementId
     * @return this instance
     */
    public function setAmazonBillingAgreementId ($value)
    {
        $this->fields['AmazonBillingAgreementId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonBillingAgreementId and returns this instance
     * 
     * @param string $value AmazonBillingAgreementId
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementDetails instance
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
        return ! is_null($this->fields['AmazonBillingAgreementId']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementLimits.
     * 
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementLimits BillingAgreementLimits
     */
    public function getBillingAgreementLimits ()
    {
        return $this->fields['BillingAgreementLimits']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementLimits.
     * 
     * @param OffAmazonPaymentsNotifications_Model_BillingAgreementLimits BillingAgreementLimits
     * @return void
     */
    public function setBillingAgreementLimits ($value)
    {
        $this->fields['BillingAgreementLimits']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementLimits  and returns this instance
     * 
     * @param OffAmazonPaymentsNotifications_Model_BillingAgreementLimits $value BillingAgreementLimits
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBillingAgreementLimits ($value)
    {
        $this->setBillingAgreementLimits($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementLimits  is set
     * 
     * @return bool true if BillingAgreementLimits property is set
     */
    public function isSetBillingAgreementLimits ()
    {
        return ! is_null($this->fields['BillingAgreementLimits']['FieldValue']);
    }

    /**
     * Gets the value of the SellerBillingAgreementAttributes.
     * 
     * @return OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     */
    public function getSellerBillingAgreementAttributes ()
    {
        return $this->fields['SellerBillingAgreementAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes.
     * 
     * @param OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     * @return void
     */
    public function setSellerBillingAgreementAttributes ($value)
    {
        $this->fields['SellerBillingAgreementAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes  and returns this instance
     * 
     * @param OffAmazonPaymentsNotifications_Model_SellerBillingAgreementAttributes $value SellerBillingAgreementAttributes
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementDetails instance
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
        return ! is_null($this->fields['SellerBillingAgreementAttributes']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementStatus.
     * 
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementStatus BillingAgreementStatus
     */
    public function getBillingAgreementStatus ()
    {
        return $this->fields['BillingAgreementStatus']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementStatus.
     * 
     * @param OffAmazonPaymentsNotifications_Model_BillingAgreementStatus BillingAgreementStatus
     * @return void
     */
    public function setBillingAgreementStatus ($value)
    {
        $this->fields['BillingAgreementStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementStatus  and returns this instance
     * 
     * @param OffAmazonPaymentsNotifications_Model_BillingAgreementStatus $value BillingAgreementStatus
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementDetails instance
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
        return ! is_null($this->fields['BillingAgreementStatus']['FieldValue']);
    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp ()
    {
        return $this->fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string CreationTimestamp
     * @return this instance
     */
    public function setCreationTimestamp ($value)
    {
        $this->fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementDetails instance
     */
    public function withCreationTimestamp ($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }

    /**
     * Checks if CreationTimestamp is set
     *
     * @return bool true if CreationTimestamp  is set
     */
    public function isSetCreationTimestamp ()
    {
        return ! is_null($this->fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementConsent property.
     * 
     * @return bool BillingAgreementConsent
     */
    public function getBillingAgreementConsent ()
    {
        return $this->fields['BillingAgreementConsent']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementConsent property.
     * 
     * @param bool BillingAgreementConsent
     * @return this instance
     */
    public function setBillingAgreementConsent ($value)
    {
        $this->fields['BillingAgreementConsent']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the BillingAgreementConsent and returns this instance
     * 
     * @param bool $value BillingAgreementConsent
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementDetails instance
     */
    public function withBillingAgreementConsent ($value)
    {
        $this->setBillingAgreementConsent($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementConsent is set
     * 
     * @return bool true if BillingAgreementConsent  is set
     */
    public function isSetBillingAgreementConsent ()
    {
        return ! is_null($this->fields['BillingAgreementConsent']['FieldValue']);
    }
}
?>