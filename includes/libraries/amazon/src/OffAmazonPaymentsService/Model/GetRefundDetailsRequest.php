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
 * OffAmazonPaymentsService_Model_GetRefundDetailsRequest
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>AmazonRefundId: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_GetRefundDetailsRequest extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_GetRefundDetailsRequest
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SellerId: string</li>
     * <li>AmazonRefundId: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'SellerId' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AmazonRefundId' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the SellerId property.
     * 
     * @return string SellerId
     */
    public function getSellerId() 
    {
        return $this->_fields['SellerId']['FieldValue'];
    }

    /**
     * Sets the value of the SellerId property.
     * 
     * @param string SellerId
     * @return this instance
     */
    public function setSellerId($value) 
    {
        $this->_fields['SellerId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerId and returns this instance
     * 
     * @param string $value SellerId
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsRequest instance
     */
    public function withSellerId($value)
    {
        $this->setSellerId($value);
        return $this;
    }


    /**
     * Checks if SellerId is set
     * 
     * @return bool true if SellerId  is set
     */
    public function isSetSellerId()
    {
        return !is_null($this->_fields['SellerId']['FieldValue']);
    }

    /**
     * Gets the value of the AmazonRefundId property.
     * 
     * @return string AmazonRefundId
     */
    public function getAmazonRefundId() 
    {
        return $this->_fields['AmazonRefundId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonRefundId property.
     * 
     * @param string AmazonRefundId
     * @return this instance
     */
    public function setAmazonRefundId($value) 
    {
        $this->_fields['AmazonRefundId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonRefundId and returns this instance
     * 
     * @param string $value AmazonRefundId
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsRequest instance
     */
    public function withAmazonRefundId($value)
    {
        $this->setAmazonRefundId($value);
        return $this;
    }


    /**
     * Checks if AmazonRefundId is set
     * 
     * @return bool true if AmazonRefundId  is set
     */
    public function isSetAmazonRefundId()
    {
        return !is_null($this->_fields['AmazonRefundId']['FieldValue']);
    }




}
?>