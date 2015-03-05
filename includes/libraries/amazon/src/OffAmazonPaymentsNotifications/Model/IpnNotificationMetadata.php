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

require_once 'OffAmazonPaymentsNotifications/Model/NotificationMetadataImpl.php';

/**
 * IPN Message information
 */
class OffAmazonPaymentsNotifications_Model_IPNNotificationMetadata 
    extends OffAmazonPaymentsNotifications_Model_NotificationMetadataImpl
{
    /**
     * Timestamp for when this notification was generated
     *
     * @var string
     */
    private $_timestamp = null;
    
    /**
     * Environment in which this notification was sent from
     *
     * @var string
     */
    private $_releaseEnvironment = null;
    
    /**
     * Identification for the reference id
     *
     * @var string
     */
    private $_notificationReferenceId = null;
    
    /**
     * Type of source message
     * 
     * @string
     */
    const SOURCE_MESSAGE_TYPE = "IPN";
    
    /**
     * Creates a new instance of the IPNNotificationMetadata
     * object, initialized from the Message
     * 
     * @param message                                             $message                    message for IPN
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $parentNotificationMetadata parent message if 
     *                                                                                        applicable otherwise 
     *                                                                                        null
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException invalid message
     * 
     * @return void
     */
    public function __construct(
        Message $message, 
        OffAmazonPaymentsNotifications_NotificationMetadata $parentNotificationMetadata = null
    ) {
        $this->_timestamp 
            = $message->getMandatoryField("Timestamp");
        $this->_releaseEnvironment
            = $message->getMandatoryField("ReleaseEnvironment");
        $this->_notificationReferenceId
            = $message->getMandatoryField("NotificationReferenceId");
        parent::__construct($parentNotificationMetadata);
    }
    
    
    /**
     * Message timestamp
     *
     * @return string timestamp
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }
    
    /**
     * Notification Reference identifier
     * 
     * @return string notification reference id
     */
    public function getNotificationReferenceId()
    {
        return $this->_notificationReferenceId;
    }
    
    /**
     * Release envionment of this notification
     * Can be sandbox or live
     * 
     * @return string release environment name
     */
    public function getReleaseEnvironment()
    {
        return $this->_releaseEnvironment;    
    }
    
    /**
     * Return the implementation type of NotificationMetadata
     * 
     * @return string type of message information
     */
    public function getNotificationMetadataType()
    {
        return self::SOURCE_MESSAGE_TYPE;
    }
}
?>