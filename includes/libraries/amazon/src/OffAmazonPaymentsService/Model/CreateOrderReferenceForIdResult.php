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
 * OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResult
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderReferenceDetails: OffAmazonPaymentsService_Model_OrderReferenceDetails</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResult extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResult
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>OrderReferenceDetails: OffAmazonPaymentsService_Model_OrderReferenceDetails</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            
            'OrderReferenceDetails' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_OrderReferenceDetails'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the OrderReferenceDetails.
     * 
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails OrderReferenceDetails
     */
    public function getOrderReferenceDetails ()
    {
        return $this->_fields['OrderReferenceDetails']['FieldValue'];
    }

    /**
     * Sets the value of the OrderReferenceDetails.
     * 
     * @param OffAmazonPaymentsService_Model_OrderReferenceDetails OrderReferenceDetails
     * @return void
     */
    public function setOrderReferenceDetails ($value)
    {
        $this->_fields['OrderReferenceDetails']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderReferenceDetails  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_OrderReferenceDetails $value OrderReferenceDetails
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResult instance
     */
    public function withOrderReferenceDetails ($value)
    {
        $this->setOrderReferenceDetails($value);
        return $this;
    }

    /**
     * Checks if OrderReferenceDetails  is set
     * 
     * @return bool true if OrderReferenceDetails property is set
     */
    public function isSetOrderReferenceDetails ()
    {
        return ! is_null($this->_fields['OrderReferenceDetails']['FieldValue']);
    }
}
?>