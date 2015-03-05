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
 * OffAmazonPaymentsService_Model_BillingAddress
 * 
 * Properties:
 * <ul>
 * 
 * <li>AddressType: string</li>
 * <li>PhysicalAddress: OffAmazonPaymentsService_Model_Address</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_BillingAddress extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_BillingAddress
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AddressType: string</li>
     * <li>PhysicalAddress: OffAmazonPaymentsService_Model_Address</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AddressType' => array('FieldValue' => null, 'FieldType' => 'string'),

        'PhysicalAddress' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Address'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the AddressType property.
     * 
     * @return string AddressType
     */
    public function getAddressType() 
    {
        return $this->_fields['AddressType']['FieldValue'];
    }

    /**
     * Sets the value of the AddressType property.
     * 
     * @param string AddressType
     * @return this instance
     */
    public function setAddressType($value) 
    {
        $this->_fields['AddressType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AddressType and returns this instance
     * 
     * @param string $value AddressType
     * @return OffAmazonPaymentsService_Model_BillingAddress instance
     */
    public function withAddressType($value)
    {
        $this->setAddressType($value);
        return $this;
    }


    /**
     * Checks if AddressType is set
     * 
     * @return bool true if AddressType  is set
     */
    public function isSetAddressType()
    {
        return !is_null($this->_fields['AddressType']['FieldValue']);
    }

    /**
     * Gets the value of the PhysicalAddress.
     * 
     * @return Address PhysicalAddress
     */
    public function getPhysicalAddress() 
    {
        return $this->_fields['PhysicalAddress']['FieldValue'];
    }

    /**
     * Sets the value of the PhysicalAddress.
     * 
     * @param Address PhysicalAddress
     * @return void
     */
    public function setPhysicalAddress($value) 
    {
        $this->_fields['PhysicalAddress']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the PhysicalAddress  and returns this instance
     * 
     * @param Address $value PhysicalAddress
     * @return OffAmazonPaymentsService_Model_BillingAddress instance
     */
    public function withPhysicalAddress($value)
    {
        $this->setPhysicalAddress($value);
        return $this;
    }


    /**
     * Checks if PhysicalAddress  is set
     * 
     * @return bool true if PhysicalAddress property is set
     */
    public function isSetPhysicalAddress()
    {
        return !is_null($this->_fields['PhysicalAddress']['FieldValue']);

    }
}
?>
