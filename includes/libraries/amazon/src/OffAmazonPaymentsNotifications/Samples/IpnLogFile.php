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
require_once 'OffAmazonPaymentsService/OffAmazonPaymentsService.config.inc.php';

/* 
 * Make the variable declared in the properties file a constant
 * for use inside the application
 */
define('LOG_FILE_LOCATION', $logFileLocation);

/**
 * Wrapper around a log file used to capture the ipn notifications
 * 
 */
class OffAmazonPaymentsNotifications_Samples_IpnLogFile
{
    /**
     * Name of the log file without directory path
     * 
     * @var string
     */
    private $_fileName = null;
    
    /**
     * File handle
     * 
     * @var int id
     */
    private $_fileHandler = null;
    
    /**
     * Create a new instance of the class
     * 
     * @param string $fileName name of the log file
     * 
     * @return void
     */
    public function __construct($fileName)
    {
        $this->_fileName = $fileName;
        $this->_fileHandler = fopen(LOG_FILE_LOCATION . $this->getFileName(), 'a');
    }
    
    /**
     * Get the name of the file
     * 
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }
    
    /**
     * Write a single line to the log file
     * 
     * @param string $content content to write
     * 
     * @return void
     */
    public function writeLine($content)
    {
        fwrite($this->_fileHandler, $content . PHP_EOL);
    }
    
    /**
     * Close the file
     * 
     * @return void
     */
    public function closeFile() 
    {
        fclose($this->_fileHandler);
    }
}
?>
