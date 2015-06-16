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
 * OffAmazonPaymentsNotifications_Model_ProviderCreditNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderCreditDetails: OffAmazonPaymentsNotifications_Model_ProviderCreditDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_ProviderCreditNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>ProviderCreditDetails: OffAmazonPaymentsNotifications_Model_ProviderCreditDetails</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, 
        $data = null
    ) {
        $this->fields = array (
                'ProviderCreditDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_ProviderCreditDetails'
                )
        );
        parent::__construct(
            $notificationMetadata,
            "ProviderCreditNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_ProviderCreditNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:ProviderCreditNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_ProviderCreditNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_ProviderCreditNotification" .
                "from provided XML. Make sure that ProviderCreditNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the ProviderCreditNotification.
     *
     * @return OrderReference property value
     */
    public function getProviderCreditDetails()
    {
        return $this->fields['ProviderCreditDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditDetails.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setProviderCreditDetails($value)
    {
        $this->fields['ProviderCreditDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the ProviderCreditDetails 
     * and returns this instance
     *
     * @param ProviderCreditDetails $value ProviderCreditDetails
     * 
     * @return OffAmazonPaymentsServices_Model_ProviderCreditDetails instance
     */
    public function withProviderCreditDetails($value)
    {
        $this->setProviderCreditDetails($value);
        return $this;
    }
    
    /**
     * Checks if ProviderCreditDetails is set
     *
     * @return bool true if ProviderCreditDetails property is set
     */
    public function isSetProviderCreditDetails()
    {
        return !is_null($this->fields['ProviderCreditDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<ProviderCreditNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</ProviderCreditNotification>";
        return $xml;
    }
}
?>