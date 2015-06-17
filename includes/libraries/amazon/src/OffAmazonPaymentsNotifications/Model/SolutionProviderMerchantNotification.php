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
 * OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification
 * 
 * Properties:
 * <ul>
 * 
 * <li>MerchantRegistrationDetails: OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification 
    extends OffAmazonPaymentsNotifications_NotificationImpl
{
    /**
     * Construct new OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for request
     * @param mixed                                               $data                 DOMElement or Associative Array 
     *                                                                                  to construct from.
     *
     * Valid properties:
     * <ul>
     *
     * <li>MerchantRegistrationDetails: OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails</li>
     *
     * </ul>
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata, 
        $data = null
    ) {
        $this->fields = array (
                'MerchantRegistrationDetails' => array(
                    'FieldValue' => null, 
                    'FieldType' => 
                        'OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails'
                )
        );
        parent::__construct(
            $notificationMetadata,
            "SolutionProviderMerchantNotification", 
            $data
        );
    }
    
    /**
     * Construct OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification 
     * from XML string
     *
     * @param string $xml XML string to construct from
     * 
     * @return OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification
     */
    public static function fromXML($xml)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('a', self::getNamespace());
        $response = $xpath->query('//a:SolutionProviderMerchantNotification');
        if ($response->length == 1) {
            return 
                new OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification(
                    $response->item(0)
                );
        } else {
            throw new Exception(
                "Unable to construct " .
                "OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification" .
                "from provided XML. Make sure that SolutionProviderMerchantNotification" .
                "is a root element"
            );
        }
    }
    
    /**
     * Gets the value of the SolutionProviderMerchantNotification.
     *
     * @return OrderReference property value
     */
    public function getMerchantRegistrationDetails()
    {
        return $this->fields['MerchantRegistrationDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the MerchantRegistrationDetails.
     *
     * @param OrderReference $value new value
     * 
     * @return void
     */
    public function setMerchantRegistrationDetails($value)
    {
        $this->fields['MerchantRegistrationDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the MerchantRegistrationDetails 
     * and returns this instance
     *
     * @param MerchantRegistrationDetails $value MerchantRegistrationDetails
     * 
     * @return OffAmazonPaymentsServices_Model_MerchantRegistrationDetails instance
     */
    public function withMerchantRegistrationDetails($value)
    {
        $this->setMerchantRegistrationDetails($value);
        return $this;
    }
    
    /**
     * Checks if MerchantRegistrationDetails is set
     *
     * @return bool true if MerchantRegistrationDetails property is set
     */
    public function isSetMerchantRegistrationDetails()
    {
        return !is_null($this->fields['MerchantRegistrationDetails']['FieldValue']);
    }
    
    /**
     * XML Representation for this object
     *
     * @return string XML for this object
     */
    public function toXML()
    {
        $xml = "";
        $xml .= "<SolutionProviderMerchantNotification xmlns=\"";
        $xml .= self::getNamespace();
        $xml .= "\">";
        $xml .= $this->_toXMLFragment();
        $xml .= "</SolutionProviderMerchantNotification>";
        return $xml;
    }
}
?>