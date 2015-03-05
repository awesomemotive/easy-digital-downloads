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
 * OffAmazonPaymentsService_Model_Address
 * 
 * Properties:
 * <ul>
 * 
 * <li>Name: string</li>
 * <li>AddressLine1: string</li>
 * <li>AddressLine2: string</li>
 * <li>AddressLine3: string</li>
 * <li>City: string</li>
 * <li>County: string</li>
 * <li>District: string</li>
 * <li>StateOrRegion: string</li>
 * <li>PostalCode: string</li>
 * <li>CountryCode: string</li>
 * <li>Phone: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_Address extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_Address
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>Name: string</li>
     * <li>AddressLine1: string</li>
     * <li>AddressLine2: string</li>
     * <li>AddressLine3: string</li>
     * <li>City: string</li>
     * <li>County: string</li>
     * <li>District: string</li>
     * <li>StateOrRegion: string</li>
     * <li>PostalCode: string</li>
     * <li>CountryCode: string</li>
     * <li>Phone: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'Name' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLine1' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLine2' => array('FieldValue' => null, 'FieldType' => 'string'),
        'AddressLine3' => array('FieldValue' => null, 'FieldType' => 'string'),
        'City' => array('FieldValue' => null, 'FieldType' => 'string'),
        'County' => array('FieldValue' => null, 'FieldType' => 'string'),
        'District' => array('FieldValue' => null, 'FieldType' => 'string'),
        'StateOrRegion' => array('FieldValue' => null, 'FieldType' => 'string'),
        'PostalCode' => array('FieldValue' => null, 'FieldType' => 'string'),
        'CountryCode' => array('FieldValue' => null, 'FieldType' => 'string'),
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
     * @return OffAmazonPaymentsService_Model_Address instance
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
     * Gets the value of the AddressLine1 property.
     * 
     * @return string AddressLine1
     */
    public function getAddressLine1() 
    {
        return $this->_fields['AddressLine1']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLine1 property.
     * 
     * @param string AddressLine1
     * @return this instance
     */
    public function setAddressLine1($value) 
    {
        $this->_fields['AddressLine1']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AddressLine1 and returns this instance
     * 
     * @param string $value AddressLine1
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withAddressLine1($value)
    {
        $this->setAddressLine1($value);
        return $this;
    }


    /**
     * Checks if AddressLine1 is set
     * 
     * @return bool true if AddressLine1  is set
     */
    public function isSetAddressLine1()
    {
        return !is_null($this->_fields['AddressLine1']['FieldValue']);
    }

    /**
     * Gets the value of the AddressLine2 property.
     * 
     * @return string AddressLine2
     */
    public function getAddressLine2() 
    {
        return $this->_fields['AddressLine2']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLine2 property.
     * 
     * @param string AddressLine2
     * @return this instance
     */
    public function setAddressLine2($value) 
    {
        $this->_fields['AddressLine2']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AddressLine2 and returns this instance
     * 
     * @param string $value AddressLine2
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withAddressLine2($value)
    {
        $this->setAddressLine2($value);
        return $this;
    }


    /**
     * Checks if AddressLine2 is set
     * 
     * @return bool true if AddressLine2  is set
     */
    public function isSetAddressLine2()
    {
        return !is_null($this->_fields['AddressLine2']['FieldValue']);
    }

    /**
     * Gets the value of the AddressLine3 property.
     * 
     * @return string AddressLine3
     */
    public function getAddressLine3() 
    {
        return $this->_fields['AddressLine3']['FieldValue'];
    }

    /**
     * Sets the value of the AddressLine3 property.
     * 
     * @param string AddressLine3
     * @return this instance
     */
    public function setAddressLine3($value) 
    {
        $this->_fields['AddressLine3']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AddressLine3 and returns this instance
     * 
     * @param string $value AddressLine3
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withAddressLine3($value)
    {
        $this->setAddressLine3($value);
        return $this;
    }


    /**
     * Checks if AddressLine3 is set
     * 
     * @return bool true if AddressLine3  is set
     */
    public function isSetAddressLine3()
    {
        return !is_null($this->_fields['AddressLine3']['FieldValue']);
    }

    /**
     * Gets the value of the City property.
     * 
     * @return string City
     */
    public function getCity() 
    {
        return $this->_fields['City']['FieldValue'];
    }

    /**
     * Sets the value of the City property.
     * 
     * @param string City
     * @return this instance
     */
    public function setCity($value) 
    {
        $this->_fields['City']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the City and returns this instance
     * 
     * @param string $value City
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withCity($value)
    {
        $this->setCity($value);
        return $this;
    }


    /**
     * Checks if City is set
     * 
     * @return bool true if City  is set
     */
    public function isSetCity()
    {
        return !is_null($this->_fields['City']['FieldValue']);
    }

    /**
     * Gets the value of the County property.
     * 
     * @return string County
     */
    public function getCounty() 
    {
        return $this->_fields['County']['FieldValue'];
    }

    /**
     * Sets the value of the County property.
     * 
     * @param string County
     * @return this instance
     */
    public function setCounty($value) 
    {
        $this->_fields['County']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the County and returns this instance
     * 
     * @param string $value County
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withCounty($value)
    {
        $this->setCounty($value);
        return $this;
    }


    /**
     * Checks if County is set
     * 
     * @return bool true if County  is set
     */
    public function isSetCounty()
    {
        return !is_null($this->_fields['County']['FieldValue']);
    }

    /**
     * Gets the value of the District property.
     * 
     * @return string District
     */
    public function getDistrict() 
    {
        return $this->_fields['District']['FieldValue'];
    }

    /**
     * Sets the value of the District property.
     * 
     * @param string District
     * @return this instance
     */
    public function setDistrict($value) 
    {
        $this->_fields['District']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the District and returns this instance
     * 
     * @param string $value District
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withDistrict($value)
    {
        $this->setDistrict($value);
        return $this;
    }


    /**
     * Checks if District is set
     * 
     * @return bool true if District  is set
     */
    public function isSetDistrict()
    {
        return !is_null($this->_fields['District']['FieldValue']);
    }

    /**
     * Gets the value of the StateOrRegion property.
     * 
     * @return string StateOrRegion
     */
    public function getStateOrRegion() 
    {
        return $this->_fields['StateOrRegion']['FieldValue'];
    }

    /**
     * Sets the value of the StateOrRegion property.
     * 
     * @param string StateOrRegion
     * @return this instance
     */
    public function setStateOrRegion($value) 
    {
        $this->_fields['StateOrRegion']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the StateOrRegion and returns this instance
     * 
     * @param string $value StateOrRegion
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withStateOrRegion($value)
    {
        $this->setStateOrRegion($value);
        return $this;
    }


    /**
     * Checks if StateOrRegion is set
     * 
     * @return bool true if StateOrRegion  is set
     */
    public function isSetStateOrRegion()
    {
        return !is_null($this->_fields['StateOrRegion']['FieldValue']);
    }

    /**
     * Gets the value of the PostalCode property.
     * 
     * @return string PostalCode
     */
    public function getPostalCode() 
    {
        return $this->_fields['PostalCode']['FieldValue'];
    }

    /**
     * Sets the value of the PostalCode property.
     * 
     * @param string PostalCode
     * @return this instance
     */
    public function setPostalCode($value) 
    {
        $this->_fields['PostalCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the PostalCode and returns this instance
     * 
     * @param string $value PostalCode
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withPostalCode($value)
    {
        $this->setPostalCode($value);
        return $this;
    }


    /**
     * Checks if PostalCode is set
     * 
     * @return bool true if PostalCode  is set
     */
    public function isSetPostalCode()
    {
        return !is_null($this->_fields['PostalCode']['FieldValue']);
    }

    /**
     * Gets the value of the CountryCode property.
     * 
     * @return string CountryCode
     */
    public function getCountryCode() 
    {
        return $this->_fields['CountryCode']['FieldValue'];
    }

    /**
     * Sets the value of the CountryCode property.
     * 
     * @param string CountryCode
     * @return this instance
     */
    public function setCountryCode($value) 
    {
        $this->_fields['CountryCode']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CountryCode and returns this instance
     * 
     * @param string $value CountryCode
     * @return OffAmazonPaymentsService_Model_Address instance
     */
    public function withCountryCode($value)
    {
        $this->setCountryCode($value);
        return $this;
    }


    /**
     * Checks if CountryCode is set
     * 
     * @return bool true if CountryCode  is set
     */
    public function isSetCountryCode()
    {
        return !is_null($this->_fields['CountryCode']['FieldValue']);
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
     * @return OffAmazonPaymentsService_Model_Address instance
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