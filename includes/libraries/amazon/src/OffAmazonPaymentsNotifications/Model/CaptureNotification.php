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

require_once 'OffAmazonPaymentsNotifications/Model/NotificationImpl.php';
require_once 'OffAmazonPaymentsNotifications/NotificationMetadata.php';

/**
 * OffAmazonPaymentsNotifications_Model_CaptureNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>CaptureDetails: OffAmazonPaymentsNotifications_Model_CaptureDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_CaptureNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_CaptureNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>CaptureDetails: OffAmazonPaymentsNotifications_Model_CaptureDetails</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, 
        $data = null
    ) {
        $this->fields = array (
                'CaptureDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_CaptureDetails'
                )
        );
       
        parent::__construct(
            $notificationMetadata,
            "CaptureNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_CaptureNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_CaptureNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:CaptureNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_CaptureNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_CaptureNotification" .
                "from provided XML. Make sure that CaptureNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the CaptureNotification.
     *
     * @return OrderReference property value
     */
    public function getCaptureDetails()
    {
        return $this->fields['CaptureDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the CaptureDetails.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setCaptureDetails($value)
    {
        $this->fields['CaptureDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the CaptureDetails 
     * and returns this instance
     *
     * @param CaptureDetails $value CaptureDetails
     * 
     * @return OffAmazonPaymentsServices_Model_CaptureDetails instance
     */
    public function withCaptureDetails($value)
    {
        $this->setCaptureDetails($value);
        return $this;
    }
    
    /**
     * Checks if CaptureDetails is set
     *
     * @return bool true if CaptureDetails property is set
     */
    public function isSetCaptureDetails()
    {
        return !is_null($this->fields['CaptureDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<CaptureNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</CaptureNotification>";
        return $xml;
    }
}
?>