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
require_once 'OffAmazonPaymentsService/Samples/ReverseProviderCreditExample.php';
require_once 'OffAmazonPaymentsService/Samples/ReverseProviderCreditSample.php';

/**
 * This script simulates a simple checkout example and generates
 * html for the page
 */
class ReverseProviderCreditResult extends WebServerExample {
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
		
		$this->exampleClass = new ReverseProviderCreditExample ( new OffAmazonPaymentsService_Client () );
	}
	
	/**
	 * Execute the example
	 *
	 * @return void
	 */
	public function run() {
		$amazonProviderCreditId = $this->queryStringParams ['providerCreditId'];
		$creditReversalAmount = $this->queryStringParams ['creditReversalAmount'];
		$creditReversalReferenceId = str_replace ( "-", "", $amazonProviderCreditId ) . "q" . rand ( 1, 1000 );
		$this->_reverseToMerchant ( $amazonProviderCreditId, $creditReversalAmount, $creditReversalReferenceId );
		print "Reverse provider credit completed" . PHP_EOL;
	}
	
	/**
	 * Perform the reverse to transfer the amount from seller
	 * to merchant
	 *
	 * @param string $creditReversalAmount
	 *        	amount to reverse to the buyer
	 * @param string $reverseCurrency
	 *        	currency of the reverse
	 */
	private function _reverseToMerchant($amazonProviderCreditId, $creditReversalAmount, $creditReversalReferenceId) {
		$response = $this->callStepAndCheckForException ( 'reverseToMerchant', array (
				$amazonProviderCreditId,
				$creditReversalAmount,
				$creditReversalReferenceId 
		) );
		
		$this->printResponseToWebpage ( "printProviderCreditReversalDetailsResponse", array (
				$response 
		) );
	}
}

$result = new ReverseProviderCreditResult ( $_SERVER ['QUERY_STRING'] );
$result->run ();
?>