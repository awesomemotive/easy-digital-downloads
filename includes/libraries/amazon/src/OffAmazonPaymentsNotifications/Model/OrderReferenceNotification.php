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
 * OffAmazonPaymentsNotifications_Model_OrderReferenceNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderReference: OffAmazonPaymentsNotifications_Model_OrderReference</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_OrderReferenceNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_OrderReferenceNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>OrderReference: OffAmazonPaymentsNotifications_Model_OrderReference</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, 
        $data = null
    ) {
        $this->fields = array (
                'OrderReference' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_OrderReference'
                )
        );
        parent::__construct(
            $notificationMetadata, 
            "OrderReferenceNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_OrderReferenceNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_OrderReferenceNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:OrderReferenceNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_OrderReferenceNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_OrderReferenceNotification" .
                "from provided XML. Make sure that OrderReferenceNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the OrderReference.
     *
     * @return OrderReference property value
     */
    public function getOrderReference()
    {
        return $this->fields['OrderReference']['FieldValue'];
    }
    
    /**
     * Sets the value of the OrderReference.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setOrderReference($value)
    {
        $this->fields['OrderReference']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the OrderReference 
     * and returns this instance
     *
     * @param OrderReferenceNotification $value OrderReference
     * 
     * @return OffAmazonPaymentsServices_Model_OrderReferenceNotification instance
     */
    public function withOrderReference($value)
    {
        $this->setOrderReference($value);
        return $this;
    }
    
    /**
     * Checks if OrderReference is set
     *
     * @return bool true if OrderReference property is set
     */
    public function isSetOrderReference()
    {
        return !is_null($this->fields['OrderReference']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<OrderReferenceNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</OrderReferenceNotification>";
        return $xml;
    }
}
?>