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
 * OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>BillingAgreementDetails: OffAmazonPaymentsService_Model_BillingAgreementDetails</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>BillingAgreementDetails: OffAmazonPaymentsService_Model_BillingAgreementDetails</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            
            'BillingAgreementDetails' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAgreementDetails'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the BillingAgreementDetails.
     * 
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails BillingAgreementDetails
     */
    public function getBillingAgreementDetails ()
    {
        return $this->_fields['BillingAgreementDetails']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementDetails.
     * 
     * @param OffAmazonPaymentsService_Model_BillingAgreementDetails BillingAgreementDetails
     * @return void
     */
    public function setBillingAgreementDetails ($value)
    {
        $this->_fields['BillingAgreementDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementDetails  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_BillingAgreementDetails $value BillingAgreementDetails
     * @return OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResult instance
     */
    public function withBillingAgreementDetails ($value)
    {
        $this->setBillingAgreementDetails($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementDetails  is set
     * 
     * @return bool true if BillingAgreementDetails property is set
     */
    public function isSetBillingAgreementDetails ()
    {
        return ! is_null($this->_fields['BillingAgreementDetails']['FieldValue']);
    }
}
?>