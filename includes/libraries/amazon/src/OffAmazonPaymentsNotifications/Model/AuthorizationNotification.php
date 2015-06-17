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
 * OffAmazonPaymentsNotifications_Model_AuthorizationNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>AuthorizationDetails: 
 *     OffAmazonPaymentsNotifications_Model_AuthorizationDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_AuthorizationNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_AuthorizationNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     * 
     * Valid properties:
     * <ul>
     *
     * <li>AuthorizationDetails: 
     *     OffAmazonPaymentsNotifications_Model_AuthorizationDetails</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata,
        $data = null
    ) {
        $this->fields = array (
                'AuthorizationDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_AuthorizationDetails'
                )
        );
        parent::__construct(
            $notificationMetadata, 
            "AuthorizationNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_AuthorizationNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_AuthorizationNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:AuthorizationNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_AuthorizationNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_AuthorizationNotification" .
                "from provided XML. Make sure that AuthorizationNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the AuthorizationNotification.
     *
     * @return AuthorizationDetails property value
     */
    public function getAuthorizationDetails()
    {
        return $this->fields['AuthorizationDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the AuthorizationDetails.
     *
     * @param AuthorizationDetails $value new value
     * 
     * @return void
     */
    public function setAuthorizationDetails($value)
    {
        $this->fields['AuthorizationDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the AuthorizationDetails 
     * and returns this instance
     *
     * @param AuthorizationDetails $value AuthorizationDetails
     * 
     * @return OffAmazonPaymentsServices_Model_AuthorizationDetails instance
     */
    public function withAuthorizationDetails($value)
    {
        $this->setAuthorizationDetails($value);
        return $this;
    }
    
    /**
     * Checks if AuthorizationDetails is set
     *
     * @return bool true if AuthorizationDetails property is set
     */
    public function isSetAuthorizationDetails()
    {
        return !is_null($this->fields['AuthorizationDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<AuthorizationNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</AuthorizationNotification>";
        return $xml;
    }
}
?>