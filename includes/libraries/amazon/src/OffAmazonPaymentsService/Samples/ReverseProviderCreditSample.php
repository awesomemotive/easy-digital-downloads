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

/**
 * ReverseProviderCredit Sample
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/.config.inc.php';

/**
 * ReverseProviderCredit Sample
 */
function printProviderCreditReversalDetailsResponse($response) {
	print "Service Response" . PHP_EOL;
	print "=============================================================================" . PHP_EOL;
	if ($response->isSetReverseProviderCreditResult ()) {
		print "            ReverseProviderCreditResult" . PHP_EOL;
		$reversalProviderCreditResult = $response->getReverseProviderCreditResult ();
		if ($reversalProviderCreditResult->isSetProviderCreditReversalDetails ()) {
			print "                ProviderCreditReversalDetails" . PHP_EOL;
			$providerCreditReversalDetails = $reversalProviderCreditResult->getProviderCreditReversalDetails ();
			if ($providerCreditReversalDetails->isSetAmazonProviderCreditReversalId ()) {
				print "                    AmazonProviderCreditReversalId" . PHP_EOL;
				print "                        " . $providerCreditReversalDetails->getAmazonProviderCreditReversalId () . PHP_EOL;
			}
			if ($providerCreditReversalDetails->isSetCreditReversalReferenceId ()) {
				print "                    CreditReversalReferenceId" . PHP_EOL;
				print "                        " . $providerCreditReversalDetails->getCreditReversalReferenceId () . PHP_EOL;
			}
			if ($providerCreditReversalDetails->isSetCreditReversalAmount ()) {
				print "                    CreditReversalAmount" . PHP_EOL;
				$creditReversalAmount = $providerCreditReversalDetails->getCreditReversalAmount ();
				if ($creditReversalAmount->isSetAmount ()) {
					print "                        Amount" . PHP_EOL;
					print "                            " . $creditReversalAmount->getAmount () . PHP_EOL;
				}
				if ($creditReversalAmount->isSetCurrencyCode ()) {
					print "                        CurrencyCode" . PHP_EOL;
					print "                            " . $creditReversalAmount->getCurrencyCode () . PHP_EOL;
				}
			}
			if ($providerCreditReversalDetails->isSetCreationTimestamp ()) {
				print "                    CreationTimestamp" . PHP_EOL;
				print "                        " . $providerCreditReversalDetails->getCreationTimestamp () . PHP_EOL;
			}
			if ($providerCreditReversalDetails->isSetCreditReversalStatus ()) {
				print "                    CreditReversalStatus" . PHP_EOL;
				$creditStatus = $providerCreditReversalDetails->getCreditReversalStatus ();
				if ($creditStatus->isSetState ()) {
					print "                        State" . PHP_EOL;
					print "                            " . $creditStatus->getState () . PHP_EOL;
				}
				if ($creditStatus->isSetLastUpdateTimestamp ()) {
					print "                        LastUpdateTimestamp" . PHP_EOL;
					print "                            " . $creditStatus->getLastUpdateTimestamp () . PHP_EOL;
				}
				if ($creditStatus->isSetReasonCode ()) {
					print "                        ReasonCode" . PHP_EOL;
					print "                            " . $creditStatus->getReasonCode () . PHP_EOL;
				}
				if ($creditStatus->isSetReasonDescription ()) {
					print "                        ReasonDescription" . PHP_EOL;
					print "                            " . $creditStatus->getReasonDescription () . PHP_EOL;
				}
			}
			if ($providerCreditReversalDetails->isSetCreditReversalNote ()) {
				print "                    CreditReversalNote" . PHP_EOL;
				print "                        " . $providerCreditReversalDetails->getCreditReversalNote () . PHP_EOL;
			}
		}
	}
	if ($response->isSetResponseMetadata ()) {
		print "            ResponseMetadata" . PHP_EOL;
		$responseMetadata = $response->getResponseMetadata ();
		if ($responseMetadata->isSetRequestId ()) {
			print "                RequestId" . PHP_EOL;
			print "                    " . $responseMetadata->getRequestId () . PHP_EOL;
		}
	}
	
	print "            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata () . PHP_EOL;
	
	return $response;
}
?>                             