<?php

/*******************************************************************************
*  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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
******************************************************************************/

/*******************************************************************************
* Cancellation command line example
*
* This class drives the cancellation example from a command line iterface
* See CancellationExample.php for more information
******************************************************************************/

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 
require_once realpath(dirname(__FILE__)) . '/SetOrderReferenceDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/GetOrderReferenceDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/AuthorizeSample.php';
require_once realpath(dirname(__FILE__)) . '/ConfirmOrderReferenceSample.php';
require_once realpath(dirname(__FILE__)) . '/CancelOrderReferenceSample.php';
require_once realpath(dirname(__FILE__)) . '/GetAuthorizationDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/CLIExample.php';
require_once realpath(dirname(__FILE__)) . '/CancellationExample.php';

/**
 * CancellationExampleCLI class captures input from stdin and prints to stdout,
 * and drives the cancellation example
 * 
 */
class CancellationExampleCLI extends CLIExample
{
    /**
     * Create a new instance of the cli example and validate command line arguments
     * 
     * @param array $argv arguments to the application passed from the command line
     */
    public function __construct($argv)
    {
        if (count($argv) < 1) {
            print "Missing mandatory argument: " .
                    "please provide an amazonOrderReferenceId";
            exit(0);
        }
        
         /*
         * Instantiate Implementation of OffAmazonPaymentsService client
         *
         * Merchant ids and access keys are defined in the constants
         * are defined in the OffAmazonPaymentsService.config.inc.php file in
         * the parent directory of this server
         */
        $this->exampleClass 
            = new CancellationExample(
                new OffAmazonPaymentsService_Client(), 
                $argv[1],
                "100.00"
            );
    }
    
    /**
     * Run all the steps for the sample in sequence
     */
    public function runSample()
    {
        $this->_setupOrderReference();
        $this->_confirmOrderReference();
        $amazonAuthorizationId = $this->_performAuthorization();
        $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId);
        $this->_cancelOrder();
        $this->_getOrderReferenceDetails();
       
        print "Cancellation completed" . PHP_EOL;
    }
    
    /**
     * Add information to the payment contract so that it can be confirmed
     * in a later step
     * Simulates a merchant adding the order details to the payment contract
     *
     */
    private function _setupOrderReference()
    {
        $response = $this->callStepAndCheckForException('setupOrderReference');
        printSetOrderReferenceDetailsResponse($response);
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     */
    private function _confirmOrderReference()
    {
        $response = $this->callStepAndCheckForException('confirmOrderReference');    
        printConfirmOrderReferenceResponse($response);
    }
    
    /**
     * Perform the authorize call for the order
     *
     * @return string amazonAuthorizationId amazon generated authorization id reference
     */
    private function _performAuthorization()
    {   
        $response = $this->callStepAndCheckForException('performAuthorization');
        printAuthorizeResponse($response);        
        return $response->getAuthorizeResult()->getAuthorizationDetails()->getAmazonAuthorizationId();
    }
    
    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated
     * Throw an error if the status is not equal to OPEN
     *
     * @param string $amazonAuthorizationId authorization transaction to query
     *
     * @return No value
     */
    private function _waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException(
            'waitUntilAuthorizationProcessingIsCompleted', 
            array($amazonAuthorizationId)
        );
        
        printGetAuthorizationDetailsResponse($response);
        validateThatAuthorizationIsOpen($response);
    }
    
    /**
     * Cancel the payment contract - this can be performed on any order reference
     * that does not have a completed transaction
     * 
     */
    private function _cancelOrder()
    {
        $response = $this->callStepAndCheckForException('cancelOrderReference');
        printCancelOrderReferenceResponse($response);
    }    
    
    /**
     * Get the status of the order reference request object to show that it
     * now cancelled
     * 
     */
    private function _getOrderReferenceDetails()
    {
        $response = $this->callStepAndCheckForException('getOrderReferenceDetails');
        printGetOrderReferenceDetailsResponse($response);
    }
}

$cancellationExample = new CancellationExampleCLI($argv);
$cancellationExample->runSample();
?>