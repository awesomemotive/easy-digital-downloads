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
 * OffAmazonPaymentsService_Model_RefundResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>RefundDetails: OffAmazonPaymentsService_Model_RefundDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_RefundResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_RefundResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>RefundDetails: OffAmazonPaymentsService_Model_RefundDetails</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (

        'RefundDetails' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_RefundDetails'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the RefundDetails.
     * 
     * @return OffAmazonPaymentsService_Model_RefundDetails RefundDetails
     */
    public function getRefundDetails() 
    {
        return $this->_fields['RefundDetails']['FieldValue'];
    }

    /**
     * Sets the value of the RefundDetails.
     * 
     * @param OffAmazonPaymentsService_Model_RefundDetails RefundDetails
     * @return void
     */
    public function setRefundDetails($value) 
    {
        $this->_fields['RefundDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the RefundDetails  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_RefundDetails $value RefundDetails
     * @return OffAmazonPaymentsService_Model_RefundResult instance
     */
    public function withRefundDetails($value)
    {
        $this->setRefundDetails($value);
        return $this;
    }


    /**
     * Checks if RefundDetails  is set
     * 
     * @return bool true if RefundDetails property is set
     */
    public function isSetRefundDetails()
    {
        return !is_null($this->_fields['RefundDetails']['FieldValue']);

    }




}
?>