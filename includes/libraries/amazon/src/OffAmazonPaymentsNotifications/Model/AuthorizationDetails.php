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
 * OffAmazonPaymentsNotifications_Model_AuthorizationDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonAuthorizationId: string</li>
 * <li>AuthorizationReferenceId: string</li>
 * <li>AuthorizationAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>CapturedAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>AuthorizationFee: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>IdList: OffAmazonPaymentsNotifications_Model_IdList</li>
 * <li>CreationTimestamp: string</li>
 * <li>ExpirationTimestamp: string</li>
 * <li>AddressVerificationCode: string</li>
 * <li>AuthorizationStatus: OffAmazonPaymentsNotifications_Model_Status</li>
 * <li>OrderItemCategories: 
 *     OffAmazonPaymentsNotifications_Model_OrderItemCategories</li>
 * <li>CaptureNow: bool</li>
 * <li>SoftDescriptor: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_AuthorizationDetails extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_AuthorizationDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonAuthorizationId: string</li>
     * <li>AuthorizationReferenceId: string</li>
     * <li>AuthorizationAmount: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>CapturedAmount: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>AuthorizationFee: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>IdList: OffAmazonPaymentsNotifications_Model_IdList</li>
     * <li>CreationTimestamp: string</li>
     * <li>ExpirationTimestamp: string</li>
     * <li>AddressVerificationCode: string</li>
     * <li>AuthorizationStatus: OffAmazonPaymentsNotifications_Model_Status</li>
     * <li>OrderItemCategories: 
     *     OffAmazonPaymentsNotifications_Model_OrderItemCategories</li>
     * <li>CaptureNow: bool</li>
     * <li>SoftDescriptor: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'AmazonAuthorizationId' => array(
            'FieldValue' => null, 
            'FieldType' => 'string'
         ),
         'AuthorizationReferenceId' => array(
            'FieldValue' => null, 
            'FieldType' => 'string'
         ),
         'AuthorizationAmount' => array(
            'FieldValue' => null,
            'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
         ),
         'CapturedAmount' => array(
            'FieldValue' => null, 
            'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
         ),
         'AuthorizationFee' => array(
            'FieldValue' => null, 
            'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
         ),
         'IdList' => array(
            'FieldValue' => null, 
            'FieldType' => 'OffAmazonPaymentsNotifications_Model_IdList'
         ),
         'CreationTimestamp' => array(
             'FieldValue' => null, 
             'FieldType' => 'string'
         ),
         'ExpirationTimestamp' => array(
             'FieldValue' => null, 
             'FieldType' => 'string'
         ),
         'AddressVerificationCode' => array(
         	 'FieldValue' => null,
         	 'FieldType' => 'string'
         ),
         'AuthorizationStatus' => array(
             'FieldValue' => null, 
             'FieldType' => 'OffAmazonPaymentsNotifications_Model_Status'
         ),
         'OrderItemCategories' => array(
             'FieldValue' => null, 
             'FieldType' 
                    => 'OffAmazonPaymentsNotifications_Model_OrderItemCategories'
         ),
         'CaptureNow' => array(
             'FieldValue' => null, 
             'FieldType' => 'bool'
         ),
         'SoftDescriptor' => array(
             'FieldValue' => null, 
             'FieldType' => 'string'
         ),
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmazonAuthorizationId property.
     * 
     * @return string AmazonAuthorizationId
     */
    public function getAmazonAuthorizationId() 
    {
        return $this->fields['AmazonAuthorizationId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonAuthorizationId property.
     * 
     * @param string $value AmazonAuthorizationId value
     * 
     * @return this instance
     */
    public function setAmazonAuthorizationId($value) 
    {
        $this->fields['AmazonAuthorizationId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonAuthorizationId and returns this instance
     * 
     * @param string $value AmazonAuthorizationId value
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withAmazonAuthorizationId($value)
    {
        $this->setAmazonAuthorizationId($value);
        return $this;
    }


    /**
     * Checks if AmazonAuthorizationId is set
     * 
     * @return bool true if AmazonAuthorizationId  is set
     */
    public function isSetAmazonAuthorizationId()
    {
        return !is_null($this->fields['AmazonAuthorizationId']['FieldValue']);
    }

    /**
     * Gets the value of the AuthorizationReferenceId property.
     * 
     * @return string AuthorizationReferenceId
     */
    public function getAuthorizationReferenceId() 
    {
        return $this->fields['AuthorizationReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationReferenceId property.
     * 
     * @param string $value AuthorizationReferenceId
     * 
     * @return this instance
     */
    public function setAuthorizationReferenceId($value) 
    {
        $this->fields['AuthorizationReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AuthorizationReferenceId and returns this instance
     * 
     * @param string $value AuthorizationReferenceId
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withAuthorizationReferenceId($value)
    {
        $this->setAuthorizationReferenceId($value);
        return $this;
    }


    /**
     * Checks if AuthorizationReferenceId is set
     * 
     * @return bool true if AuthorizationReferenceId  is set
     */
    public function isSetAuthorizationReferenceId()
    {
        return !is_null($this->fields['AuthorizationReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the AuthorizationAmount.
     * 
     * @return Price AuthorizationAmount
     */
    public function getAuthorizationAmount() 
    {
        return $this->fields['AuthorizationAmount']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationAmount.
     * 
     * @param Price $value AuthorizationAmount
     * 
     * @return void
     */
    public function setAuthorizationAmount($value) 
    {
        $this->fields['AuthorizationAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationAmount  and returns this instance
     * 
     * @param Price $value AuthorizationAmount
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withAuthorizationAmount($value)
    {
        $this->setAuthorizationAmount($value);
        return $this;
    }


    /**
     * Checks if AuthorizationAmount  is set
     * 
     * @return bool true if AuthorizationAmount property is set
     */
    public function isSetAuthorizationAmount()
    {
        return !is_null($this->fields['AuthorizationAmount']['FieldValue']);

    }

    /**
     * Gets the value of the CapturedAmount.
     * 
     * @return Price CapturedAmount
     */
    public function getCapturedAmount() 
    {
        return $this->fields['CapturedAmount']['FieldValue'];
    }

    /**
     * Sets the value of the CapturedAmount.
     * 
     * @param Price $value CapturedAmount
     * 
     * @return void
     */
    public function setCapturedAmount($value) 
    {
        $this->fields['CapturedAmount']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CapturedAmount  and returns this instance
     * 
     * @param Price $value CapturedAmount
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withCapturedAmount($value)
    {
        $this->setCapturedAmount($value);
        return $this;
    }


    /**
     * Checks if CapturedAmount  is set
     * 
     * @return bool true if CapturedAmount property is set
     */
    public function isSetCapturedAmount()
    {
        return !is_null($this->fields['CapturedAmount']['FieldValue']);

    }

    /**
     * Gets the value of the AuthorizationFee.
     * 
     * @return Price AuthorizationFee
     */
    public function getAuthorizationFee() 
    {
        return $this->fields['AuthorizationFee']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationFee.
     * 
     * @param Price $value AuthorizationFee
     * 
     * @return void
     */
    public function setAuthorizationFee($value) 
    {
        $this->fields['AuthorizationFee']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationFee  and returns this instance
     * 
     * @param Price $value AuthorizationFee
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withAuthorizationFee($value)
    {
        $this->setAuthorizationFee($value);
        return $this;
    }


    /**
     * Checks if AuthorizationFee  is set
     * 
     * @return bool true if AuthorizationFee property is set
     */
    public function isSetAuthorizationFee()
    {
        return !is_null($this->fields['AuthorizationFee']['FieldValue']);

    }

    /**
     * Gets the value of the IdList.
     * 
     * @return IdList IdList
     */
    public function getIdList() 
    {
        return $this->fields['IdList']['FieldValue'];
    }

    /**
     * Sets the value of the IdList.
     * 
     * @param IdList $value IdList
     * 
     * @return void
     */
    public function setIdList($value) 
    {
        $this->fields['IdList']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the IdList  and returns this instance
     * 
     * @param IdList $value IdList
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withIdList($value)
    {
        $this->setIdList($value);
        return $this;
    }


    /**
     * Checks if IdList  is set
     * 
     * @return bool true if IdList property is set
     */
    public function isSetIdList()
    {
        return !is_null($this->fields['IdList']['FieldValue']);

    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp() 
    {
        return $this->fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string $value CreationTimestamp
     * 
     * @return this instance
     */
    public function setCreationTimestamp($value) 
    {
        $this->fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withCreationTimestamp($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }


    /**
     * Checks if CreationTimestamp is set
     * 
     * @return bool true if CreationTimestamp  is set
     */
    public function isSetCreationTimestamp()
    {
        return !is_null($this->fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the ExpirationTimestamp property.
     * 
     * @return string ExpirationTimestamp
     */
    public function getExpirationTimestamp() 
    {
        return $this->fields['ExpirationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the ExpirationTimestamp property.
     * 
     * @param string $value ExpirationTimestamp
     * 
     * @return this instance
     */
    public function setExpirationTimestamp($value) 
    {
        $this->fields['ExpirationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ExpirationTimestamp and returns this instance
     * 
     * @param string $value ExpirationTimestamp
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withExpirationTimestamp($value)
    {
        $this->setExpirationTimestamp($value);
        return $this;
    }


    /**
     * Checks if ExpirationTimestamp is set
     * 
     * @return bool true if ExpirationTimestamp  is set
     */
    public function isSetExpirationTimestamp()
    {
        return !is_null($this->fields['ExpirationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the AddrerificationCode property.
     *
     * @param string AddressVerificationCode
     * @return this instance
     */
    public function getAddressVerificationCode()
    {
    	return $this->fields['AddressVerificationCode']['FieldValue'];
    }
    
    /**
     * Sets the value of the AddressVerificationCode property.
     *
     * @param string AddressVerificationCode
     * @return this instance
     */
    public function setAddressVerificationCode($value)
    {
    	$this->fields['AddressVerificationCode']['FieldValue'] = $value;
    	return $this;
    }
    
    /**
     * Sets the value of the AddressVerificationCode and return this instance
     *
     * @param string $value AddressVerificationCode
     * @return OffAmazonPaymentsService_Model_AuthorizationDetails instance
     */
    public function withAddressVerificationCode($value)
    {
    	$this->setAddressVerificationCode($value);
    	return $this;
    }
    
    /**
     * Checks if AddressVerificationCode is set
     *
     * @return bool true if AddressVerificationCode is set
     */
    public function isSetAddressVerificationCode()
    {
    	return !is_null($this->fields['AddressVerificationCode']['FieldValue']);
    }
    
    /**
     * Gets the value of the AuthorizationStatus.
     * 
     * @return Status AuthorizationStatus
     */
    public function getAuthorizationStatus() 
    {
        return $this->fields['AuthorizationStatus']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationStatus.
     * 
     * @param Status $value AuthorizationStatus
     * 
     * @return void
     */
    public function setAuthorizationStatus($value) 
    {
        $this->fields['AuthorizationStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationStatus  and returns this instance
     * 
     * @param Status $value AuthorizationStatus
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withAuthorizationStatus($value)
    {
        $this->setAuthorizationStatus($value);
        return $this;
    }


    /**
     * Checks if AuthorizationStatus  is set
     * 
     * @return bool true if AuthorizationStatus property is set
     */
    public function isSetAuthorizationStatus()
    {
        return !is_null($this->fields['AuthorizationStatus']['FieldValue']);

    }

    /**
     * Gets the value of the OrderItemCategories.
     * 
     * @return OrderItemCategories OrderItemCategories
     */
    public function getOrderItemCategories() 
    {
        return $this->fields['OrderItemCategories']['FieldValue'];
    }

    /**
     * Sets the value of the OrderItemCategories.
     * 
     * @param OrderItemCategories $value OrderItemCategories
     * 
     * @return void
     */
    public function setOrderItemCategories($value) 
    {
        $this->fields['OrderItemCategories']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderItemCategories  and returns this instance
     * 
     * @param OrderItemCategories $value OrderItemCategories
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withOrderItemCategories($value)
    {
        $this->setOrderItemCategories($value);
        return $this;
    }


    /**
     * Checks if OrderItemCategories  is set
     * 
     * @return bool true if OrderItemCategories property is set
     */
    public function isSetOrderItemCategories()
    {
        return !is_null($this->fields['OrderItemCategories']['FieldValue']);

    }

    /**
     * Gets the value of the CaptureNow property.
     * 
     * @return bool CaptureNow
     */
    public function getCaptureNow() 
    {
        return $this->fields['CaptureNow']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureNow property.
     * 
     * @param bool $value CaptureNow
     * 
     * @return this instance
     */
    public function setCaptureNow($value) 
    {
        $this->fields['CaptureNow']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CaptureNow and returns this instance
     * 
     * @param bool $value CaptureNow
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withCaptureNow($value)
    {
        $this->setCaptureNow($value);
        return $this;
    }


    /**
     * Checks if CaptureNow is set
     * 
     * @return bool true if CaptureNow  is set
     */
    public function isSetCaptureNow()
    {
        return !is_null($this->fields['CaptureNow']['FieldValue']);
    }

    /**
     * Gets the value of the SoftDescriptor property.
     * 
     * @return string SoftDescriptor
     */
    public function getSoftDescriptor() 
    {
        return $this->fields['SoftDescriptor']['FieldValue'];
    }

    /**
     * Sets the value of the SoftDescriptor property.
     * 
     * @param string $value SoftDescriptor
     * 
     * @return this instance
     */
    public function setSoftDescriptor($value) 
    {
        $this->fields['SoftDescriptor']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SoftDescriptor and returns this instance
     * 
     * @param string $value SoftDescriptor
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationDetails instance
     */
    public function withSoftDescriptor($value)
    {
        $this->setSoftDescriptor($value);
        return $this;
    }


    /**
     * Checks if SoftDescriptor is set
     * 
     * @return bool true if SoftDescriptor  is set
     */
    public function isSetSoftDescriptor()
    {
        return !is_null($this->fields['SoftDescriptor']['FieldValue']);
    }
}
?>