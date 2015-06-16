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
 * OffAmazonPaymentsService_Model_Constraint
 * 
 * Properties:
 * <ul>
 * 
 * <li>ConstraintID: string</li>
 * <li>Description: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_Constraint extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_Constraint
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ConstraintID: string</li>
     * <li>Description: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'ConstraintID' => array('FieldValue' => null, 'FieldType' => 'string'),
        'Description' => array('FieldValue' => null, 'FieldType' => 'string'),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the ConstraintID property.
     * 
     * @return string ConstraintID
     */
    public function getConstraintID() 
    {
        return $this->_fields['ConstraintID']['FieldValue'];
    }

    /**
     * Sets the value of the ConstraintID property.
     * 
     * @param string ConstraintID
     * @return this instance
     */
    public function setConstraintID($value) 
    {
        $this->_fields['ConstraintID']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ConstraintID and returns this instance
     * 
     * @param string $value ConstraintID
     * @return OffAmazonPaymentsService_Model_Constraint instance
     */
    public function withConstraintID($value)
    {
        $this->setConstraintID($value);
        return $this;
    }


    /**
     * Checks if ConstraintID is set
     * 
     * @return bool true if ConstraintID  is set
     */
    public function isSetConstraintID()
    {
        return !is_null($this->_fields['ConstraintID']['FieldValue']);
    }

    /**
     * Gets the value of the Description property.
     * 
     * @return string Description
     */
    public function getDescription() 
    {
        return $this->_fields['Description']['FieldValue'];
    }

    /**
     * Sets the value of the Description property.
     * 
     * @param string Description
     * @return this instance
     */
    public function setDescription($value) 
    {
        $this->_fields['Description']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Description and returns this instance
     * 
     * @param string $value Description
     * @return OffAmazonPaymentsService_Model_Constraint instance
     */
    public function withDescription($value)
    {
        $this->setDescription($value);
        return $this;
    }


    /**
     * Checks if Description is set
     * 
     * @return bool true if Description  is set
     */
    public function isSetDescription()
    {
        return !is_null($this->_fields['Description']['FieldValue']);
    }
}
?>