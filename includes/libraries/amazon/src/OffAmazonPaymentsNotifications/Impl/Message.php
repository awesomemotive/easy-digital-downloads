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

require_once 'OffAmazonPaymentsNotifications/InvalidMessageException.php';

/**
 * Class to wrap a message
 * 
 */
class Message
{
    /**
     * Json message as associative array
     * 
     * @var array
     */
    private $_message;
    
    /**
     * Metadata for the request
     * 
     * @var NotificationMetadata
     */
    private $_notificationMetadata = null;
    
    /**
     * Create a new instance of the message and
     * wraps the contents in a class
     * 
     * Throws an exception if the message is not valid
     * as defined by the implementation of this class
     * 
     * @param string $json json string
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return new instance of concreate class
     */
    public function __construct($json)
    {
        $this->_message = json_decode($json, true);
        
        $json_error = json_last_error();
       
        if ($json_error != 0) {
            $errorMsg = "Error with message - content is not in json format" .
                self::_getErrorMessageForJsonError($json_error) . " " .
                $json;
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                $errorMsg
            );
        }
    }
    
    /**
     * Set the notification metadata
     * 
     * @param NotificationMetadata $notificationMetadata value to set
     * 
     * @return void
     */
    public function setNotificationMetadata($notificationMetadata)
    {
        $this->_notificationMetadata = $notificationMetadata;
    }
    
    /**
     * Get the notification metadata
     * 
     * @return NotificationMetadata
     */
    public function getNotificationMetadata()
    {
        return $this->_notificationMetadata;
    }
    
    /**
     * Extract the mandatory field from the message and return the contents
     * 
     * @param string $fieldName name of the field to extract
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if not found
     * 
     * @return string field contents if found
     */
    public function getMandatoryField($fieldName)
    {
        $value = $this->getField($fieldName);
        if (is_null($value)) {
            throw new OffAmazonPaymentsNotifications_InvalidMessageException(
                "Error with json message - mandatory field " . $fieldName .
                " cannot be found"
            );
        }
        return $value;
    }
    
    /**
     * Extract the field if present, return null if not defined
     * 
     * @param string $fieldName name of the field to extract
     * 
     * @return string field contents if found, null otherwise
     */
    public function getField($fieldName)
    {
        if (array_key_exists($fieldName, $this->_message)) {
            return $this->_message[$fieldName];
        } else {
            return null;
        }
    }
    
    /**
     * Convert a json error code to a descriptive error
     * message
     * 
     * @param int $json_error message code
     * 
     * @return string error message
     */
    private static function _getErrorMessageForJsonError($json_error)
    {
        switch ($json_error) {
        case JSON_ERROR_DEPTH:
            return " - maximum stack depth exceeded.";
            break;
        case JSON_ERROR_STATE_MISMATCH:
            return " - invalid or malformed JSON.";
            break;
        case JSON_ERROR_CTRL_CHAR:
            return " - control character error.";
            break;
        case JSON_ERROR_SYNTAX:
            return " - syntax error.";
            break;
        default:
            return ".";
            break;
        }
    }
}
?>