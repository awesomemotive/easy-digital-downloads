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
require_once 'OffAmazonPaymentsService/Samples/AddressConsentSample.php';

/**
 * This script shows the difference between the the getOrderReferenceDetails call with
 * and without an LwA access token for a DRAFT Order Reference object 
 *
 * Note that this sample is currently applicable only for US customers of 
 * Login and Pay with Amazon
 *
 */
class AddressConsentSampleResult extends WebServerExample
{
	/**
	 * Create a new instance of the Address Consent Example
	 *
	 * @param string $queryString url query string
	 *
	 * @return void
	 */
	public function __construct($queryString)
	{
		parent::__construct($queryString);
		 
		$this->exampleClass = new AddressConsentSample(
				new OffAmazonPaymentsService_Client(),
				$this->queryStringParams['orderReferenceId']
		);
	}
	
	/**
	 * Execute the example
	 *
	 * @return void
	 */
	public function run()
	{
		echo '<html><head><title>Address consent result</title></head><body>';
		$noConsentResponse = $this->_getOrderReferenceDetailsWithoutAddressConsent();
		$this->_validateOrderReferenceIsInACorrectState($noConsentResponse);
		
		$consentResponse = $this->_getOrderReferenceDetailsWithAddressConsent();
		
		$this->_printOrderReferenceResponses($noConsentResponse, $consentResponse);
		echo '</body></html>';
	}
	
	/**
	 * Get the order reference detail without passing in the address consent token
	 *
	 * @returns OffAmazonPayments_Model_GetOrderReferenceDetailsResponse object
	 */
	private function _getOrderReferenceDetailsWithoutAddressConsent()
	{
		return $this->callStepAndCheckForException('getOrderReferenceDetails');
	}
	
	/**
	 * Validate that the order reference is in a correct state
	 * 
	 * @param OffAmazonPayments_Model_GetOrderReferenceDetailsResponse in an unverified state
	 * 
	 * @return void
	 * @throws ErrorException if the state does not match the expected state
	 */
	private function _validateOrderReferenceIsInACorrectState($getOrderReferenceDetailsResponse)
	{
		$this->exampleClass->validateOrderReferenceIsInACorrectState($getOrderReferenceDetailsResponse);
	}
	
	/**
	 * Get the order reference details using the address consent token
	 */
	private function _getOrderReferenceDetailsWithAddressConsent()
	{
		return $this->callStepAndCheckForException(
			'getOrderReferenceDetails',
			array(
				$this->queryStringParams['accessToken']
			)
		);
	}
	
	/**
	 * Merge the two response into a single output buffer so that the contents are listed side by side
	 * 
	 */
	private function _printOrderReferenceResponses($respWithoutConsent, $respWithConsent)
	{
		echo '<div style="width:100%">';
		echo '<div style="float:left; width:45%">';
		echo '<div style="font-weight:bold">GetOrderReferenceDetails result without address consent token</div>';
		echo '<div>';
		echo $this->printResponseToWebpage(
			"printGetOrderReferenceDetailsResponse", array($respWithoutConsent)
		);
		echo '</div>';
		echo '</div>';
		echo '<div style="float:right; width:45%; border-left:dashed; padding-left:1%">';
		echo '<div style="font-weight:bold">GetOrderReferenceDetails result with address consent token</div>';
		echo '<div>';
		echo $this->printResponseToWebpage(
			"printGetOrderReferenceDetailsResponse", array($respWithConsent)
		);
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}

$addressConsentSampleResult = new AddressConsentSampleResult($_SERVER['QUERY_STRING']);
$addressConsentSampleResult->run();

?>
