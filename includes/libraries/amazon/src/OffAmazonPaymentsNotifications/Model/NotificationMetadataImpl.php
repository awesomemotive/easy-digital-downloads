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

require_once 'OffAmazonPaymentsNotifications/NotificationMetadata.php';

/**
 * Common functions for classes that extend the message information
 * class
 * 
 */
abstract class OffAmazonPaymentsNotifications_Model_NotificationMetadataImpl
    implements OffAmazonPaymentsNotifications_NotificationMetadata
{
    /**
     * Parent message if applicable, null otherwise
     *
     * @var object
     */
    private $_parentNotificationMetadata = null; 
    
    /**
     * Construct a new instance of the message
     * information implementation class
     * 
     * @param NotificationMetadata $parentNotificationMetadata parent message 
     *                                                         information null if 
     *                                                         not applicable
     * 
     * @return new instance of the concreate class
     */
    public function __construct($parentNotificationMetadata = null)
    {
        $this->_parentNotificationMetadata = $parentNotificationMetadata;
    }
    
    /**
     * Returns the parent message information type
     * or null if not defined
     *
     * @return NotificationMetadata message information type
     */
    public function getParentNotificationMetadata()
    {
        return $this->_parentNotificationMetadata;
    }   
    
    /**
     * Returns true if a parent notification is defined
     * 
     * @return true if parent notification is defined
     */
    public function hasParentNotificationMetadata()
    {
        return !is_null($this->_parentNotificationMetadata);
    }
}
?>