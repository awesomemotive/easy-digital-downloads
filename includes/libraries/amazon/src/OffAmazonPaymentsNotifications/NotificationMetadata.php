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

/**
 * Interface to wrap an notification message metadata
 * 
 */
interface OffAmazonPaymentsNotifications_NotificationMetadata
{
    /**
     * Return the implementation type of NotificationMetadata
     *
     * @return string type of message information
     */
    public function getNotificationMetadataType();
    
    /**
     * Returns the parent message information type
     * or null if not defined
     *
     * @return NotificationMetadata message information type
     */
    public function getParentNotificationMetadata();
    
    /**
     * Returns true if a parent notification is defined
     * 
     * @return true if parent notification is defined
     */
    public function hasParentNotificationMetadata();
}
?>