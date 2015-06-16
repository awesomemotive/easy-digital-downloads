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
 * OffAmazonPaymentsNotifications_Model_OrderReference
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonOrderReferenceId: string</li>
 * <li>OrderTotal: OffAmazonPaymentsNotifications_Model_OrderTotal</li>
 * <li>SellerOrderAttributes: 
 *     OffAmazonPaymentsNotifications_Model_SellerOrderAttributes</li>
 * <li>OrderReferenceStatus: 
 *     OffAmazonPaymentsNotifications_Model_OrderReferenceStatus</li>
 * <li>CreationTimestamp: string</li>
 * <li>ExpirationTimestamp: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_OrderReference
    extends OffAmazonPayments_Model
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_OrderReference
     *
     * @param mixed $data DOMElement or Associative Array to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>AmazonOrderReferenceId: string</li>
     * <li>OrderTotal: OffAmazonPaymentsNotifications_Model_OrderTotal</li>
     * <li>SellerOrderAttributes: 
     *     OffAmazonPaymentsNotifications_Model_OrderTotal</li>
     * <li>OrderReferenceStatus: 
     *     OffAmazonPaymentsNotifications_Model_OrderReferenceStatus</li>
     * <li>CreationTimestamp: string</li>
     * <li>ExpirationTimestamp: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
                'AmazonOrderReferenceId' => array(
                    'FieldValue' => null,
                    'FieldType' => 'string'
                ),
                'OrderTotal' => array(
                    'FieldValue' => null, 
                    'FieldType' => 'OffAmazonPaymentsNotifications_Model_OrderTotal'
                ),
                'SellerOrderAttributes' => array(
                        'FieldValue' => null,
                        'FieldType' 
                            => 'OffAmazonPaymentsNotifications_Model_SellerOrderAttributes'
                ),
                'OrderReferenceStatus' => array(
                        'FieldValue' => null,
                        'FieldType' => 'OffAmazonPaymentsNotifications_Model_OrderReferenceStatus'
                ),
                'CreationTimestamp' => array(
                        'FieldValue' => null,
                        'FieldType' => 'string'
                ),
                'ExpirationTimestamp' => array(
                        'FieldValue' => null,
                        'FieldType' => 'string'
                )
        );
        parent::__construct($data);
    }
    
    /**
     * Gets the value of the AmazonOrderReferenceId.
     *
     * @return string property value
     */
    public function getAmazonOrderReferenceId()
    {
        return $this->fields['AmazonOrderReferenceId']['FieldValue'];
    }
    
    /**
     * Sets the value of the AmazonOrderReferenceId.
     *
     * @param string $value new value
     * 
     * @return void
     */
    public function setAmazonOrderReferenceId($value)
    {
        $this->fields['AmazonOrderReferenceId']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the AmazonOrderReferenceId 
     * and returns this instance
     *
     * @param string $value AmazonOrderReferenceId
     * 
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withAmazonOrderReferenceId($value)
    {
        $this->setAmazonOrderReferenceId($value);
        return $this;
    }
    
    /**
     * Checks if AmazonOrderReferenceId is set
     *
     * @return bool true if AmazonOrderReferenceId property is set
     */
    public function isSetAmazonOrderReferenceId()
    {
        return !is_null($this->fields['AmazonOrderReferenceId']['FieldValue']);
    }
    
    /**
     * Gets the value of the OrderTotal.
     *
     * @return string property value
     */
    public function getOrderTotal()
    {
        return $this->fields['OrderTotal']['FieldValue'];
    }
    
    /**
     * Sets the value of the OrderTotal.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setOrderTotal($value)
    {
        $this->fields['OrderTotal']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the OrderTotal
     * and returns this instance
     *
     * @param string $value OrderTotal
     *
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withOrderTotal($value)
    {
        $this->setOrderTotal($value);
        return $this;
    }
    
    /**
     * Checks if OrderTotal is set
     *
     * @return bool true if OrderTotal property is set
     */
    public function isSetOrderTotal()
    {
        return !is_null($this->fields['OrderTotal']['FieldValue']);
    }
    
    /**
     * Gets the value of the SellerOrderAttributes.
     *
     * @return string property value
     */
    public function getSellerOrderAttributes()
    {
        return $this->fields['SellerOrderAttributes']['FieldValue'];
    }
    
    /**
     * Sets the value of the SellerOrderAttributes.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setSellerOrderAttributes($value)
    {
        $this->fields['SellerOrderAttributes']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the SellerOrderAttributes
     * and returns this instance
     *
     * @param string $value SellerOrderAttributes
     *
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withSellerOrderAttributes($value)
    {
        $this->setSellerOrderAttributes($value);
        return $this;
    }
    
    /**
     * Checks if SellerOrderAttributes is set
     *
     * @return bool true if SellerOrderAttributes property is set
     */
    public function isSetSellerOrderAttributes()
    {
        return !is_null($this->fields['SellerOrderAttributes']['FieldValue']);
    }
    
    /**
     * Gets the value of the OrderReferenceStatus.
     *
     * @return string property value
     */
    public function getOrderReferenceStatus()
    {
        return $this->fields['OrderReferenceStatus']['FieldValue'];
    }
    
    /**
     * Sets the value of the OrderReferenceStatus.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setOrderReferenceStatus($value)
    {
        $this->fields['OrderReferenceStatus']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the OrderReferenceStatus
     * and returns this instance
     *
     * @param string $value OrderReferenceStatus
     *
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withOrderReferenceStatus($value)
    {
        $this->setOrderReferenceStatus($value);
        return $this;
    }
    
    /**
     * Checks if OrderReferenceStatus is set
     *
     * @return bool true if OrderReferenceStatus property is set
     */
    public function isSetOrderReferenceStatus()
    {
        return !is_null($this->fields['OrderReferenceStatus']['FieldValue']);
    }
    
    /**
     * Gets the value of the CreationTimestamp.
     *
     * @return string property value
     */
    public function getCreationTimestamp()
    {
        return $this->fields['CreationTimestamp']['FieldValue'];
    }
    
    /**
     * Sets the value of the CreationTimestamp.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setCreationTimestamp($value)
    {
        $this->fields['CreationTimestamp']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the CreationTimestamp
     * and returns this instance
     *
     * @param string $value CreationTimestamp
     *
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withCreationTimestamp($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }
    
    /**
     * Checks if CreationTimestamp is set
     *
     * @return bool true if CreationTimestamp property is set
     */
    public function isSetCreationTimestamp()
    {
        return !is_null($this->fields['CreationTimestamp']['FieldValue']);
    }
    
    /**
     * Gets the value of the ExpirationTimestamp.
     *
     * @return string property value
     */
    public function getExpirationTimestamp()
    {
        return $this->fields['ExpirationTimestamp']['FieldValue'];
    }
    
    /**
     * Sets the value of the ExpirationTimestamp.
     *
     * @param string $value new value
     *
     * @return void
     */
    public function setExpirationTimestamp($value)
    {
        $this->fields['ExpirationTimestamp']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the ExpirationTimestamp
     * and returns this instance
     *
     * @param string $value ExpirationTimestamp
     *
     * @return OffAmazonPaymentsServices_Model_OrderReference instance
     */
    public function withExpirationTimestamp($value)
    {
        $this->setExpirationTimestamp($value);
        return $this;
    }
    
    /**
     * Checks if ExpirationTimestamp is set
     *
     * @return bool true if ExpirationTimestamp property is set
     */
    public function isSetExpirationTimestamp()
    {
        return !is_null($this->fields['ExpirationTimestamp']['FieldValue']);
    }
}
?>