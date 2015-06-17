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
 * OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>ProviderCreditReversalDetails: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>ProviderCreditReversalDetails: OffAmazonPaymentsNotifications_Model_ProviderCreditReversalDetails</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, 
        $data = null
    ) {
        $this->fields = array (
                'ProviderCreditReversalDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_ProviderCreditReversalDetails'
                )
        );
        parent::__construct(
            $notificationMetadata,
            "ProviderCreditReversalNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:ProviderCreditReversalNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification" .
                "from provided XML. Make sure that ProviderCreditReversalNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the ProviderCreditReversalNotification.
     *
     * @return OrderReference property value
     */
    public function getProviderCreditReversalDetails()
    {
        return $this->fields['ProviderCreditReversalDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the ProviderCreditReversalDetails.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setProviderCreditReversalDetails($value)
    {
        $this->fields['ProviderCreditReversalDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the ProviderCreditReversalDetails 
     * and returns this instance
     *
     * @param ProviderCreditReversalDetails $value ProviderCreditReversalDetails
     * 
     * @return OffAmazonPaymentsServices_Model_ProviderCreditReversalDetails instance
     */
    public function withProviderCreditReversalDetails($value)
    {
        $this->setProviderCreditReversalDetails($value);
        return $this;
    }
    
    /**
     * Checks if ProviderCreditReversalDetails is set
     *
     * @return bool true if ProviderCreditReversalDetails property is set
     */
    public function isSetProviderCreditReversalDetails()
    {
        return !is_null($this->fields['ProviderCreditReversalDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<ProviderCreditReversalNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</ProviderCreditReversalNotification>";
        return $xml;
    }
}
?>