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

/*
 * Automatic payments simple checkout example command line example
 *
 * This class drives the automatic payment simple checkout example from a command 
 * line interface. See AutomaticPaymentsSimpleCheckoutExample.php for more information
 */
require_once realpath(dirname(__FILE__)) . '/.config.inc.php';
require_once realpath(dirname(__FILE__)) . '/GetBillingAgreementDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/SetBillingAgreementDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/ConfirmBillingAgreementSample.php';
require_once realpath(dirname(__FILE__)) . '/ValidateBillingAgreementSample.php';
require_once realpath(dirname(__FILE__)) . '/AuthorizeOnBillingAgreementSample.php';
require_once realpath(dirname(__FILE__)) . '/GetAuthorizationDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/CaptureSample.php';
require_once realpath(dirname(__FILE__)) . '/GetCaptureDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/CloseBillingAgreementSample.php';
require_once realpath(dirname(__FILE__)) . '/CLIExample.php';
require_once realpath(dirname(__FILE__)) . '/AutomaticPaymentsSimpleCheckoutExample.php';

/**
 * AutomaticPaymentsSimpleCheckoutExampleCLI class captures input from stdin and
 * prints to stdout, and drives the automatic payment simple checkout example
 */
class AutomaticPaymentsSimpleCheckoutExampleCLI extends CLIExample
{

    private $_amazonBillingAgreementId;

    /**
     * Create a new instance of the cli example and
     * validate command line arguments
     *
     * @param array $argv
     *            arguments to the appplication passed from the command line
     */
    public function __construct ($argv)
    {
        if (count($argv) < 1) {
            print "Missing mandatory argument: " . "please provide an amazonBillingAgreementId";
            exit(0);
        }
        
        /*
         * Instantiate Implementation of OffAmazonPaymentsService client
         * Merchant ids and access keys are defined in the constants are defined
         * in the OffAmazonPaymentsService.config.inc.php file in the parent
         * directory of this server
         */
        $this->exampleClass = new AutomaticPaymentsSimpleCheckoutExample(
                new OffAmazonPaymentsService_Client(), $argv[1]);
        $this->_amazonBillingAgreementId = $argv[1];
    }

    /**
     * Run all the steps for the sample in sequence
     */
    public function runSample ()
    {
        // Calculate payment amount based on buyer selected shipping address
        $paymentTotal = $this->_calculatePaymentAmountBasedOnBuyerDestinationAddress();
        
        // Added custom information and seller note to the billing agreement
        $this->_addSellerInformationToBillingAgreement();
        
        /*
         * Confirm billing agreement. The billing agreement has to be consented 
         * by buyer before you confirm the billing agreement.
         */
        $this->_confirmBillingAgreement();
        
        // Validate billing agreement (optional)
        $this->_validateBillingAgreement();
        
        // First payment
        $amazonAuthorizationId1 = $this->_authorizePaymentAmount($paymentTotal, 
                $this->_amazonBillingAgreementId . "-A01");
        $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId1);
        $this->_captureOrderAmount($paymentTotal, $amazonAuthorizationId1);
        
        // Second payment with capture now
        $this->_authorizePaymentAmountWithCaptureNow($paymentTotal, 
                $this->_amazonBillingAgreementId . "-A02");
        
        // More payments here ...
        
        /*
         * Confirm the billing agreement again if the buyer changes the shipping
         * address or payments method through widgets. For details on how to render
         * widgets for existing billing agreements, please see integration guide
         * for details.
         */
        $this->_confirmBillingAgreement();
        
        // Validate billing agreement (optional)
        $this->_validateBillingAgreement();
        
        // Another payment here
        $this->_authorizePaymentAmountWithCaptureNow($paymentTotal, 
                $this->_amazonBillingAgreementId . "-A03");
        
        // More payments here ...
        
        // Close the billing agreement when this automatic payment is no longer
        // needed
        $this->_closeBillingAgreement();
        
        print "Automatic payment simple checkout example completed" . PHP_EOL;
    }

    /**
     * Retreive the current information about the billing agreement as indicated
     * by the buyer and calculate amount for each payment to charge, based on
     * address destination state and country
     *
     * @return string total amount for the order that the merchant will charge
     *         the buyer
     */
    private function _calculatePaymentAmountBasedOnBuyerDestinationAddress ()
    {
        $response = $this->callStepAndCheckForException('getBillingAgreementDetails');
        printGetBillingAgreementDetailsResponse($response);
        
        $orderTotalPreTaxAndShipping = $this->_getPreTaxAndShippingOrderAmountFromStdIn();
        $shippingType = $this->_getShippingTypeFromStdIn();
	   
        return $this->exampleClass->calculatePaymentAmountBasedOnBuyerDetails(
                $response->getGetBillingAgreementDetailsResult()
                    ->getBillingAgreementDetails(), $orderTotalPreTaxAndShipping, $shippingType);
    }

    /**
     * Add seller information by making the call to setBillingAgreementDetails
     * with seller notes and custom information.
     *          
     */
    private function _addSellerInformationToBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('addSellerInformationToBillingAgreement');
        printSetBillingAgreementDetailsResponse($response);
    }

    /**
     * Confirm the order reference information, allowing for authorizations and
     * captures to be created.
     */
    private function _confirmBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('confirmBillingAgreement');
        printConfirmBillingAgreementResponse($response);
    }

    /**
     * Check that the billing agreement is in valid status and the selected payment
     * method is also valid. 
     */
    private function _validateBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('validateBillingAgreement');
        printValidateBillingAgreementResponse($response);
    }

    /**
     * Perform the authorize call for the billing agreement
     *
     * @param float $authorizationAmount
     *            amount to authorize from the buyer
     *            
     * @return string amazonAuthorizationId amazon generated authorization id
     *         reference
     */
    private function _authorizePaymentAmount ($authorizationAmount, $authorizationReferenceId)
    {
        $response = $this->callStepAndCheckForException('authorizePaymentAmount', 
                array(
                    $authorizationAmount,
                    $authorizationReferenceId
                ));
        printAuthorizeOnBillingAgreementResponse($response);
        return $response->getAuthorizeOnBillingAgreementResult()
            ->getAuthorizationDetails()
            ->getAmazonAuthorizationId();
    }

    /**
     * Perform the authorize call with auto capture for the billing agreement
     * 
     * @param unknown $authorizationAmount
     * @param unknown $authorizationReferenceId
     */
    private function _authorizePaymentAmountWithCaptureNow ($authorizationAmount, 
            $authorizationReferenceId)
    {
        $response = $this->callStepAndCheckForException('authorizePaymentAmountWithCaptureNow', 
                array(
                    $authorizationAmount,
                    $authorizationReferenceId
                ));
        printAuthorizeOnBillingAgreementResponse($response);
        return $response->getAuthorizeOnBillingAgreementResult()
            ->getAuthorizationDetails()
            ->getAmazonAuthorizationId();
    }

    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated
     * Throw an error if the status is not equal to Open
     *
     * @param string $amazonAuthorizationId
     *            authorization transaction to query
     *            
     */
    private function _waitUntilAuthorizationProcessingIsCompleted ($amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException(
                'waitUntilAuthorizationProcessingIsCompleted', 
                array(
                    $amazonAuthorizationId
                ));
        printGetAuthorizationDetailsResponse($response);
        validateThatAuthorizationIsOpen($response);
    }

    /**
     * Perform the capture call for the order
     * Throw an exception if the capture is not processed, as this is the
     * expected result
     *
     * @param float $captureAmount
     *            amount to capture from the buyer
     * @param string $amazonAuthorizationId
     *            auth id to perform the capture on
     *            
     * @return no response
     */
    private function _captureOrderAmount ($captureAmount, $amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException('captureOrderAmount', 
                array(
                    $captureAmount,
                    $amazonAuthorizationId
                ));
        
        validateThatCaptureIsCompleted($response->getCaptureResult());
        printCaptureResponse($response);
    }

    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     *
     * @return no value
     */
    private function _closeBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('closeBillingAgreement');
    }

    /**
     * Capture the pre tax order amount from standard input,
     * making sure that it is a numeric string
     *
     * @return string total amount of the order before tax and shipping charges
     */
    private function _getPreTaxAndShippingOrderAmountFromStdIn ()
    {
        print PHP_EOL . "-------------------------------------------" . PHP_EOL;
        print "Enter the pre tax amount to charge for the order" . "as a number (to 2 decimal places): ";
        do {
            $orderAmount = trim(fgets(STDIN));
        } while (! is_numeric($orderAmount));
        
        return $orderAmount;
    }

    /**
     * Capture the shipping type for this order, which determines
     * the shipping charge
     *
     * @return number selected shipping type index
     */
    private function _getShippingTypeFromStdIn ()
    {
        print PHP_EOL . "Select a shipping option for the order:" . PHP_EOL;
        print "\t 1 - Overnight shipping" . PHP_EOL;
        print "\t 2 - 2-day shipping" . PHP_EOL;
        print "\t 3 - 5-day shipping" . PHP_EOL;
        print ">>";
        
        do {
            $shippingType = trim(fgets(STDIN));
        } while (! is_numeric($shippingType) and ($shippingType < 1 or $shippingType > 3));
        
        return $shippingType - 1;
    }
}
;

$AutomaticPaymentsSimpleCheckoutExample = new AutomaticPaymentsSimpleCheckoutExampleCLI($argv);
$AutomaticPaymentsSimpleCheckoutExample->runSample();

?>
