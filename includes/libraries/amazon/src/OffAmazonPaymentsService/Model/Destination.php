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
 * OffAmazonPaymentsService_Model_Destination
 * 
 * Properties:
 * <ul>
 * 
 * <li>DestinationType: string</li>
 * <li>PhysicalDestination: OffAmazonPaymentsService_Model_Address</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_Destination extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_Destination
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>DestinationType: string</li>
     * <li>PhysicalDestination: OffAmazonPaymentsService_Model_Address</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'DestinationType' => array('FieldValue' => null, 'FieldType' => 'string'),

        'PhysicalDestination' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Address'),

        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the DestinationType property.
     * 
     * @return string DestinationType
     */
    public function getDestinationType() 
    {
        return $this->_fields['DestinationType']['FieldValue'];
    }

    /**
     * Sets the value of the DestinationType property.
     * 
     * @param string DestinationType
     * @return this instance
     */
    public function setDestinationType($value) 
    {
        $this->_fields['DestinationType']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the DestinationType and returns this instance
     * 
     * @param string $value DestinationType
     * @return OffAmazonPaymentsService_Model_Destination instance
     */
    public function withDestinationType($value)
    {
        $this->setDestinationType($value);
        return $this;
    }


    /**
     * Checks if DestinationType is set
     * 
     * @return bool true if DestinationType  is set
     */
    public function isSetDestinationType()
    {
        return !is_null($this->_fields['DestinationType']['FieldValue']);
    }

    /**
     * Gets the value of the PhysicalDestination.
     * 
     * @return Address PhysicalDestination
     */
    public function getPhysicalDestination() 
    {
        return $this->_fields['PhysicalDestination']['FieldValue'];
    }

    /**
     * Sets the value of the PhysicalDestination.
     * 
     * @param Address PhysicalDestination
     * @return void
     */
    public function setPhysicalDestination($value) 
    {
        $this->_fields['PhysicalDestination']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the PhysicalDestination  and returns this instance
     * 
     * @param Address $value PhysicalDestination
     * @return OffAmazonPaymentsService_Model_Destination instance
     */
    public function withPhysicalDestination($value)
    {
        $this->setPhysicalDestination($value);
        return $this;
    }


    /**
     * Checks if PhysicalDestination  is set
     * 
     * @return bool true if PhysicalDestination property is set
     */
    public function isSetPhysicalDestination()
    {
        return !is_null($this->_fields['PhysicalDestination']['FieldValue']);

    }
}
?>