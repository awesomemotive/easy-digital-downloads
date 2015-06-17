<?php

/**
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
 */

/*
 * Refund example This demonstrates the scenario where merchant a merchant needs to perform a reverseProviderCredit on a previously captured amount for a closed order reference This example will show the following service calls: - GetOrderReferenceDetails - GetCaptureDetails - Refund - GetRefundDetails
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/.config.inc.php';

/**
 * ReverseProviderCreditExample includes the logic
 * required to reverseProviderCredit a capture,
 * which transfers the funds from
 * the merchant back to the buyer
 */
class ReverseProviderCreditExample {
	private $_sellerId;
	private $_service;
	
	/**
	 * Create a new instance of the reverseProviderCredit example case
	 *
	 * @param OffAmazonPaymentsService_Client $service
	 *        	instance of the service
	 *        	client
	 *        	
	 * @return new ReverseProviderCreditExample
	 */
	public function __construct($service) {
		$this->_service = $service;
		$this->_sellerId = $this->_service->getMerchantValues ()->getMerchantId ();
	}
	
	/**
	 * Perform the reverseProviderCredit to transfer the amount from provider
	 * to merchant
	 *
	 * @param string $reverseProviderCreditAmount
	 *        	amount to reverseProviderCredit to the merchant
	 * @param string $reverseProviderCreditCurrency
	 *        	currency of the reverseProviderCredit
	 *        	
	 * @return void
	 */
	public function reverseToMerchant($amazonProviderCreditId, $reverseAmount, $creditReversalReferenceId) {
		$reversePrice = new OffAmazonPaymentsService_Model_Price ();
		$reversePrice->setCurrencyCode ( $this->_service->getMerchantValues ()->getCurrency () );
		$reversePrice->setAmount ( $reverseAmount );
		
		$reverseProviderCreditRequest = new OffAmazonPaymentsService_Model_ReverseProviderCreditRequest ();
		$reverseProviderCreditRequest->setSellerId ( $this->_sellerId );
		$reverseProviderCreditRequest->setAmazonProviderCreditId ( $amazonProviderCreditId );
		$reverseProviderCreditRequest->setCreditReversalReferenceId ( $creditReversalReferenceId );
		$reverseProviderCreditRequest->setCreditReversalAmount ( $reversePrice );
		
		return $this->_service->reverseProviderCredit ( $reverseProviderCreditRequest );
	}
}
?>