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

require_once 'OffAmazonPayments/Model.php';
require_once 'OffAmazonPaymentsNotifications/Notification.php';
require_once 'OffAmazonPaymentsNotifications/NotificationMetadata.php';
require_once 'OffAmazonPaymentsNotifications/Impl/Message.php';

/**
 * NotificationsImpl class, contains common functionality for
 * an implementation of the notification interface
 *
 */
abstract class OffAmazonPaymentsNotifications_NotificationImpl
    extends OffAmazonPayments_Model
    implements OffAmazonPaymentsNotifications_Notification
{
    /**
     * Metadata about the notification request 
     */
    private $_notificationMetadata;
    
    /**
     * Notification type
     */
    private $_notificationType;
    
    /**
     * Construct new instance of the object, mapping the xml to the fields
     * defined in the child of this class, and extracting the request
     * metadata for this object
     *
     * @param OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata metadata for message 
     *                                                                                  containing the
     *                                                                                  common notification 
     *                                                                                  fields
     * @param string                                              $notificationType     type of notification
     * @param mixed                                               $data                 DOMElement or
     *                                                                                  Associative Array 
     *                                                                                  to construct from
     *
     * @return new instance
     */
    public function __construct(
        OffAmazonPaymentsNotifications_NotificationMetadata $notificationMetadata,
        $notificationType,
        $data = null      
    ) {
        $this->_notificationMetadata = $notificationMetadata;
        $this->_notificationType = $notificationType;
        parent::__construct($data);
    }
    
    /**
     * Return the type of notification to determine
     * what the implementation type is
     * 
     * @return string notification type
     */
    public function getNotificationType()
    {
        return $this->_notificationType;
    }
    
    /**
     * Return the metadata for the ipn notification
     * 
     * @return NotificationMetadata notificationMetadata
     */
    public function getNotificationMetadata()
    {
        return $this->_notificationMetadata;
    }
}
?>