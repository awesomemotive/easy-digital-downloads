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
 *

/*
 * Refund command line example
 *
 * This demonstrates the scenario where merchant a merchant
 * needs to perform a refund on a previously captured amount 
 * for a closed order reference
 * 
 * See RefundExample.php for further information
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';
require_once realpath(dirname(__FILE__)) . '/GetOrderReferenceDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/GetCaptureDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/RefundExample.php';
require_once realpath(dirname(__FILE__)) . '/GetRefundDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/CLIExample.php';
require_once realpath(dirname(__FILE__)) . '/RefundSample.php';

/**
 * RefundExampleCLI class captures input from stdin and prints to stdout,
 * and drives the refund example
 * 
 */
class RefundExampleCLI extends CLIExample
{   
    /**
     * Create a new instance of the cli example and validate command line arguments
     * 
     * @param array $argv arguments to the application passed from the command line
     */
    public function __construct($argv)
    {
        if (count($argv) < 2) {
            print "Missing mandatory argument: " .
                    "please provide an amazonOrderReferenceId, a" .
                    "captureId that you want to refund";
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
            = new RefundExample(
                new OffAmazonPaymentsService_Client(), 
                $argv[1],
                $argv[2]
            );
    }
    
    /**
     * Run all the steps for the sample in sequence
     *
     */
    public function runSample()
    {
        $this->_getOrderReferenceDetails();
        $this->_getAmountToRefund();
        $refundAmount = $this->_getRefundAmount();
        $refundCurrency = $this->_getRefundCurrency();
        $amazonRefundReferenceId = $this->_refundToBuyer($refundAmount, $refundCurrency);
        $this->_waitUntilRefundProcessingIsCompleted($amazonRefundReferenceId);
       
        print "Refund completed" . PHP_EOL;
    }
    
    /**
     * Get the order reference details to find to the state
     * of the order reference
     *
     */
    private function _getOrderReferenceDetails()
    {
        $response = $this->callStepAndCheckForException('getOrderReferenceDetails');
        printGetOrderReferenceDetailsResponse($response);
    }
    
    /**
     * Get the capture details to find out the
     * maximum amount that can be refunded
     *
     */
    private function _getAmountToRefund()
    {
        $response = $this->callStepAndCheckForException('getCaptureDetailsRequest');
        printGetCaptureDetailsResponse($response);
    }
    
    /**
     * Get the amount to refund
     * 
     * @return string refund amount, as a numeric string`
     */
    private function _getRefundAmount()
    {
        print PHP_EOL . "----------------------------------------------------------------------------" . PHP_EOL;
        
        print "Enter the amount to refund the customer (to 2 decimal places): ";
        do {
            $refundAmount = trim(fgets(STDIN));
        } while (!is_numeric($refundAmount));
        
        return $refundAmount;
    }
    
    /**
     * Get the currency to perform the refund in
     * 
     * @return the curreny to perform the refund in
     */
    private function _getRefundCurrency()
    {
        print "Enter the currency for the refund: ";
        do {
            $refundCurrency = trim(fgets(STDIN));
        } while (!is_string($refundCurrency));
        
        return $refundCurrency;
    }
    
    /**
     * Perform the refund to transfer the amount from seller
     * to buyer
     * 
     * @param string $refundAmount   amount to refund to the buyer
     * @param string $refundCurrency currency of the refund
     */
    private function _refundToBuyer($refundAmount, $refundCurrency)
    {
        $response = $this->callStepAndCheckForException(
            'refundToBuyer', 
            array($refundAmount, $refundCurrency)
        );
        printRefundResponse($response);
        return $response->getRefundResult()->getRefundDetails()->getAmazonRefundId();
    }
    
    /**
     * Poll the API for the status of the Refund Request, and continue
     * once the status has been updated
     * Throw an error if the status is not equal to OPEN
     *
     * @param string $amazonRefundReferenceId refund transaction to query
     *
     * @return No value
     */
    private function _waitUntilRefundProcessingIsCompleted($amazonRefundReferenceId)
    {
        $response = $this->callStepAndCheckForException(
            'waitUntilRefundProcessingIsCompleted',
            array($amazonRefundReferenceId)
        );
       
        printGetRefundDetailsResponse($response);
        validateThatRefundIsCompleted($response->getGetRefundDetailsResult()->getRefundDetails());
    }
}

$refundExample = new RefundExampleCLI($argv);
$refundExample->runSample();

?>