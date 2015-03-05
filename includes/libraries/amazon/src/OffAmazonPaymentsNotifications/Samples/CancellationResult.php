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
require_once 'OffAmazonPaymentsService/Samples/CancellationExample.php';
require_once 'OffAmazonPaymentsService/Samples/CancelOrderReferenceSample.php';
require_once 'OffAmazonPaymentsService/Samples/SetOrderReferenceDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/ConfirmOrderReferenceSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetOrderReferenceDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetAuthorizationDetailsSample.php';

/**
 * This script simulates a simple checkout example and generates
 * html for the page
 * 
 */
class CancellationResult extends WebServerExample
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
           
        $this->exampleClass = new CancellationExample(
            new OffAmazonPaymentsService_Client(),
            $this->queryStringParams['orderReferenceId'],
            "100.00",
            $this->currencyCode
        );
    }
    
    /**
     * Execute the example
     *  
     * @return void
     */
    public function run()
    {
        $this->_setupOrderReference();
        $this->_confirmOrderReference();
        $amazonAuthorizationId = $this->_performAuthorization();
        $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId);
        $this->_cancelOrder();
        $this->_getOrderReferenceDetails();
        print HTML_LB.HTML_LB."Cancellation Sample is Complete";
    }
    
    /**
     * Add information to the payment contract so that it can be confirmed
     * in a later step
     * Simulates a merchant adding the order details to the payment contract
     *
     * @return void
     */
    private function _setupOrderReference()
    {
        $response = $this->callStepAndCheckForException('setupOrderReference');
        echo $this->printResponseToWebpage(
            "printSetOrderReferenceDetailsResponse", array($response)
        );
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     * @return void
     */
    private function _confirmOrderReference()
    {
        $response = $this->callStepAndCheckForException('confirmOrderReference');
        echo $this->printResponseToWebpage(
            "printConfirmOrderReferenceResponse", array($response)
        );
    }
    
    /**
     * Perform the authorize call for the order
     *
     * @return string amazonAuthorizationId 
     *     amazon generated authorization id reference
     */
    private function _performAuthorization()
    {   
        $response = $this->callStepAndCheckForException('performAuthorization');
        echo $this->printResponseToWebpage(
            "printAuthorizeResponse", array($response)
        );      
        return $response->getAuthorizeResult()
            ->getAuthorizationDetails()->getAmazonAuthorizationId();
    }
    
        
    /**
     * Cancel the payment contract - this can be performed on any order reference
     * that does not have a completed transaction
     * 
     * @return void
     */
    private function _cancelOrder()
    {
        $response = $this->callStepAndCheckForException('cancelOrderReference');
        echo $this->printResponseToWebpage(
            "printCancelOrderReferenceResponse", array($response)
        );
    }    
    
    /**
     * Get the status of the order reference request object to show that it
     * now cancelled
     * 
     * @return void
     */
    private function _getOrderReferenceDetails()
    {
        $response = $this->callStepAndCheckForException('getOrderReferenceDetails');
        echo $this->printResponseToWebpage(
            "printGetOrderReferenceDetailsResponse", array($response)
        );
    }
    /**
     * Check that we have received an IPN notification for the authorization
     * 
     * For PHP, there is an IPN handler that will write the contents of the IPN to
     * a file in the format of 
     * <amazonAuthorizationId>_Authorization.
     * This method will check for the presnece of this file 
     * and will loop/timeout until the notification has been handled.
     * 
     * Merchants can use alternative approaches such as memory caches, 
     * shared memory or database storage so that scripts serving user 
     * pages are able to check on the status of a notification
     *
     * @param string $amazonAuthorizationId authorization transaction to query
     *
     * @return void
     */
    private function _waitUntilAuthorizationProcessingIsCompleted
    ($amazonAuthorizationId)
    {
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing(
            $amazonAuthorizationId, 
            "AuthorizationNotification"
        );
        
        // Notification is present, go and get the full 
        // information for this notification
        $response 
            = $this->callStepAndCheckForException(
                'getAuthorizationDetails',
                array($amazonAuthorizationId) 
            ); 
        
        echo $this->printResponseToWebpage(
            "printGetAuthorizationDetailsResponse", array($response)
        );
        validateThatAuthorizationIsOpen($response);
    }    
}

$cancellationResult = new CancellationResult($_SERVER['QUERY_STRING']);
$cancellationResult->run();

?>