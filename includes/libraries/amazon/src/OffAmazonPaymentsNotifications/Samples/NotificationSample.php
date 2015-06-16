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
require_once 'OffAmazonPaymentsNotifications/Samples/IpnLogFile.php';
require_once 'OffAmazonPaymentsNotifications/Notification.php';

/**
 * Abstract parent class for handling an notification and print the
 * contents to the log file
 *
 */
abstract class OffAmazonPaymentsNotifications_Samples_NotificationSample
{
    /**
     * Log file handle
     *
     * @var OffAmazonPaymentsNotifications_Samples_IpnLogFile
     */
    protected $ipnLogFile = null;
    
    /**
     * Notification to print to the log
     * 
     * @var OffAmazonPaymentsNotifications_Notification notification
     */
    protected $notification = null;
    
    /**
     * Create a new instance of the notification handler
     *
     * @param OffAmazonPaymentsNotifications_Model_Notification $notification notification
     *                                                                        to handle
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function __construct($notification) 
    {
        if (!isset($notification)) {
            throw new InvalidArgumentException("notification is NULL");
        } else {
            $this->notification = $notification;
            $this->ipnLogFile 
                = new OffAmazonPaymentsNotifications_Samples_IpnLogFile(
                    $this->getLogFileName()
                );
        }
    }
    
    /**
     * Extract the name of the log file based on the notification=
     * 
     * @throws InvalidArgumentException
     * 
     * @return string
     */
    protected abstract function getLogFileName();
    
    /**
     * Log the notification to the file
     * 
     * @return void
     */
    public function logNotification()
    {
        try {
            $this->logNotificationContents();
            $this->ipnLogFile->writeLine("=============================================================================");
            $this->ipnLogFile->closeFile();
        } catch (Exception $ex){
            error_log($ex->getMessage());
        } 
    }
    
    /**
     * Log the notification contents
     *
     * @return void
     */
    protected abstract function logNotificationContents();
}
?>