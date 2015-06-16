<?php

/*
 * Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved. Licensed under the Apache License, Version 2.0 (the "License"); You may not use this file except in compliance with the License. You may obtain a copy of the License at: http://aws.amazon.com/apache2.0 This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License. /* Refund command line example This demonstrates the scenario where merchant a merchant needs to perform a reverse on a previously captured amount for a closed order reference See ReverseProviderCreditExample.php for further information
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/.config.inc.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/ReverseProviderCreditSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/ReverseProviderCreditExample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/CLIExample.php';

/**
 * ReverseProviderCreditExampleCLI class captures input from stdin and prints to stdout,
 * and drives the reverse example
 */
class ReverseProviderCreditExampleCLI extends CLIExample {
	/**
	 * Create a new instance of the cli example and validate command line arguments
	 *
	 * @param array $argv
	 *        	arguments to the application passed from the command line
	 */
	public function __construct($argv) {
		
		/*
		 * Instantiate Implementation of OffAmazonPaymentsService client Merchant ids and access keys are defined in the constants are defined in the OffAmazonPaymentsService.config.inc.php file in the parent directory of this server
		 */
		$this->exampleClass = new ReverseProviderCreditExample ( new OffAmazonPaymentsService_Client () );
	}
	
	/**
	 * Run all the steps for the sample in sequence
	 */
	public function runSample() {
		$amazonProviderCreditId = $this->getProviderCreditId ();
		$reverseAmount = $this->_getCreditReversalAmount ();
		$creditReversalReferenceId = str_replace("-", "", $amazonProviderCreditId) . "q" . rand(1, 1000);
		$this->_reverseToMerchant ( $amazonProviderCreditId, $reverseAmount, $creditReversalReferenceId );
		
		print "Reverse provider credit completed" . PHP_EOL;
	}
	
	/**
	 * Perform the reverse to transfer the amount from seller
	 * to merchant
	 *
	 * @param string $reverseAmount
	 *        	amount to reverse to the buyer
	 * @param string $reverseCurrency
	 *        	currency of the reverse
	 */
	private function _reverseToMerchant($amazonProviderCreditId, $reverseAmount, $creditReversalReferenceId) {
		$response = $this->callStepAndCheckForException ( 'reverseToMerchant', array (
				$amazonProviderCreditId,
				$reverseAmount,
				$creditReversalReferenceId 
		) );
		
		printProviderCreditReversalDetailsResponse ( $response );
	}
	
	/**
	 *
	 * @return AmazonProviderCreditId
	 */
	private function getProviderCreditId() {

		print PHP_EOL."Enter the AmazonProviderCreditId >> ";
		do {
			$amazonProviderCreditId = trim ( fgets ( STDIN ) );
		} while ( ! is_string ( $amazonProviderCreditId ) );
	
		return $amazonProviderCreditId;
	}
	
	/**
	 * Get the amount to reverse
	 *
	 * @return string reverse amount, as a numeric string`
	 */
	private function _getCreditReversalAmount() {
		print PHP_EOL."Enter the amount to reverse from the provider (to 2 decimal places):";
		do {
			$reverseAmount = trim ( fgets ( STDIN ) );
		} while ( ! is_numeric ( $reverseAmount ) );
	
		return $reverseAmount;
	}
}

$reverseProviderCreditExample = new ReverseProviderCreditExampleCLI ( $argv );
$reverseProviderCreditExample->runSample ();

?>