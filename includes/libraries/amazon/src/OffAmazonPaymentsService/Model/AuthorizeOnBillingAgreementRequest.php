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
 * OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonBillingAgreementId: string</li>
 * <li>AuthorizationReferenceId: string</li>
 * <li>AuthorizationAmount: OffAmazonPaymentsService_Model_Price</li>
 * <li>SellerAuthorizationNote: string</li>
 * <li>TransactionTimeout: int</li>
 * <li>CaptureNow: bool</li>
 * <li>SoftDescriptor: string</li>
 * <li>SellerNote: string</li>
 * <li>PlatformId: string</li>
 * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
 * <li>InheritShippingAddress: bool</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonBillingAgreementId: string</li>
     * <li>AuthorizationReferenceId: string</li>
     * <li>AuthorizationAmount: OffAmazonPaymentsService_Model_Price</li>
     * <li>SellerAuthorizationNote: string</li>
     * <li>TransactionTimeout: int</li>
     * <li>CaptureNow: bool</li>
     * <li>SoftDescriptor: string</li>
     * <li>SellerNote: string</li>
     * <li>PlatformId: string</li>
     * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
     * <li>InheritShippingAddress: bool</li>
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
            'AuthorizationReferenceId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'AuthorizationAmount' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_Price'
            ),
            
            'SellerAuthorizationNote' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'TransactionTimeout' => array(
                'FieldValue' => null,
                'FieldType' => 'int'
            ),
            'CaptureNow' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
            ),
            'SoftDescriptor' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'SellerNote' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'PlatformId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'SellerOrderAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_SellerOrderAttributes'
            ),
            
            'InheritShippingAddress' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
            )
        );
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
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
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
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
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
     * Gets the value of the AuthorizationReferenceId property.
     * 
     * @return string AuthorizationReferenceId
     */
    public function getAuthorizationReferenceId ()
    {
        return $this->_fields['AuthorizationReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationReferenceId property.
     * 
     * @param string AuthorizationReferenceId
     * @return this instance
     */
    public function setAuthorizationReferenceId ($value)
    {
        $this->_fields['AuthorizationReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AuthorizationReferenceId and returns this instance
     * 
     * @param string $value AuthorizationReferenceId
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withAuthorizationReferenceId ($value)
    {
        $this->setAuthorizationReferenceId($value);
        return $this;
    }

    /**
     * Checks if AuthorizationReferenceId is set
     * 
     * @return bool true if AuthorizationReferenceId  is set
     */
    public function isSetAuthorizationReferenceId ()
    {
        return ! is_null($this->_fields['AuthorizationReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the AuthorizationAmount.
     * 
     * @return Price AuthorizationAmount
     */
    public function getAuthorizationAmount ()
    {
        return $this->_fields['AuthorizationAmount']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationAmount.
     * 
     * @param Price AuthorizationAmount
     * @return void
     */
    public function setAuthorizationAmount ($value)
    {
        $this->_fields['AuthorizationAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationAmount  and returns this instance
     * 
     * @param Price $value AuthorizationAmount
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withAuthorizationAmount ($value)
    {
        $this->setAuthorizationAmount($value);
        return $this;
    }

    /**
     * Checks if AuthorizationAmount  is set
     * 
     * @return bool true if AuthorizationAmount property is set
     */
    public function isSetAuthorizationAmount ()
    {
        return ! is_null($this->_fields['AuthorizationAmount']['FieldValue']);
    }

    /**
     * Gets the value of the SellerAuthorizationNote property.
     * 
     * @return string SellerAuthorizationNote
     */
    public function getSellerAuthorizationNote ()
    {
        return $this->_fields['SellerAuthorizationNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerAuthorizationNote property.
     * 
     * @param string SellerAuthorizationNote
     * @return this instance
     */
    public function setSellerAuthorizationNote ($value)
    {
        $this->_fields['SellerAuthorizationNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerAuthorizationNote and returns this instance
     * 
     * @param string $value SellerAuthorizationNote
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withSellerAuthorizationNote ($value)
    {
        $this->setSellerAuthorizationNote($value);
        return $this;
    }

    /**
     * Checks if SellerAuthorizationNote is set
     * 
     * @return bool true if SellerAuthorizationNote  is set
     */
    public function isSetSellerAuthorizationNote ()
    {
        return ! is_null($this->_fields['SellerAuthorizationNote']['FieldValue']);
    }

    /**
     * Gets the value of the TransactionTimeout property.
     * 
     * @return int TransactionTimeout
     */
    public function getTransactionTimeout ()
    {
        return $this->_fields['TransactionTimeout']['FieldValue'];
    }

    /**
     * Sets the value of the TransactionTimeout property.
     * 
     * @param int TransactionTimeout
     * @return this instance
     */
    public function setTransactionTimeout ($value)
    {
        $this->_fields['TransactionTimeout']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TransactionTimeout and returns this instance
     * 
     * @param int $value TransactionTimeout
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withTransactionTimeout ($value)
    {
        $this->setTransactionTimeout($value);
        return $this;
    }

    /**
     * Checks if TransactionTimeout is set
     * 
     * @return bool true if TransactionTimeout  is set
     */
    public function isSetTransactionTimeout ()
    {
        return ! is_null($this->_fields['TransactionTimeout']['FieldValue']);
    }

    /**
     * Gets the value of the CaptureNow property.
     * 
     * @return bool CaptureNow
     */
    public function getCaptureNow ()
    {
        return $this->_fields['CaptureNow']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureNow property.
     * 
     * @param bool CaptureNow
     * @return this instance
     */
    public function setCaptureNow ($value)
    {
        $this->_fields['CaptureNow']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CaptureNow and returns this instance
     * 
     * @param bool $value CaptureNow
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withCaptureNow ($value)
    {
        $this->setCaptureNow($value);
        return $this;
    }

    /**
     * Checks if CaptureNow is set
     * 
     * @return bool true if CaptureNow  is set
     */
    public function isSetCaptureNow ()
    {
        return ! is_null($this->_fields['CaptureNow']['FieldValue']);
    }

    /**
     * Gets the value of the SoftDescriptor property.
     * 
     * @return string SoftDescriptor
     */
    public function getSoftDescriptor ()
    {
        return $this->_fields['SoftDescriptor']['FieldValue'];
    }

    /**
     * Sets the value of the SoftDescriptor property.
     * 
     * @param string SoftDescriptor
     * @return this instance
     */
    public function setSoftDescriptor ($value)
    {
        $this->_fields['SoftDescriptor']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SoftDescriptor and returns this instance
     * 
     * @param string $value SoftDescriptor
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withSoftDescriptor ($value)
    {
        $this->setSoftDescriptor($value);
        return $this;
    }

    /**
     * Checks if SoftDescriptor is set
     * 
     * @return bool true if SoftDescriptor  is set
     */
    public function isSetSoftDescriptor ()
    {
        return ! is_null($this->_fields['SoftDescriptor']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
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
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
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
     * Gets the value of the SellerOrderAttributes.
     * 
     * @return SellerOrderAttributes SellerOrderAttributes
     */
    public function getSellerOrderAttributes ()
    {
        return $this->_fields['SellerOrderAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerOrderAttributes.
     * 
     * @param SellerOrderAttributes SellerOrderAttributes
     * @return void
     */
    public function setSellerOrderAttributes ($value)
    {
        $this->_fields['SellerOrderAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerOrderAttributes  and returns this instance
     * 
     * @param SellerOrderAttributes $value SellerOrderAttributes
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withSellerOrderAttributes ($value)
    {
        $this->setSellerOrderAttributes($value);
        return $this;
    }

    /**
     * Checks if SellerOrderAttributes  is set
     * 
     * @return bool true if SellerOrderAttributes property is set
     */
    public function isSetSellerOrderAttributes ()
    {
        return ! is_null($this->_fields['SellerOrderAttributes']['FieldValue']);
    }

    /**
     * Gets the value of the InheritShippingAddress property.
     * 
     * @return bool InheritShippingAddress
     */
    public function getInheritShippingAddress ()
    {
        return $this->_fields['InheritShippingAddress']['FieldValue'];
    }

    /**
     * Sets the value of the InheritShippingAddress property.
     * 
     * @param bool InheritShippingAddress
     * @return this instance
     */
    public function setInheritShippingAddress ($value)
    {
        $this->_fields['InheritShippingAddress']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the InheritShippingAddress and returns this instance
     * 
     * @param bool $value InheritShippingAddress
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest instance
     */
    public function withInheritShippingAddress ($value)
    {
        $this->setInheritShippingAddress($value);
        return $this;
    }

    /**
     * Checks if InheritShippingAddress is set
     * 
     * @return bool true if InheritShippingAddress  is set
     */
    public function isSetInheritShippingAddress ()
    {
        return ! is_null($this->_fields['InheritShippingAddress']['FieldValue']);
    }
}
?>