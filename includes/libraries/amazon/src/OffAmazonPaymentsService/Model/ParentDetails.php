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
 * OffAmazonPaymentsService_Model_ParentDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>Id: string</li>
 * <li>Type: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_ParentDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ParentDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Id: string</li>
     * <li>Type: string</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'Id' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'Type' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the Id property.
     * 
     * @return string Id
     */
    public function getId ()
    {
        return $this->_fields['Id']['FieldValue'];
    }

    /**
     * Sets the value of the Id property.
     * 
     * @param string Id
     * @return this instance
     */
    public function setId ($value)
    {
        $this->_fields['Id']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Id and returns this instance
     * 
     * @param string $value Id
     * @return OffAmazonPaymentsService_Model_ParentDetails instance
     */
    public function withId ($value)
    {
        $this->setId($value);
        return $this;
    }

    /**
     * Checks if Id is set
     * 
     * @return bool true if Id  is set
     */
    public function isSetId ()
    {
        return ! is_null($this->_fields['Id']['FieldValue']);
    }

    /**
     * Gets the value of the Type property.
     * 
     * @return string Type
     */
    public function getType ()
    {
        return $this->_fields['Type']['FieldValue'];
    }

    /**
     * Sets the value of the Type property.
     * 
     * @param string Type
     * @return this instance
     */
    public function setType ($value)
    {
        $this->_fields['Type']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the Type and returns this instance
     * 
     * @param string $value Type
     * @return OffAmazonPaymentsService_Model_ParentDetails instance
     */
    public function withType ($value)
    {
        $this->setType($value);
        return $this;
    }

    /**
     * Checks if Type is set
     * 
     * @return bool true if Type  is set
     */
    public function isSetType ()
    {
        return ! is_null($this->_fields['Type']['FieldValue']);
    }
}
?>