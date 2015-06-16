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
 * SNS Message information
 */
class OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata 
    extends OffAmazonPaymentsNotifications_Model_NotificationMetadataImpl
{
    /**
     * Timestamp for when this notification was generated
     *
     * @var string
     */
    private $_timestamp = null;
    
    /**
     * Topic that the notification was generated from
     *
     * @var string
     */
    private $_topicArn = null;
    
    /**
     * Message id
     *
     * @var string
     */
    private $_messageId = null;
    
    /**
     * Type of source message
     * 
     * @string
     */
    const SOURCE_MESSAGE_TYPE = "SNS";
    
    /**
     * Creates a new instance of the SnsNotificationMetadata
     * object, initialized from the Message
     * 
     * @param message              $message                    message for SNS
     * @param NotificationMetadata $parentNotificationMetadata parent message if 
     *                                                         applicable otherwise 
     *                                                         null
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException invalid message
     * 
     * @return void
     */
    public function __construct(
        Message $message, 
        NotificationMetadata $parentNotificationMetadata = null
    ) {
        $this->_timestamp 
            = $message->getMandatoryField("Timestamp");
        $this->_topicArn
            = $message->getMandatoryField("TopicArn");
        $this->_messageId
            = $message->getMandatoryField("MessageId");
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
     * Topic arn
     * 
     * @return string topic arn
     */
    public function getTopicArn()
    {
        return $this->_topicArn;
    }
    
    /**
     * Message id
     * 
     * @return string messageId
     */
    public function getMessageId()
    {
        return $this->_messageId;    
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