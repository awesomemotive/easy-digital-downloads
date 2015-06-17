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
require_once 'OffAmazonPaymentsService/Model.php';

/**
 * OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse
 * 
 * Properties:
 * <ul>
 * 
 * <li>ValidateBillingAgreementResult: OffAmazonPaymentsService_Model_ValidateBillingAgreementResult</li>
 * <li>ResponseMetadata: OffAmazonPaymentsService_Model_ResponseMetadata</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>ValidateBillingAgreementResult: OffAmazonPaymentsService_Model_ValidateBillingAgreementResult</li>
     * <li>ResponseMetadata: OffAmazonPaymentsService_Model_ResponseMetadata</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            
            'ValidateBillingAgreementResult' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_ValidateBillingAgreementResult'
            ),
            
            'ResponseMetadata' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_ResponseMetadata'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Construct OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse from XML string
     * 
     * @param string $xml XML string to construct from
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse 
     */
    public static function fromXML ($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', 
                'http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:ValidateBillingAgreementResponse');
        if ($response->length == 1) {
            return new OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse(
                    ($response->item(0)));
        } else {
            throw new Exception(
                    "Unable to construct OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse from provided XML. 
                                  Make sure that ValidateBillingAgreementResponse is a root element");
        }
    }

    /**
     * Gets the value of the ValidateBillingAgreementResult.
     * 
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResult ValidateBillingAgreementResult
     */
    public function getValidateBillingAgreementResult ()
    {
        return $this->_fields['ValidateBillingAgreementResult']['FieldValue'];
    }

    /**
     * Sets the value of the ValidateBillingAgreementResult.
     * 
     * @param OffAmazonPaymentsService_Model_ValidateBillingAgreementResult ValidateBillingAgreementResult
     * @return void
     */
    public function setValidateBillingAgreementResult ($value)
    {
        $this->_fields['ValidateBillingAgreementResult']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ValidateBillingAgreementResult  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_ValidateBillingAgreementResult $value ValidateBillingAgreementResult
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse instance
     */
    public function withValidateBillingAgreementResult ($value)
    {
        $this->setValidateBillingAgreementResult($value);
        return $this;
    }

    /**
     * Checks if ValidateBillingAgreementResult  is set
     * 
     * @return bool true if ValidateBillingAgreementResult property is set
     */
    public function isSetValidateBillingAgreementResult ()
    {
        return ! is_null($this->_fields['ValidateBillingAgreementResult']['FieldValue']);
    }

    /**
     * Gets the value of the ResponseMetadata.
     * 
     * @return OffAmazonPaymentsService_Model_ResponseMetadata ResponseMetadata
     */
    public function getResponseMetadata ()
    {
        return $this->_fields['ResponseMetadata']['FieldValue'];
    }

    /**
     * Sets the value of the ResponseMetadata.
     * 
     * @param OffAmazonPaymentsService_Model_ResponseMetadata ResponseMetadata
     * @return void
     */
    public function setResponseMetadata ($value)
    {
        $this->_fields['ResponseMetadata']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the ResponseMetadata  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_ResponseMetadata $value ResponseMetadata
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse instance
     */
    public function withResponseMetadata ($value)
    {
        $this->setResponseMetadata($value);
        return $this;
    }

    /**
     * Checks if ResponseMetadata  is set
     * 
     * @return bool true if ResponseMetadata property is set
     */
    public function isSetResponseMetadata ()
    {
        return ! is_null($this->_fields['ResponseMetadata']['FieldValue']);
    }

    /**
     * XML Representation for this object
     * 
     * @return string XML for this object
     */
    public function toXML ()
    {
        $xml = "";
        $xml .= "<ValidateBillingAgreementResponse xmlns=\"http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</ValidateBillingAgreementResponse>";
        return $xml;
    }

    private $_responseHeaderMetadata = null;

    public function getResponseHeaderMetadata ()
    {
        return $this->_responseHeaderMetadata;
    }

    public function setResponseHeaderMetadata ($responseHeaderMetadata)
    {
        return $this->_responseHeaderMetadata = $responseHeaderMetadata;
    }
}
?>