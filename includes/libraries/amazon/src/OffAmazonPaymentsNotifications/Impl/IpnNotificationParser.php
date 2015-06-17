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
require_once 'OffAmazonPaymentsNotifications/Model/IpnNotificationMetadata.php';

/**
 * Wrapper around a decoded IPN notification message
 * in json format
 */
class IpnNotificationParser
{
    /**
     * Converts a an sns message into a
     * ipn notification object
     *
     * @param Messsage $snsMsg snsMessage
     *
     * @throws OffAmazonPaymentsNotifications if there is an error
     *
     * @return Message ipn message
     */
    public static function parseSnsMessage(Message $snsMsg)
    {
        // Create the message and extract the information we need
        $ipnMsg = new Message($snsMsg->getMandatoryField("Message"));
        self::_addMetadataToIpnMessage(
            $ipnMsg, 
            $snsMsg->getNotificationMetadata()
        );
        return $ipnMsg;
    }
    
    /**
     * Create the metadata object for the ipn message and attach
     * to the object instance
     *
     * @param Message                                             $ipnMsg          ipn message
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $messageMetadata parent notification
     *
     * @return void
     */
    private static function _addMetadataToIpnMessage (
        Message $ipnMsg,
        OffAmazonPaymentsNotifications_NotificationMetadata $messageMetadata = null
    ) {
        $ipnMetadata
            = new OffAmazonPaymentsNotifications_Model_IPNNotificationMetadata(
                $ipnMsg,
                $messageMetadata
            );
        $ipnMsg->setNotificationMetadata($ipnMetadata);
    }
}
?>