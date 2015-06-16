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
 * OffAmazonPaymentsService_Model_Constraints
 * 
 * Properties:
 * <ul>
 * 
 * <li>Constraint: OffAmazonPaymentsService_Model_Constraint</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_Constraints extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_Constraints
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Constraint: OffAmazonPaymentsService_Model_Constraint</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Constraint' => array('FieldValue' => array(), 'FieldType' => array('OffAmazonPaymentsService_Model_Constraint')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the Constraint.
     * 
     * @return array of Constraint Constraint
     */
    public function getConstraint() 
    {
        return $this->_fields['Constraint']['FieldValue'];
    }

    /**
     * Sets the value of the Constraint.
     * 
     * @param mixed Constraint or an array of Constraint Constraint
     * @return this instance
     */
    public function setConstraint($constraint) 
    {
        if (!$this->_isNumericArray($constraint)) {
            $constraint =  array ($constraint);    
        }
        $this->_fields['Constraint']['FieldValue'] = $constraint;
        return $this;
    }


    /**
     * Sets single or multiple values of Constraint list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withConstraint($constraint1, $constraint2)</code>
     * 
     * @param Constraint  $constraintArgs one or more Constraint
     * @return OffAmazonPaymentsService_Model_Constraints  instance
     */
    public function withConstraint($constraintArgs)
    {
        foreach (func_get_args() as $constraint) {
            $this->_fields['Constraint']['FieldValue'][] = $constraint;
        }
        return $this;
    }   



    /**
     * Checks if Constraint list is non-empty
     * 
     * @return bool true if Constraint list is non-empty
     */
    public function isSetConstraint()
    {
        return count ($this->_fields['Constraint']['FieldValue']) > 0;
    }
}
?>