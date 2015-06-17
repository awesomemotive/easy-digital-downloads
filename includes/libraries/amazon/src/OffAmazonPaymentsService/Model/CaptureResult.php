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


/**
 *  @see OffAmazonPaymentsService_Model
 */
require_once 'OffAmazonPaymentsService/Model.php';  

    

/**
 * OffAmazonPaymentsService_Model_CaptureResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>CaptureDetails: OffAmazonPaymentsService_Model_CaptureDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_CaptureResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_CaptureResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>CaptureDetails: OffAmazonPaymentsService_Model_CaptureDetails</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'CaptureDetails' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_CaptureDetails'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the CaptureDetails.
     * 
     * @return OffAmazonPaymentsService_Model_CaptureDetails CaptureDetails
     */
    public function getCaptureDetails() 
    {
        return $this->_fields['CaptureDetails']['FieldValue'];
    }

    /**
     * Sets the value of the CaptureDetails.
     * 
     * @param OffAmazonPaymentsService_Model_CaptureDetails CaptureDetails
     * @return void
     */
    public function setCaptureDetails($value) 
    {
        $this->_fields['CaptureDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CaptureDetails  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_CaptureDetails $value CaptureDetails
     * @return OffAmazonPaymentsService_Model_CaptureResult instance
     */
    public function withCaptureDetails($value)
    {
        $this->setCaptureDetails($value);
        return $this;
    }


    /**
     * Checks if CaptureDetails  is set
     * 
     * @return bool true if CaptureDetails property is set
     */
    public function isSetCaptureDetails()
    {
        return !is_null($this->_fields['CaptureDetails']['FieldValue']);

    }
}
?>