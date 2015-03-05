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
 * OffAmazonPaymentsNotifications_Model_SellerOrderAttributes
 * 
 * Properties:
 * <ul>
 * 
 * <li>SellerId: string</li>
 * <li>SellerOrderId: string</li>
 * <li>OrderItemCategories: 
 *     OffAmazonPaymentsNotifications_Model_OrderItemCategories</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_SellerOrderAttributes
    extends OffAmazonPayments_Model
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_SellerOrderAttributes
     *
     * @param mixed $data DOMElement or Associative Array to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>SellerId: string</li>
     * <li>SellerOrderId: string</li>
     * <li>OrderItemCategories: 
     *     OffAmazonPaymentsNotifications_Model_OrderItemCategories</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
                'SellerId' => array(
                    'FieldValue' => null, 
                    'FieldType' => 'string'
                ),
                'SellerOrderId' => array(
                        'FieldValue' => null,
                        'FieldType' => 'string'
                ),
                'OrderItemCategories' => array(
                        'FieldValue' => null,
                        'FieldType' 
                            => 'OffAmazonPaymentsNotifications_Model_OrderItemCategories'
                )
        );
        parent::__construct($data);
    }
    
    /**
     * Gets the value of the SellerId.
     *
     * @return string property value
     */
    public function getSellerId()
    {
        return $this->fields['SellerId']['FieldValue'];
    }
    
    /**
     * Sets the value of the SellerId.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setSellerId($value)
    {
        $this->fields['SellerId']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the SellerId
     * and returns this instance
     *
     * @param string $value SellerId
     *
     * @return OffAmazonPaymentsNotifications_Model_SellerOrderAttributes instance
     */
    public function withSellerId($value)
    {
        $this->setSellerId($value);
        return $this;
    }
    
    /**
     * Checks if SellerId is set
     *
     * @return bool true if SellerId property is set
     */
    public function isSetSellerId()
    {
        return !is_null($this->fields['SellerId']['FieldValue']);
    }
    
    /**
     * Gets the value of the SellerOrderId.
     *
     * @return string property value
     */
    public function getSellerOrderId()
    {
        return $this->fields['SellerOrderId']['FieldValue'];
    }
    
    /**
     * Sets the value of the SellerOrderId.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setSellerOrderId($value)
    {
        $this->fields['SellerOrderId']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the SellerOrderId
     * and returns this instance
     *
     * @param string $value SellerOrderId
     *
     * @return OffAmazonPaymentsNotifications_Model_SellerOrderAttributes instance
     */
    public function withSellerOrderId($value)
    {
        $this->setSellerOrderId($value);
        return $this;
    }
    
    /**
     * Checks if SellerOrderId is set
     *
     * @return bool true if SellerOrderId property is set
     */
    public function isSetSellerOrderId()
    {
        return !is_null($this->fields['SellerOrderId']['FieldValue']);
    }
    
    /**
     * Gets the value of the OrderItemCategories.
     *
     * @return string property value
     */
    public function getOrderItemCategories()
    {
        return $this->fields['OrderItemCategories']['FieldValue'];
    }
    
    /**
     * Sets the value of the OrderItemCategories.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setOrderItemCategories($value)
    {
        $this->fields['OrderItemCategories']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the OrderItemCategories
     * and returns this instance
     *
     * @param string $value OrderItemCategories
     *
     * @return OffAmazonPaymentsNotifications_Model_SellerOrderAttributes instance
     */
    public function withOrderItemCategories($value)
    {
        $this->setOrderItemCategories($value);
        return $this;
    }
    
    /**
     * Checks if OrderItemCategories is set
     *
     * @return bool true if OrderItemCategories property is set
     */
    public function isSetOrderItemCategories()
    {
        return !is_null($this->fields['OrderItemCategories']['FieldValue']);
    }
}
?>