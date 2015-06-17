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
 * OffAmazonPaymentsNotifications_Model_RefundNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>RefundDetails: OffAmazonPaymentsNotifications_Model_RefundDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_RefundNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_RefundNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>RefundDetails: OffAmazonPaymentsNotifications_Model_RefundDetails</li>
     *
     * </ul>
     */
    public function __construct($notificationMetadata, $data = null)
    {
        $this->fields = array (
                'RefundDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_RefundDetails'
                )
        );
        parent::__construct(
            $notificationMetadata, 
            "RefundNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_RefundNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_RefundNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:RefundNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_RefundNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_RefundNotification" .
                "from provided XML. Make sure that RefundNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the RefundNotification.
     *
     * @return OrderReference property value
     */
    public function getRefundDetails()
    {
        return $this->fields['RefundDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the RefundDetails.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setRefundDetails($value)
    {
        $this->fields['RefundDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the RefundDetails 
     * and returns this instance
     *
     * @param RefundDetails $value RefundDetails
     * 
     * @return OffAmazonPaymentsServices_Model_RefundDetails instance
     */
    public function withRefundDetails($value)
    {
        $this->setRefundDetails($value);
        return $this;
    }
    
    /**
     * Checks if RefundDetails is set
     *
     * @return bool true if RefundDetails property is set
     */
    public function isSetRefundDetails()
    {
        return !is_null($this->fields['RefundDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<RefundNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</RefundNotification>";
        return $xml;
    }
}
?>