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
 * OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
 * 
 * Properties:
 * <ul>
 * 
 * <li>AuthorizeOnBillingAgreementResult: OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult</li>
 * <li>ResponseMetadata: OffAmazonPaymentsService_Model_ResponseMetadata</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AuthorizeOnBillingAgreementResult: OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult</li>
     * <li>ResponseMetadata: OffAmazonPaymentsService_Model_ResponseMetadata</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            
            'AuthorizeOnBillingAgreementResult' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult'
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
     * Construct OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse from XML string
     * 
     * @param string $xml XML string to construct from
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse AuthorizeOnBillingAgreementResponse
     */
    public static function fromXML ($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', 
                'http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01');
        $response = $xpath->query('//a:AuthorizeOnBillingAgreementResponse');
        if ($response->length == 1) {
            return new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse(
                    ($response->item(0)));
        } else {
            throw new Exception(
                    "Unable to construct OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse from provided XML. 
                                  Make sure that AuthorizeOnBillingAgreementResponse is a root element");
        }
    }

    /**
     * Gets the value of the AuthorizeOnBillingAgreementResult.
     * 
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult AuthorizeOnBillingAgreementResult
     */
    public function getAuthorizeOnBillingAgreementResult ()
    {
        return $this->_fields['AuthorizeOnBillingAgreementResult']['FieldValue'];
    }

    /**
     * Sets the value of the AuthorizeOnBillingAgreementResult.
     * 
     * @param OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult $value
     * @return void
     */
    public function setAuthorizeOnBillingAgreementResult ($value)
    {
        $this->_fields['AuthorizeOnBillingAgreementResult']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AuthorizeOnBillingAgreementResult  and returns this instance
     * 
     * @param OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResult $value
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse instance
     */
    public function withAuthorizeOnBillingAgreementResult ($value)
    {
        $this->setAuthorizeOnBillingAgreementResult($value);
        return $this;
    }

    /**
     * Checks if AuthorizeOnBillingAgreementResult  is set
     * 
     * @return bool true if AuthorizeOnBillingAgreementResult property is set
     */
    public function isSetAuthorizeOnBillingAgreementResult ()
    {
        return ! is_null($this->_fields['AuthorizeOnBillingAgreementResult']['FieldValue']);
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
     * @param OffAmazonPaymentsService_Model_ResponseMetadata $value
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
     * @param OffAmazonPaymentsService_Model_ResponseMetadata $value
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse instance
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
        $xml .= "<AuthorizeOnBillingAgreementResponse xmlns=\"http://mws.amazonservices.com/schema/OffAmazonPayments/2013-01-01\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</AuthorizeOnBillingAgreementResponse>";
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