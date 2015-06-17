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
require_once realpath ( dirname ( __FILE__ ) . "/.config.inc.php" );
require_once 'OffAmazonPaymentsNotifications/Samples/WebServerExample.php';
require_once 'OffAmazonPaymentsService/Client.php';
require_once 'OffAmazonPaymentsService/Samples/RefundExample.php';
require_once 'OffAmazonPaymentsService/Samples/RefundSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetCaptureDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetRefundDetailsSample.php';
require_once 'OffAmazonPaymentsService/Samples/GetProviderCreditReversalDetailsSample.php';

/**
 * This script simulates a simple checkout example and generates
 * html for the page
 */
class ProviderRefundResult extends WebServerExample {
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
		
		$this->exampleClass = new RefundExample ( new OffAmazonPaymentsService_Client (), $this->queryStringParams ['orderReferenceId'], $this->queryStringParams ['amazonCaptureId'] );
	}
	
	/**
	 * Execute the example
	 *
	 * @return void
	 */
	public function run() {
		$this->_getOrderReferenceDetails ();
		$refundAmount = $this->queryStringParams ["refundAmount"];
		$providerCreditReversalInfo = $this->_getProviderCreditReversalInfoFromQueryParams();
		$amazonRefundReferenceId = $this->_refundOrder ($refundAmount , $this->currencyCode, $providerCreditReversalInfo );
		$providerCreditReversalSummaryList = $this->_waitUntilRefundProcessingIsCompleted ( $amazonRefundReferenceId );
		$this->_confirmProviderCreditReversalSummaries ( $providerCreditReversalSummaryList );
		print HTML_LB . HTML_LB . "Provider Refund Sample is Complete";
	}
	
	/**
	 * Get the order reference details to find to the state
	 * of the order reference
	 *
	 * @return void
	 */
	private function _getOrderReferenceDetails() {
		$response = $this->callStepAndCheckForException ( 'getOrderReferenceDetails' );
		echo $this->printResponseToWebpage ( "printGetOrderReferenceDetailsResponse", array (
				$response 
		) );
	}
	
	/**
	 * Perform the refund to transfer the amount from seller
	 * to buyer
	 *
	 * @param string $refundAmount
	 *        	amount to refund to the buyer
	 * @param string $refundCurrency
	 *        	currency of the refund
	 */
	private function _refundOrder($refundAmount, $refundCurrency, $providerCreditReversalInfo = null) {
		$response = $this->callStepAndCheckForException ( 'refundToBuyer', array (
				$refundAmount,
				$refundCurrency,
				$providerCreditReversalInfo 
		) );
		
		$this->printResponseToWebpage ( "printRefundResponse", array (
				$response 
		) );
		
		return $response->getRefundResult ()->getRefundDetails ()->getAmazonRefundId ();
	}
	
	/**
	 * Poll the API for the status of the Refund Request, and continue
	 * once the status has been updated
	 * Throw an error if the status is not equal to OPEN
	 *
	 * @param string $amazonRefundReferenceId
	 *        	refund transaction to query
	 *        	
	 * @return No value
	 */
	private function _waitUntilRefundProcessingIsCompleted($amazonRefundReferenceId) {
		// Check for the presence of the ipn notification
		$this->waitForNotificationToBeProcessedBeforeContinuing ( $amazonRefundReferenceId, "RefundNotification" );
		
		$response = null;
		$providerCreditReversalSummaryList = null;
		$maxSleepTime = 60;
		while ( is_null ( $providerCreditReversalSummaryList ) && $maxSleepTime > 0 ) {
			sleep ( 5 );
			$maxSleepTime -= 5;
			$response = $this->callStepAndCheckForException ( 'getRefundDetails', array (
					$amazonRefundReferenceId 
			) );
			
			$providerCreditReversalSummaryList = $response->getGetRefundDetailsResult ()->getRefundDetails ()->getProviderCreditReversalSummaryList ();
		}
		
		validateThatRefundIsCompleted ( $response->getGetRefundDetailsResult ()->getRefundDetails () );
		$this->printResponseToWebpage ( "printGetRefundDetailsResponse", array (
				$response 
		) );
		if (is_null($providerCreditReversalSummaryList)) {
            throw new ErrorException("No providerCreditReversalSummaryList found in getRefundDetails response");
        }
		return $providerCreditReversalSummaryList;
	}
	
	/**
	 */
	private function _confirmProviderCreditReversalSummaries($providerCreditReversalSummaryList) {
		if (! is_null ( $providerCreditReversalSummaryList ) && $providerCreditReversalSummaryList->isSetmember()) {
			$values = $providerCreditReversalSummaryList->getmember ();
			foreach ( $values as $value ) {
				if ($value->isSetProviderCreditReversalId ()) {
					$providerCreditReversalId = $value->getProviderCreditReversalId ();
					// Check for the presence of the ipn notification
					$this->waitForNotificationToBeProcessedBeforeContinuing ( $providerCreditReversalId, "ProviderCreditReversalNotification" );
		
					$response = $this->callStepAndCheckForException ( 'getProviderCreditReversalDetails', array (
							$providerCreditReversalId 
					) );
					
					$this->printResponseToWebpage ( "printGetProviderCreditReversalDetailsResponse", array (
							$response 
					) );
				}
			}
		}
	}
	
	/**
	 * Get provider id and credit amount for this order
	 */
	private function _getProviderCreditReversalInfoFromQueryParams() {
		$providerId = $this->queryStringParams ['providerId'];
		$providerCreditReversalAmount = $this->queryStringParams ['creditReversalAmount'];
		if(empty($providerId) || empty($providerCreditReversalAmount))
			throw new ErrorException("No provider id or provider credit reversal amount empty.");
		return array($providerId , $providerCreditReversalAmount);
	}
	
}

$result = new ProviderRefundResult ( $_SERVER ['QUERY_STRING'] );
$result->run ();

?>