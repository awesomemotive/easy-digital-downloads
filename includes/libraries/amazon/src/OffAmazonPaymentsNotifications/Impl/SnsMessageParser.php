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
require_once 'OffAmazonPaymentsNotifications/Model/SnsNotificationMetadata.php';

/**
 * Parses a json string message into an sns message
 */
class SnsMessageParser
{

    /**
     * Convert a json string to a json msg that
     * meets our expections of a SnsMessage
     *
     * @param string $jsonString raw data as json string
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if not valid json
     *
     * @return Message converted message
     */
    public static function parseNotification($headers, $jsonString)
    {
        self::_validateHeaders($headers);
        $snsMsg = new Message($jsonString);
        self::_checkForCorrectMessageType($snsMsg);
        self::_setMetadataForMessage($snsMsg);
        return $snsMsg;
    }
    
    /**
     * Validate the sns header fields to see if we should
     * handle this message
     * 
     * @param array $headers array of headers, organised in key, value pairs
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return void
     */
    private static function _validateHeaders($headers)
    {
        // Quickly check that this is a sns message
        if (!array_key_exists('x-amz-sns-message-type', $headers)) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with message - header " .
                "does not contain x-amz-sns-message-type header"
            );
        }
        
        if ($headers['x-amz-sns-message-type'] !== 'Notification') {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with message - header x-amz-sns-message-type is not " .
                "Notification, is " . $headers['x-amz-sns-message-type']
            );
        }
    }

    /**
     * Assert that the SNS message type is notification
     *
     * @param message $snsJsonMsg convertedJsonMsg
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if not correct type
     *
     * @return void if correct type
     */
    private static function _checkForCorrectMessageType($snsJsonMsg)
    {
        $type = $snsJsonMsg->getMandatoryField("Type");
        if (strcasecmp($type, "Notification") != 0) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with SNS Notification - unexpected message with Type of " .
                $type
            );
        }
    }

    /**
     * Generate an sns metadata instance based on the values
     * in the sns message
     *
     * @param message $snsJsonMsg convertedJsonMsg to attach the metadata to
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if not correct type
     *
     * @return void
     */
    private static function _setMetadataForMessage(Message $snsMsg)
    {
        $notificationMetadata 
            = new OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata($snsMsg);
        $snsMsg->setNotificationMetadata($notificationMetadata);
    }
}
?>