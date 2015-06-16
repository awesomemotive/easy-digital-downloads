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
 * GetProviderCreditDetails Sample
 */
require_once realpath ( dirname ( __FILE__ ) ) . '/.config.inc.php';

/**
 * GetProviderCreditDetails Sample
 */
function printProviderCreditDetailsResponse($response) {
	print "Service Response" . PHP_EOL;
	print "=============================================================================" . PHP_EOL;
	if ($response->isSetGetProviderCreditDetailsResult ()) {
		print "            GetProviderCreditDetailsResult" . PHP_EOL;
		$getProviderCreditDetailsResult = $response->getGetProviderCreditDetailsResult ();
		if ($getProviderCreditDetailsResult->isSetProviderCreditDetails ()) {
			print "                ProviderCreditDetails" . PHP_EOL;
			$providerCreditDetails = $getProviderCreditDetailsResult->getProviderCreditDetails ();
			if ($providerCreditDetails->isSetAmazonProviderCreditId ()) {
				print "                    AmazonProviderCreditId" . PHP_EOL;
				print "                        " . $providerCreditDetails->getAmazonProviderCreditId () . PHP_EOL;
			}
			if ($providerCreditDetails->isSetCreditReferenceId ()) {
				print "                    CreditReferenceId" . PHP_EOL;
				print "                        " . $providerCreditDetails->getCreditReferenceId () . PHP_EOL;
			}
			if ($providerCreditDetails->isSetCreditAmount ()) {
				print "                    CreditAmount" . PHP_EOL;
				$creditAmount = $providerCreditDetails->getCreditAmount ();
				if ($creditAmount->isSetAmount ()) {
					print "                        Amount" . PHP_EOL;
					print "                            " . $creditAmount->getAmount () . PHP_EOL;
				}
				if ($creditAmount->isSetCurrencyCode ()) {
					print "                        CurrencyCode" . PHP_EOL;
					print "                            " . $creditAmount->getCurrencyCode () . PHP_EOL;
				}
			}
			if ($providerCreditDetails->isSetCreditReversalAmount ()) {
				print "                    CreditReversalAmount" . PHP_EOL;
				$creditReversalAmount = $providerCreditDetails->getCreditReversalAmount ();
				if ($creditReversalAmount->isSetAmount ()) {
					print "                        Amount" . PHP_EOL;
					print "                            " . $creditReversalAmount->getAmount () . PHP_EOL;
				}
				if ($creditReversalAmount->isSetCurrencyCode ()) {
					print "                        CurrencyCode" . PHP_EOL;
					print "                            " . $creditReversalAmount->getCurrencyCode () . PHP_EOL;
				}
			}
			if ($providerCreditDetails->isSetCreditReversalIdList ()) {
				print "                    CreditReversalIdList" . PHP_EOL;
				$idList = $providerCreditDetails->getCreditReversalIdList ();
				$memberList = $idList->getmember ();
				foreach ( $memberList as $member ) {
					print "                        member" . PHP_EOL;
					print "                            " . $member . PHP_EOL;
				}
			}
			if ($providerCreditDetails->isSetCreationTimestamp ()) {
				print "                    CreationTimestamp" . PHP_EOL;
				print "                        " . $providerCreditDetails->getCreationTimestamp () . PHP_EOL;
			}
			if ($providerCreditDetails->isSetCreditStatus ()) {
				print "                    CreditStatus" . PHP_EOL;
				$creditStatus = $providerCreditDetails->getCreditStatus ();
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