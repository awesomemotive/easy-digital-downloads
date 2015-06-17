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

require_once realpath(dirname(__FILE__) . "/.config.inc.php");
require_once 'OffAmazonPaymentsNotifications/InvalidMessageException.php';
require_once 'OffAmazonPaymentsNotifications/Client.php';
require_once 'OffAmazonPaymentsNotifications/Samples/AuthorizationNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/CaptureNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/RefundNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/OrderReferenceSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/BillingAgreementNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/ProviderCreditNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/ProviderCreditReversalNotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Samples/SolutionProviderMerchantNotificationSample.php';

/**
 * IPN_Handler 
 *
 * This file is invoked whenever a new notification needs to be processed,
 * and will call the IPN API
 *
 * Note that if the IPN Client throws an exception, the IPH_Handler routine is
 * expected to throw a HTTP error response to signal that there has been an issue
 * with the message
 * 
 * This class logs information to an error logs, 
 * and places the last received notification
 * into the session context as a way to pass to other pages
 *
 */ 
$headers = getallheaders();
$body = file_get_contents('php://input');
try {
    $client = new OffAmazonPaymentsNotifications_Client();
    $result = $client->parseRawMessage($headers, $body);
} catch (OffAmazonPaymentsNotifications_InvalidMessageException $ex) {
    error_log($ex->getMessage());
    header("HTTP/1.1 503 Service Unavailable");
    exit(0);
}

try {
    logNotification($result);
} catch (Exception $ex) {
    error_log($ex->getMessage());
}

/**
 * Return the identifier for this notification
 *
 * @param OffAmazonPaymentsNotifications_Notification $notification to extract 
 *                                                                  value from
 * 
 * @return string id string in format notificationType:id
 */
function logNotification(
    OffAmazonPaymentsNotifications_Notification $notification
) {
    if (is_null($notification)) {
        error_log("No notification received");
        return;
    }
    
    $type = $notification->getNotificationType();
    switch($type) {
    case "OrderReferenceNotification":
        error_log("Received Order Reference Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_OrderReferenceSample($notification);
        error_log("Order Reference Notification Logged");
        break;
    case "BillingAgreementNotification":
        error_log("Received Billing Agreement Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_BillingAgreementNotificationSample($notification);
        error_log("Billing Agreement Notification Logged");
        break;
    case "AuthorizationNotification":
        error_log("Received Auth notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_AuthorizationNotificationSample($notification);
        error_log("Auth notification logged");
        break;
    case "CaptureNotification":
        error_log("Received Capture Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_CaptureNotificationSample($notification);
        error_log("Capture Notification logged");
        break;
    case "RefundNotification":
        error_log("Received Refund Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_RefundNotificationSample($notification);
        error_log("Refund Notification logged");
        break;
    case "ProviderCreditNotification":
        error_log("Received ProviderCredit Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_ProviderCreditNotificationSample($notification);
        error_log("ProviderCredit Notification logged");
        break;
    case "ProviderCreditReversalNotification":
        error_log("Received ProviderCreditReversal Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_ProviderCreditReversalNotificationSample($notification);
        error_log("ProviderCreditReversal Notification logged");
        break;
    case "SolutionProviderMerchantNotification":
        error_log("Received SolutionProviderMerchant Notification");
        $obj = new OffAmazonPaymentsNotifications_Samples_SolutionProviderMerchantNotificationSample($notification);
        error_log("SolutionProviderMerchant Notification logged");
        break;
    default:
        error_log("Received Unknown Notification Type: ".$notification->getNotificationType());
        return "Unknown Notification Type";
    }
    
    $obj->logNotification();
    error_log("*********************************************************************************");
    error_log("IPN Logging Completed for: ".$type);
    error_log("*********************************************************************************");
}
?>