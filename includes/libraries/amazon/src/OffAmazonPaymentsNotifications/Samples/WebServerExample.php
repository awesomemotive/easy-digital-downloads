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
require_once 'OffAmazonPaymentsService/Exception.php';
require_once 'OffAmazonPaymentsNotifications/Samples/IpnLogFile.php';
require_once 'OffAmazonPaymentsService/Samples/GetOrderReferenceDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/SetOrderReferenceDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/ConfirmOrderReferenceSample.php';
require_once 'OffAmazonPaymentsService/Samples/AuthorizeSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetAuthorizationDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/CaptureSample.php';
require_once 'OffAmazonPaymentsService/Samples/CloseOrderReferenceSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetCaptureDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetBillingAgreementDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/SetBillingAgreementDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/ConfirmBillingAgreementSample.php';
require_once 'OffAmazonPaymentsService/Samples/ValidateBillingAgreementSample.php';
require_once 'OffAmazonPaymentsService/Samples/AuthorizeOnBillingAgreementSample.php';
require_once 'OffAmazonPaymentsService/Samples/CloseBillingAgreementSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetProviderCreditDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetProviderCreditReversalDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/ReverseProviderCreditSample.php';



define('HTML_LB', "<br/>");

/**
 * Parent class for webserver based samples that contains common
 * shared code
 */
abstract class WebServerExample
{
    /**
     * Query string parameters
     *
     * @var assoc array values
     */
    protected $queryStringParams = null;
    
    /**
     * Sample class
     *
     * @var mixed sample class
     */
    protected $exampleClass = null;
    
    /**
     * Curreny code for requests
     *
     * @var string
     */
    protected $currencyCode = "USD";
    
    /**
     * Log file path location
     *
     * @var string
     */
    protected $folderPath = null;
    
    /**
     * Construct a new instance of the child class
     * 
     * @param string $queryString url query string
     * 
     * @return void
     */
    public function __construct($queryString)
    {
        parse_str($queryString, $this->queryStringParams);
        $this->folderPath = LOG_FILE_LOCATION;
    }
    
    /**
     * Check that we have received an IPN notification for the defined event
     * 
     * For PHP, there is an IPN handler that will write the contents of the IPN to
     * a file in the format of 
     * <amazonOrderReferenceId>_<amazonAuthorizationId>_Authorization.
     * This method will check for the presnece of this file 
     * and will loop/timeout until the notification has been handled.
     * 
     * Merchants can use alternative approaches such as memory caches, 
     * shared memory or database storage so that scripts serving user 
     * pages are able to check on the status of a notification
     *
     * @param string $identifier       transaction      that we are waiting to query
     * @param string $notificationType notificationType notification type 
     *                                                  that we expect
     *
     * @return void
     */
    protected function waitForNotificationToBeProcessedBeforeContinuing(
        $identifier, 
        $notificationType
    ) {
        $fileName = $this->folderPath . $identifier . "_" . $notificationType . ".txt";
        // timeout after 1 minute
        $totalChecks = 0;

         while (!file_exists($fileName) && $totalChecks < 20) {
             sleep(5);
             $totalChecks += 1;
         }
        
        if ($totalChecks >= 20) {
            throw new ErrorException(
                "IPN has not been received within timeout period exiting sample" ."for ".$identifier ." and ".$notificationType
            );
        }    
    }
    
    /**
     * Call the desired step and check that it does not throw an
     * exception
     *
     * @param string $stepName the name of the step to call on the example class
     * @param array  $args     optional parameters to function call
     *
     * @return mixed the response object from the step, or an exception if thrown
     */
    protected function callStepAndCheckForException($stepName, $args = array())
    {
        try {
            $response = call_user_func_array(
                array($this->exampleClass, $stepName), 
                $args
            );
        } catch (OffAmazonPaymentsService_Exception $ex) {
            $this->printResponseToWebpage(
                "printExceptionToWebpage", 
                array($ex, $stepName)
            );
            throw $ex;
        }
    
        return $response;
    }
    
    /**
     * Invoke the passed in function and print the results out in
     * html format to the output buffer
     * 
     * @param string $stepName Name of the function to call
     * @param array  $arg      Function arguments
     * 
     * @return void
     */
    protected function printResponseToWebpage($stepName, $arg=array())
    {
        ob_start();
        call_user_func_array($stepName, $arg);
        $result = ob_get_contents();
        ob_clean();
        $result = preg_replace("/(\\n)/", HTML_LB, $result);
        $result = preg_replace("/(\\s)/", "&nbsp;", $result);
        print $result;
    }
    
    /**
     * Execute the example
     * 
     * @return void
     */
    public abstract function run();
}

/**
 * Output information about the raised exception to standard output
 *
 * @param OffAmazonPaymentsService_Exception $ex       exception
 * @param string                             $stepName step where ex occured
 *
 * @return string decription of the exception
 */
function printExceptionToWebpage(
    OffAmazonPaymentsService_Exception $ex,
    $stepName
) {
    print "Error caught executing step " . $stepName . HTML_LB;
    print "Caught Exception: " . $ex->getMessage() . HTML_LB;
    print "Response Status Code: " . $ex->getStatusCode() . HTML_LB;
    print "Error Code: " . $ex->getErrorCode() . HTML_LB;
    print "Error Type: " . $ex->getErrorType() . HTML_LB;
    print "Request ID: " . $ex->getRequestId() . HTML_LB;
    print "XML: " . $ex->getXML() . HTML_LB;
    print "ResponseHeaderMetadata: " . $ex->getResponseHeaderMetadata() . HTML_LB;
}
?>
