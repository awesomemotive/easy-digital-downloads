<?php

/**
 * *****************************************************************************
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
require_once 'OffAmazonPaymentsNotifications/Samples/WebServerExample.php';
require_once 'OffAmazonPaymentsService/Client.php';
require_once 'OffAmazonPaymentsService/Samples/RefundExample.php';
require_once 'OffAmazonPaymentsService/Samples/RefundSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetCaptureDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetRefundDetailsSample.php';


/**
 * This script simulates a simple checkout example and generates
 * html for the page
 * 
 */
class RefundResult extends WebServerExample
{
    /**
     * Create a new instance of the Simple Checkout Example
     * 
     * @param string $queryString url query string
     * 
     * @return void
     */
    public function __construct($queryString)
    {
        parent::__construct($queryString);
           
        $this->exampleClass = new RefundExample(
            new OffAmazonPaymentsService_Client(),
            $this->queryStringParams['orderReferenceId'], 
            $this->queryStringParams['amazonCaptureId'],
        );
    }
    
    /**
     * Execute the example
     *  
     * @return void
     */
    public function run()
    {
        $this->_getOrderReferenceDetails();
        $amazonRefundId = $this->_refundToBuyer(
            $this->queryStringParams["refundAmount"], 
            $this->currencyCode
        );
        $this->_waitUntilRefundProcessingIsCompleted($amazonRefundId);
        $this->_getCaptureDetails();
        print HTML_LB.HTML_LB."Refund Sample is Complete";
    }
    
    /**
     * Get the order reference details to find to the state
     * of the order reference
     *
     * @return void
     */
    private function _getOrderReferenceDetails()
    {
        $response = $this->callStepAndCheckForException('getOrderReferenceDetails');
        echo $this->printResponseToWebpage(
            "printGetOrderReferenceDetailsResponse",
            array($response)
        );
    }
    
    /**
     * Get the order reference details to find to the state
     * of the order reference
     *
     * @return void
     */
    private function _getCaptureDetails()
    {
        $response = $this->callStepAndCheckForException('getCaptureDetails');
        echo $this->printResponseToWebpage(
            "printGetCaptureDetailsResponse",
            array($response)
        );
    }
    
    /**
     * Perform the refund to transfer the amount from seller
     * to buyer
     *
     * @param string $refundAmount   amount to refund to the buyer
     * @param string $refundCurrency currency of the refund
     * 
     * @return void
     */
    private function _refundToBuyer($refundAmount, $refundCurrency)
    {
        $response = $this->callStepAndCheckForException(
            'refundToBuyer',
            array($refundAmount, $refundCurrency)
        );
        echo $this->printResponseToWebpage("printRefundResponse", array($response));
        return $response->getRefundResult()
            ->getRefundDetails()->getAmazonRefundId();
    }
    
    /**
     * Check that we have received an IPN notification for the refund
     * 
     * For PHP, there is an IPN handler that will write the contents of the IPN to
     * a file in the format of 
     * <amazonReferenceId>_RefundNotification.txt
     * This method will check for the presence of this file 
     * and will loop/timeout until the notification has been handled.
     * 
     * Merchants can use alternative approaches such as memory caches, 
     * shared memory or database storage so that scripts serving user 
     * pages are able to check on the status of a notification
     * 
     * @param string $amazonRefundId refund transaction to query
     * 
     * @return void
     */
    private function _waitUntilRefundProcessingIsCompleted($amazonRefundId)
    {
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing(
            $amazonRefundId,
            "RefundNotification"
        );
        // Notification is present, go and get the full
        // information for this notification
        $response = $this->callStepAndCheckForException(
            'getRefundDetails', array($amazonRefundId)
        );
        validateThatRefundIsCompleted($response->getGetRefundDetailsResult()->getRefundDetails());
        echo $this->printResponseToWebpage(
            "printGetRefundDetailsResponse",
            array($response)
        );
     
    }
    
}
$refundResult = new RefundResult($_SERVER['QUERY_STRING']);
$refundResult->run();

?>