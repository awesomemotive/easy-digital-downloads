<?php
/*
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
 */

require_once realpath(dirname(__FILE__) . '/.config.inc.php');
require_once 'OffAmazonPaymentsService/Exception.php';

/**
 * Helper class to print out the exception to the command line
 */
abstract class CLIExample
{
    /**
     * Store an instance of the example class that this CLI class executes
     */
    protected $exampleClass;
    
    /**
     * Call the desired setp and check that it does not throw an
     * exception
     * 
     * @param string $stepName the name of the step to call on the example class
     * 
     * @return mixed the response object from the step, or an exception if thrown
     */
    protected function callStepAndCheckForException($stepName, $args = array())
    {
        try {
            $response = call_user_func_array(array($this->exampleClass, $stepName), $args);
        } catch (OffAmazonPaymentsService_Exception $ex) {
            $this->printExceptionToCLI($ex, $stepName);
            throw $ex;
        }

        return $response;
    }
    
    /**
     * Output information about the raised exception to standard output
     *
     * @param OffAmazonPaymentsService_Exception $ex       exception
     * @param string                             $stepName step where ex occured
     *
     * @return no value
     */
    protected function printExceptionToCLI(
        OffAmazonPaymentsService_Exception $ex,
        $stepName
    ) {
        print "Error caught executing step " . $stepName . PHP_EOL;
        print "Caught Exception: " . $ex->getMessage() . PHP_EOL;
        print "Response Status Code: " . $ex->getStatusCode() . PHP_EOL;
        print "Error Code: " . $ex->getErrorCode() . PHP_EOL;
        print "Error Type: " . $ex->getErrorType() . PHP_EOL;
        print "Request ID: " . $ex->getRequestId() . PHP_EOL;
        print "XML: " . $ex->getXML() . PHP_EOL;
        print "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . PHP_EOL;
    }
    
    /**
     * Populate the steps required to execute the sample code scenario
     */
    public abstract function runSample();
}
?>
