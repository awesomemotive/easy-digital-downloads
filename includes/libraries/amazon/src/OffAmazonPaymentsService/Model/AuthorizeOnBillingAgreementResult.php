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
 * OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>AuthorizationDetails: OffAmazonPaymentsService_Model_AuthorizationDetails</li>
 * <li>AmazonOrderReferenceId: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AuthorizationDetails: OffAmazonPaymentsService_Model_AuthorizationDetails</li>
     * <li>AmazonOrderReferenceId: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            
            'AuthorizationDetails' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_AuthorizationDetails'
            ),
            
            'AmazonOrderReferenceId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AuthorizationDetails.
     * 
     * @return OffAmazonPaymentsService_Model_AuthorizationDetails AuthorizationDetails
     */
    public function getAuthorizationDetails ()
    {
        return $this->_fields['AuthorizationDetails']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizationDetails.
     * 
     * @param OffAmazonPaymentsService_Model_AuthorizationDetails AuthorizationDetails
     * @return void
     */
    public function setAuthorizationDetails ($value)
    {
        $this->_fields['AuthorizationDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizationDetails  and returns this instance
     * 
     * @param AuthorizationDetails $value AuthorizationDetails
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult instance
     */
    public function withAuthorizationDetails ($value)
    {
        $this->setAuthorizationDetails($value);
        return $this;
    }

    /**
     * Checks if AuthorizationDetails  is set
     * 
     * @return bool true if OffAmazonPaymentsService_Model_AuthorizationDetails property is set
     */
    public function isSetAuthorizationDetails ()
    {
        return ! is_null($this->_fields['AuthorizationDetails']['FieldValue']);
    }

    /**
     * Gets the value of the AmazonOrderReferenceId property.
     * 
     * @return string AmazonOrderReferenceId
     */
    public function getAmazonOrderReferenceId ()
    {
        return $this->_fields['AmazonOrderReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonOrderReferenceId property.
     * 
     * @param string AmazonOrderReferenceId
     * @return this instance
     */
    public function setAmazonOrderReferenceId ($value)
    {
        $this->_fields['AmazonOrderReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonOrderReferenceId and returns this instance
     * 
     * @param string $value AmazonOrderReferenceId
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult instance
     */
    public function withAmazonOrderReferenceId ($value)
    {
        $this->setAmazonOrderReferenceId($value);
        return $this;
    }

    /**
     * Checks if AmazonOrderReferenceId is set
     * 
     * @return bool true if AmazonOrderReferenceId  is set
     */
    public function isSetAmazonOrderReferenceId ()
    {
        return ! is_null($this->_fields['AmazonOrderReferenceId']['FieldValue']);
    }
}
?>