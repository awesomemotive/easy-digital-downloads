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
 * OffAmazonPaymentsNotifications_Model_BillingAgreementNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>BillingAgreement: OffAmazonPaymentsNotifications_Model_BillingAgreement</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_BillingAgreementNotification extends OffAmazonPaymentsNotifications_NotificationImpl
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_BillingAgreementNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>BillingAgreement: OffAmazonPaymentsNotifications_Model_BillingAgreement</li>
     *
     * </ul>
     */
    public function __construct (
            OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, $data = null)
    {
        $this->fields = array(
            'BillingAgreement' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_BillingAgreement'
            )
        );
        parent::__construct($notificationMetadata, "BillingAgreementNotification", $data);
    }

    /**
     * Construct OffAmazonPaymentsNotifications_Model_BillingAgreementNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementNotification
     */
    public static function fromXML ($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:BillingAgreementNotification');
        if ($response->length == 1) {
            return new OffAmazonPaymentsNotifications_Model_BillingAgreementNotification(
                    $response->item(0));
        } else {
            throw new Exception(
                    "Unable to construct " .
                             "OffAmazonPaymentsNotifications_Model_BillingAgreementNotification" .
                             "from provided XML. Make sure that BillingAgreementNotification" .
                             "is a root element");
        }
    }

    /**
     * Gets the value of the BillingAgreement.
     *
     * @return BillingAgreement property value
     */
    public function getBillingAgreement ()
    {
        return $this->fields['BillingAgreement']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreement.
     *
     * @param BillingAgreement $value new value
     * 
     * @return void
     */
    public function setBillingAgreement ($value)
    {
        $this->fields['BillingAgreement']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreement 
     * and returns this instance
     *
     * @param BillingAgreementNotification $value BillingAgreement
     * 
     * @return OffAmazonPaymentsServices_Model_BillingAgreementNotification instance
     */
    public function withBillingAgreement ($value)
    {
        $this->setBillingAgreement($value);
        return $this;
    }

    /**
     * Checks if BillingAgreement is set
     *
     * @return bool true if BillingAgreement property is set
     */
    public function isSetBillingAgreement ()
    {
        return ! is_null($this->fields['BillingAgreement']['FieldValue']);
    }

    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML ()
    {
        $xml = "";
        $xml .= "<BillingAgreementNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</BillingAgreementNotification>";
        return $xml;
    }
}
?>