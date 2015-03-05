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
 * OffAmazonPaymentsService_Model_Buyer
 * 
 * Properties:
 * <ul>
 * 
 * <li>Name: string</li>
 * <li>Email: string</li>
 * <li>Phone: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_Buyer extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_Buyer
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Name: string</li>
     * <li>Email: string</li>
     * <li>Phone: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Name' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Email' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Phone' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Name property.
     * 
     * @return string Name
     */
    public function getName() 
    {
        return $this->_fields['Name']['FieldValue'];
    }

    /**
     * Sets the value of the Name property.
     * 
     * @param string Name
     * @return this instance
     */
    public function setName($value) 
    {
        $this->_fields['Name']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Name and returns this instance
     * 
     * @param string $value Name
     * @return OffAmazonPaymentsService_Model_Buyer instance
     */
    public function withName($value)
    {
        $this->setName($value);
        return $this;
    }


    /**
     * Checks if Name is set
     * 
     * @return bool true if Name  is set
     */
    public function isSetName()
    {
        return !is_null($this->_fields['Name']['FieldValue']);
    }

    /**
     * Gets the value of the Email property.
     * 
     * @return string Email
     */
    public function getEmail() 
    {
        return $this->_fields['Email']['FieldValue'];
    }

    /**
     * Sets the value of the Email property.
     * 
     * @param string Email
     * @return this instance
     */
    public function setEmail($value) 
    {
        $this->_fields['Email']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Email and returns this instance
     * 
     * @param string $value Email
     * @return OffAmazonPaymentsService_Model_Buyer instance
     */
    public function withEmail($value)
    {
        $this->setEmail($value);
        return $this;
    }


    /**
     * Checks if Email is set
     * 
     * @return bool true if Email  is set
     */
    public function isSetEmail()
    {
        return !is_null($this->_fields['Email']['FieldValue']);
    }

    /**
     * Gets the value of the Phone property.
     * 
     * @return string Phone
     */
    public function getPhone() 
    {
        return $this->_fields['Phone']['FieldValue'];
    }

    /**
     * Sets the value of the Phone property.
     * 
     * @param string Phone
     * @return this instance
     */
    public function setPhone($value) 
    {
        $this->_fields['Phone']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Phone and returns this instance
     * 
     * @param string $value Phone
     * @return OffAmazonPaymentsService_Model_Buyer instance
     */
    public function withPhone($value)
    {
        $this->setPhone($value);
        return $this;
    }


    /**
     * Checks if Phone is set
     * 
     * @return bool true if Phone  is set
     */
    public function isSetPhone()
    {
        return !is_null($this->_fields['Phone']['FieldValue']);
    }
}
?>