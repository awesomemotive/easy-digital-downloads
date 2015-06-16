<?php

/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
 *  @see OffAmazonPaymentsNotifications_Model
 */
require_once 'OffAmazonPayments/Model.php';  

    

/**
 * OffAmazonPaymentsNotifications_Model_SolutionProviderOption
 * 
 * Properties:
 * <ul>
 * 
 * <li>name: string</li>
 * <li>value: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_SolutionProviderOption extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_SolutionProviderOption
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>name: string</li>
     * <li>value: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'name' => array('FieldValue' => null, 'FieldType' => 'string'),
        'value' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the name property.
     * 
     * @return string name
     */
    public function getname() 
    {
        return $this->fields['name']['FieldValue'];
    }

    /**
     * Sets the value of the name property.
     * 
     * @param string name
     * @return this instance
     */
    public function setname($value) 
    {
        $this->fields['name']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the name and returns this instance
     * 
     * @param string $value name
     * @return OffAmazonPaymentsNotifications_Model_SolutionProviderOption instance
     */
    public function withname($value)
    {
        $this->setname($value);
        return $this;
    }


    /**
     * Checks if name is set
     * 
     * @return bool true if name  is set
     */
    public function isSetname()
    {
        return !is_null($this->fields['name']['FieldValue']);
    }

    /**
     * Gets the value of the value property.
     * 
     * @return string value
     */
    public function getvalue() 
    {
        return $this->fields['value']['FieldValue'];
    }

    /**
     * Sets the value of the value property.
     * 
     * @param string value
     * @return this instance
     */
    public function setvalue($value) 
    {
        $this->fields['value']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the value and returns this instance
     * 
     * @param string $value value
     * @return OffAmazonPaymentsNotifications_Model_SolutionProviderOption instance
     */
    public function withvalue($value)
    {
        $this->setvalue($value);
        return $this;
    }


    /**
     * Checks if value is set
     * 
     * @return bool true if value  is set
     */
    public function isSetvalue()
    {
        return !is_null($this->fields['value']['FieldValue']);
    }

}