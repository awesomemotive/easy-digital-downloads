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
require_once 'OffAmazonPaymentsNotifications/Samples/WebServerExample.php';
require_once 'OffAmazonPaymentsService/Client.php';
require_once 'OffAmazonPaymentsService/Samples/AutomaticPaymentsSimpleCheckoutExample.php';

/**
 * This script simulates a simple checkout example for automatic payment 
 * and generates html for the page
 * 
 */
class AutomaticPaymentsSimpleCheckoutResult extends WebServerExample
{

    /**
     * Create a new instance of the Automatic Payment Simple Checkout Example
     * 
     * @param string $queryString url query string
     * 
     * @return void
     */
    public function __construct ($queryString)
    {
        parent::__construct($queryString);
        
        $this->exampleClass = new AutomaticPaymentsSimpleCheckoutExample(
                new OffAmazonPaymentsService_Client(), 
                $this->queryStringParams['billingAgreementId']);
    }

    /**
     * Execute the example
     *  
     * @return void
     */
    public function run ()
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
                $this->queryStringParams['billingAgreementId'] . "-A01");
        $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId1);
        $this->_capturePaymentAmount($paymentTotal, $amazonAuthorizationId1);
        
        // Second payment with capture now
        $this->_authorizePaymentAmountWithCaptureNow($paymentTotal, 
                $this->queryStringParams['billingAgreementId'] . "-A02");
        
        // Third payment with capture now
        $this->_authorizePaymentAmountWithCaptureNow($paymentTotal, 
                $this->queryStringParams['billingAgreementId'] . "-A03");
        
        // Close the billing agreement when this automatic payment is no longer
        // needed
        $this->_closeBillingAgreement();
        
        print HTML_LB . HTML_LB . "Automatic payment simple checkout sample is complete";
    }

    /**
     * Retreive the current information about the order as indicated by the buyer
     * and calculate the total amount to charge, based on address destination state
     * and country
     *
     * @return string total amount for the order that the merchant will
     *         charge the buyer
     */
    private function _calculatePaymentAmountBasedOnBuyerDestinationAddress ()
    {
        $response = $this->callStepAndCheckForException('getBillingAgreementDetails');
        
        $this->printResponseToWebpage("printGetBillingAgreementDetailsResponse", array(
            $response
        ));
        
        $paymentAmountPreTaxAndShipping = $this->_getPreTaxAndShippingPaymentAmountFromQueryParams();
        $shippingType = $this->_getShippingTypeFromQueryParams();
	    
        return $this->exampleClass->calculatePaymentAmountBasedOnBuyerDetails(
                $response->getGetBillingAgreementDetailsResult()
                    ->getBillingAgreementDetails(), $paymentAmountPreTaxAndShipping, $shippingType);
    }

    /**
     * Add seller information by making the call to setBillingAgreementDetails
     * with seller notes and custom information.
     *          
     */
    private function _addSellerInformationToBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('addSellerInformationToBillingAgreement');
        $this->printResponseToWebpage("printSetBillingAgreementDetailsResponse", array(
            $response
        ));
    }

    /**
     * Confirm the billing agreement
     *
     * @return void
     */
    private function _confirmBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('confirmBillingAgreement');
        
        $this->printResponseToWebpage("printConfirmBillingAgreementResponse", array(
            $response
        ));
    }

    /**
     * Check that the billing agreement is in valid status and the selected payment
     * method is also valid.
     */
    private function _validateBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('validateBillingAgreement');
        
        $this->printResponseToWebpage("printValidateBillingAgreementResponse", array(
            $response
        ));
    }

    /**
     * Perform authorize call on billing agreement
     * 
     * @param float $authorizationAmount
     * @param string $authorizationReferenceId
     */
    private function _authorizePaymentAmount ($authorizationAmount, $authorizationReferenceId)
    {
        $response = $this->callStepAndCheckForException('authorizePaymentAmount', 
                array(
                    $authorizationAmount,
                    $authorizationReferenceId
                ));
        
        $this->printResponseToWebpage("printAuthorizeOnBillingAgreementResponse", array(
            $response
        ));
        
        return getAmazonAuthorizationIdFromAuthorizeOnBillingAgreementResponse($response);
    }

    /**
     * Perform authorize call on billing agreement
     *
     * @param float $authorizationAmount
     * @param string $authorizationReferenceId
     */
    private function _authorizePaymentAmountWithCaptureNow ($authorizationAmount, 
            $authorizationReferenceId)
    {
        $response = $this->callStepAndCheckForException('authorizePaymentAmountWithCaptureNow', 
                array(
                    $authorizationAmount,
                    $authorizationReferenceId
                ));
        
        $this->printResponseToWebpage("printAuthorizeOnBillingAgreementResponse", array(
            $response
        ));
        
        return getAmazonAuthorizationIdFromAuthorizeOnBillingAgreementResponse($response);
    }

    /**
     * Check that we have received an IPN notification for the authorization
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
     * @param string $amazonAuthorizationId authorization transaction to query
     *
     * @return GetAuthorizationDetailsResponse
     */
    private function _waitUntilAuthorizationProcessingIsCompleted ($amazonAuthorizationId)
    {
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing($amazonAuthorizationId, 
                "AuthorizationNotification");
    }

    /**
     * Display additional information about a completed authorization
     * 
     * Once an IPN has been received to notify the transition of an IPN to
     * one of the terminal states, the merchant may optionally call 
     * GetAuthorizationDetails to obtain additional information about the authorization.
     * 
     * In countries which require VAT invoicing, this approach will allow you to obtain
     * the buyers billing address so that the invocing requirements can be met.
     */
    private function _getAdditionalInformationForProcessedAuthorization ($amazonAuthorizationId)
    {
        // Notification is present, go and get the full
        // information for this notification
        $response = $this->callStepAndCheckForException('getAuthorizationDetails', 
                array(
                    $amazonAuthorizationId
                ));
        
        $this->printResponseToWebpage("printGetAuthorizationDetailsResponse", array(
            $response
        ));
        
        validateThatAuthorizationIsOpen($response);
    }

    /**
     * Perform the capture call for the order
     * Throw an exception if the capture is not processed, as this is the
     * expected result
     *
     * @param float  $captureAmount         amount to capture from the buyer
     * @param string $amazonAuthorizationId auth id to perform the capture on
     *
     * @return no response
     */
    private function _capturePaymentAmount ($captureAmount, $amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException('captureOrderAmount', 
                array(
                    $captureAmount,
                    $amazonAuthorizationId,
                    $this->currencyCode
                ));
        
        $captureId = $response->getCaptureResult()
            ->getCaptureDetails()
            ->getAmazonCaptureId();
        
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing($captureId, "CaptureNotification");
        
        $response = $this->callStepAndCheckForException('getCaptureDetails', array(
            $captureId
        ));
        
        validateThatCaptureIsCompleted($response->getGetCaptureDetailsResult());
        $this->printResponseToWebpage("printGetCaptureDetailsResponse", array(
            $response
        ));
    }

    /**
     * Close this billing agreement to indicate that the automatic payment is complete, and
     * no further payments will be performed on this billing agreement
     *
     * @return no value
     */
    private function _closeBillingAgreement ()
    {
        $response = $this->callStepAndCheckForException('closeBillingAgreement');
        $this->printResponseToWebpage("printCloseBillingAgreementResponse", array(
            $response
        ));
    }

    /**
     * Capture the pre tax order amount from standard input,
     * making sure that it is a numeric string
     *
     * @return string total amount of the order before tax and shipping charges
     */
    private function _getPreTaxAndShippingPaymentAmountFromQueryParams ()
    {
        $orderAmount = $this->queryStringParams['paymentAmount'];
        return $orderAmount;
    }

    /**
     * Capture the shipping type for this order, which determines
     * the shipping charge
     *
     * @return number selected shipping type index
     */
    private function _getShippingTypeFromQueryParams ()
    {
        $shippingType = $this->queryStringParams['shipping'];
        return $shippingType - 1;
    }

}

$AutomaticPaymentsSimpleCheckoutResult = new AutomaticPaymentsSimpleCheckoutResult(
        $_SERVER['QUERY_STRING']);
$AutomaticPaymentsSimpleCheckoutResult->run();

?>
