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
require_once realpath ( dirname ( __FILE__ ) . "/.config.inc.php" );
require_once 'OffAmazonPaymentsNotifications/Samples/WebServerExample.php';
require_once 'OffAmazonPaymentsService/Client.php';
require_once 'OffAmazonPaymentsService/Samples/SimpleCheckoutExample.php';
require_once 'OffAmazonPaymentsService/Samples/GetProviderCreditDetailsSample.php';

/**
 * This script simulates a simple checkout example and generates
 * html for the page
 */
class ProviderCheckoutResult extends WebServerExample {
	/**
	 * Create a new instance of the Simple Checkout Example
	 *
	 * @param string $queryString
	 *        	url query string
	 *        	
	 * @return void
	 */
	public function __construct($queryString) {
		parent::__construct ( $queryString );
		
		$this->exampleClass = new SimpleCheckoutExample ( new OffAmazonPaymentsService_Client (), $this->queryStringParams ['orderReferenceId'] );
	}
	
	/**
	 * Execute the example
	 *
	 * @return void
	 */
	public function run() {
		$orderTotal = $this->_calculateOrderTotalBasedOnBuyerInfo();
		$this->_addOrderTotalAndSellerInformationToOrder($orderTotal );
		$this->_confirmOrderReference();
		$amazonAuthorizationId = $this->_authorizeBasedOnOptionSelected ($orderTotal );
		$this->_getAdditionalInformationForProcessedAuthorization ( $amazonAuthorizationId );
		$providerCreditInfo = $this->_getProviderCreditInfoFromQueryParams();
		$captureResponse = $this->_captureOrderAmount ( $orderTotal, $amazonAuthorizationId , $providerCreditInfo);
		$providerCreditSummaryList = $this->_waitForProviderCreditSummaryList ( $captureResponse );
		$this->_confirmProviderCreditSummaries ( $providerCreditSummaryList );
		$this->_closeOrderReference ();
		print HTML_LB . HTML_LB . "Provider Checkout Sample is Complete";
	}
	
	/**
	 * Retreive the current information about the order as indicated by the buyer
	 * and calculate the total amount to charge, based on address destination state
	 * and country
	 *
	 * @return string total amount for the order that the merchant will
	 *         charge the buyer
	 */
	private function _calculateOrderTotalBasedOnBuyerInfo() {
		$response = $this->callStepAndCheckForException ( 'getOrderReferenceDetails' );
		
		$this->printResponseToWebpage ( "printGetOrderReferenceDetailsResponse", array (
				$response 
		) );
		
		$orderTotalPreTaxAndShipping = $this->_getPreTaxAndShippingOrderAmountFromQueryParams ();
		$shippingType = $this->_getShippingTypeFromQueryParams ();
		
		return $this->exampleClass->calculateOrderTotalBasedOnBuyerDetails ( $response->getGetOrderReferenceDetailsResult ()->getOrderReferenceDetails (), $orderTotalPreTaxAndShipping, $shippingType);
	}
	
	/**
	 * Add order information by making the call to setOrderReferenceDetails with
	 * the total order amount, as well as notes describing the order information
	 *
	 * @param float $orderTotal
	 *        	total value of the order, incl shipping and tax
	 *        	
	 * @return void
	 */
	private function _addOrderTotalAndSellerInformationToOrder($orderTotal) {
		$response = $this->callStepAndCheckForException ( 'addOrderTotalAndSellerInformationToOrder', array (
				$orderTotal 
		) );
		
		$this->printResponseToWebpage ( "printSetOrderReferenceDetailsResponse", array (
				$response 
		) );
	}
	
	/**
	 * Confirm the order reference information, allowing for
	 * authorizations and captures to be created
	 *
	 * @return void
	 */
	private function _confirmOrderReference() {
		$response = $this->callStepAndCheckForException ( 'confirmOrderReference' );
		
		$this->printResponseToWebpage ( "printConfirmOrderReferenceResponse", array (
				$response 
		) );
	}
	
	/**
	 * Perform Authorize using Regular Authorization or Fast Authorization.
	 *
	 * @param float $authorizationAmount
	 *        	amount to authorize from the buyer
	 *        	
	 * @return string amazonAuthorizationId amazon generated authorization id reference
	 */
	private function _authorizeBasedOnOptionSelected($authorizationAmount) {
		$authOption = $this->_getAuthOptionFromQueryParams();
		$amazonAuthorizationId = $this->_authorizeOrderAmount ( $authorizationAmount, $authOption );
		$this->_waitUntilAuthorizationProcessingIsCompleted ( $amazonAuthorizationId );
		return $amazonAuthorizationId;
	}
	
	/**
	 * Perform the authorize call for the order
	 *
	 * @param float $authorizationAmount
	 *        	amount to authorize from the buyer
	 *        	
	 * @return string amazonAuthorizationId amazon generated authorization rest
	 *         id reference
	 */
	private function _authorizeOrderAmount($authorizationAmount, $authOption = 1) {
		$response = $this->callStepAndCheckForException ( 'authorizeOrderAmount', array (
				$authorizationAmount,
				$authOption 
		) );
		
		$this->printResponseToWebpage ( "printAuthorizeResponse", array (
				$response 
		) );
		
		return getAmazonAuthorizationIdFromAuthorizeResponse ( $response );
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
	 * @param string $amazonAuthorizationId
	 *        	authorization transaction to query
	 *        	
	 * @return GetAuthorizationDetailsResponse
	 */
	private function _waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId) {
		// Check for the presence of the ipn notification
		$this->waitForNotificationToBeProcessedBeforeContinuing ( $amazonAuthorizationId, "AuthorizationNotification" );
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
	private function _getAdditionalInformationForProcessedAuthorization($amazonAuthorizationId) {
		// Notification is present, go and get the full
		// information for this notification
		$response = $this->callStepAndCheckForException ( 'getAuthorizationDetails', array (
				$amazonAuthorizationId 
		) );
		
		$this->printResponseToWebpage ( "printGetAuthorizationDetailsResponse", array (
				$response 
		) );
		
		validateThatAuthorizationIsOpen ( $response );
	}
	
	/**
	 * Perform the capture call for the order
	 * Throw an exception if the capture is not processed, as this is the
	 * expected result
	 *
	 * @param float $captureAmount
	 *        	amount to capture from the buyer
	 * @param string $amazonAuthorizationId
	 *        	auth id to perform the capture on
	 *        	
	 * @return no response
	 */
	private function _captureOrderAmount($captureAmount, $amazonAuthorizationId, $providerCreditInfo = null) {
		
		$response = $this->callStepAndCheckForException ( 'captureOrderAmount', 
			array($captureAmount,
				$amazonAuthorizationId,
				$providerCreditInfo) );
		$this->printResponseToWebpage ( "printCaptureResponse", array (
				$response 
		) );
		$captureId = $response->getCaptureResult()->getCaptureDetails()->getAmazonCaptureId();

		// Check for the presence of the ipn notification
		$this->waitForNotificationToBeProcessedBeforeContinuing(
            $captureId,
            "CaptureNotification"
        );
		
		validateThatCaptureIsCompleted ( $response->getCaptureResult () );
		$this->printResponseToWebpage ( "printCaptureResponse", array (
				$response 
		) );
		
		return $response;
	}
	private function _waitForProviderCreditSummaryList($captureResponse) {
		$amazonCaptureId = $captureResponse->getCaptureResult()->getCaptureDetails()->getAmazonCaptureId ();
		
		$maxSleepTime = 60;
		$getCaptureDetailsResponse = null;
		$providerCreditSummaryList = null;
		while ( is_null ( $providerCreditSummaryList ) && $maxSleepTime > 0 ) {
			sleep ( 5 );
			$maxSleepTime -= 5;
			$getCaptureDetailsResponse = $this->callStepAndCheckForException ( 'getCaptureDetails', array (
					$amazonCaptureId 
			) );
			$providerCreditSummaryList = $getCaptureDetailsResponse->getGetCaptureDetailsResult()->getCaptureDetails()->getProviderCreditSummaryList();
		}
		
		validateThatCaptureIsCompleted ( $getCaptureDetailsResponse->getGetCaptureDetailsResult () );
		$this->printResponseToWebpage ( "printGetCaptureDetailsResponse", array (
				$getCaptureDetailsResponse 
		) );
		if (is_null($providerCreditSummaryList)) {
            throw new ErrorException("No providerCreditSummaryList found in getCaptureDetails response");
        }
		return $providerCreditSummaryList;
	}
	
	/**
	 */
	private function _confirmProviderCreditSummaries($providerCreditSummaryList) {
		if (! is_null ( $providerCreditSummaryList ) && $providerCreditSummaryList->isSetmember()) {
			$values = $providerCreditSummaryList->getmember ();
			foreach ( $values as $value ) {
				if ($value->isSetProviderCreditId ()) {
					$providerCreditId = $value->getProviderCreditId ();
					// Check for the presence of the ipn notification
					$this->waitForNotificationToBeProcessedBeforeContinuing ( $providerCreditId, "ProviderCreditNotification" );
		
					$response = $this->callStepAndCheckForException ( 'getProviderCreditDetails', array (
							$providerCreditId 
					) );
					$this->printResponseToWebpage ( "printProviderCreditDetailsResponse", array (
							$response 
					) );
				}
			}
		}
	}
	
	/**
	 * Close this order reference to indicate that the order is complete, and
	 * no further authorizations and captures will be performed on this order
	 *
	 * @return no value
	 */
	private function _closeOrderReference() {
		$response = $this->callStepAndCheckForException ( 'closeOrderReference' );
		$this->printResponseToWebpage ( "printCloseOrderReferenceResponse", array (
				$response 
		) );
	}
	
	/**
	 * Get the authorization type selected from the request params
	 * i.e.
	 * Check if fast auth was selected.
	 *
	 * @return true for fast authorization, false for regular authorization
	 */
	private function _getAuthOptionFromQueryParams() {
		$authOption = $this->queryStringParams ['authOption'];
		return $authOption;
	}
	
	/**
	 * Capture the pre tax order amount from standard input,
	 * making sure that it is a numeric string
	 *
	 * @return string total amount of the order before tax and shipping charges
	 */
	private function _getPreTaxAndShippingOrderAmountFromQueryParams() {
		$orderAmount = $this->queryStringParams ['orderTotal'];
		return $orderAmount;
	}
	
	/**
	 * Capture the shipping type for this order, which determines
	 * the shipping charge
	 *
	 * @return number selected shipping type index
	 */
	private function _getShippingTypeFromQueryParams() {
		$shippingType = $this->queryStringParams ['shipping'];
		return $shippingType - 1;
	}
	
	/**
	 * Get provider id and credit amount for this order
	 */
	private function _getProviderCreditInfoFromQueryParams() {
		$providerId = $this->queryStringParams ['providerId'];
		$providerCreditAmount = $this->queryStringParams ['providerCreditAmount'];
		if(empty($providerId) || empty($providerCreditAmount))
			throw new ErrorException("No provider id or provider credit amount empty.");
		return array($providerId , $providerCreditAmount);
	}
}

$result = new ProviderCheckoutResult ( $_SERVER ['QUERY_STRING'] );
$result->run ();

?>