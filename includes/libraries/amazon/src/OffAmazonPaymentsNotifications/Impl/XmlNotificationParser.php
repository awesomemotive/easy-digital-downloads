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

require_once 'OffAmazonPaymentsNotifications/Impl/Message.php';
require_once 'OffAmazonPaymentsNotifications/Model/AuthorizationNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreementNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/RefundNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/CaptureNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderReferenceNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/SolutionProviderMerchantNotification.php';


/**
 * Wrapper around a decoded IPN notification message to create
 * the notification
 */
class XmlNotificationParser
{
    /**
     * Converts a ipn message into a
     * notification object
     * 
     * @param JsonMesssage $ipnMsg ipnMessage
     * 
     * @throws OffAmazonPaymentsNotifications if there is an error
     * 
     * @return Message
     */
    public static function parseIpnMessage(Message $ipnMsg)
    {
        $xmlDocumentElement = self::_getXmlFromIpnMessage($ipnMsg);
        return self::_createNotificationForNotificationType(
            $ipnMsg,
            $xmlDocumentElement
        );
    }
    
    /**
     * Convert the xml message from the ipn payload 
     * into an xml document
     * 
     * @param Message $ipnMsg ipn message
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return XmlElement xml document element
     */
    private static function _getXmlFromIpnMessage(Message $ipnMsg)
    {        
        // Try and load the notification data as xml
        $notificationData = $ipnMsg->getMandatoryField("NotificationData");
        
        $dom = new DOMDocument();
        try {
            $dom->loadXML($notificationData);
        } catch (Exception $ex) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with ipn message - NotificationData field does not contain xml, " .
                "contents: " . $notificationData
            );
        }
        
        return $dom->documentElement;
    }
    
    
    
    /**
     * Return a notification object initialised by the xml
     * 
     * @param Message $ipnMsg             ipn message
     * @param XmlNode $xmlDocumentElement xml message
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return OffAmazonPaymentsNotifications_Notification
     */
    private static function _createNotificationForNotificationType(
        Message $ipnMsg,
        $xmlDocumentElement
    ) {           
        // Construct an instance of the notification class
        switch ($ipnMsg->getMandatoryField("NotificationType")) {
        case "OrderReferenceNotification":
            $notification
                = new OffAmazonPaymentsNotifications_Model_OrderReferenceNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "BillingAgreementNotification":
            $notification
                = new OffAmazonPaymentsNotifications_Model_BillingAgreementNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "PaymentAuthorize":
            $notification
                = new OffAmazonPaymentsNotifications_Model_AuthorizationNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "PaymentCapture":
            $notification
                = new OffAmazonPaymentsNotifications_Model_CaptureNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "PaymentRefund":
            $notification
                = new OffAmazonPaymentsNotifications_Model_RefundNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "ProviderCredit":
            $notification
                = new OffAmazonPaymentsNotifications_Model_ProviderCreditNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "ProviderCreditReversal":
            $notification
                = new OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;
        case "SolutionProviderEvent":
            $notification
                = new OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification(
                    $ipnMsg->getNotificationMetadata(),
                    $xmlDocumentElement
                );
            break;

        default:
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with IPN notification - unknown notification " .
                $ipnMsg->getMandatoryField("NotificationType")
            );
        }
        
        return $notification;
    }
}
?>