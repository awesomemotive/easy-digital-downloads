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
 * Capture  Sample
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 
             
/**
  * Capture Action Sample
  
  * @param OffAmazonPaymentsService_Interface $service instance of OffAmazonPaymentsService_Interface
  * @param mixed $request OffAmazonPaymentsService_Model_Capture or array of parameters
  */
function printCaptureResponse($response) 
{

    print "Service Response" . PHP_EOL;
    print "=============================================================================" . PHP_EOL;

    print "        CaptureResponse" . PHP_EOL;
    if ($response->isSetCaptureResult()) { 
        print "            CaptureResult" . PHP_EOL;
        $captureResult = $response->getCaptureResult();
        if ($captureResult->isSetCaptureDetails()) { 
            print "                CaptureDetails" . PHP_EOL;
            $captureDetails = $captureResult->getCaptureDetails();
            if ($captureDetails->isSetAmazonCaptureId()) {
                print "                    AmazonCaptureId" . PHP_EOL;
                print "                        " . $captureDetails->getAmazonCaptureId() . PHP_EOL;
            }
            if ($captureDetails->isSetCaptureReferenceId()) {
                print "                    CaptureReferenceId" . PHP_EOL;
                print "                        " . $captureDetails->getCaptureReferenceId() . PHP_EOL;
            }
            if ($captureDetails->isSetSellerCaptureNote()) {
                print "                    SellerCaptureNote" . PHP_EOL;
                print "                        " . $captureDetails->getSellerCaptureNote() . PHP_EOL;
            }
            if ($captureDetails->isSetCaptureAmount()) { 
                print "                    CaptureAmount" . PHP_EOL;
                $captureAmount = $captureDetails->getCaptureAmount();
                if ($captureAmount->isSetAmount()) {
                    print "                        Amount" . PHP_EOL;
                    print "                            " . $captureAmount->getAmount() . PHP_EOL;
                }
                if ($captureAmount->isSetCurrencyCode()) {
                    print "                        CurrencyCode" . PHP_EOL;
                    print "                            " . $captureAmount->getCurrencyCode() . PHP_EOL;
                }
            } 
            if ($captureDetails->isSetRefundedAmount()) { 
                print "                    RefundedAmount" . PHP_EOL;
                $refundedAmount = $captureDetails->getRefundedAmount();
                if ($refundedAmount->isSetAmount()) {
                    print "                        Amount" . PHP_EOL;
                    print "                            " . $refundedAmount->getAmount() . PHP_EOL;
                }
                if ($refundedAmount->isSetCurrencyCode()) {
                    print "                        CurrencyCode" . PHP_EOL;
                    print "                            " . $refundedAmount->getCurrencyCode() . PHP_EOL;
                }
            } 
            if ($captureDetails->isSetCaptureFee()) { 
                print "                    CaptureFee" . PHP_EOL;
                $captureFee = $captureDetails->getCaptureFee();
                if ($captureFee->isSetAmount()) {
                    print "                        Amount" . PHP_EOL;
                    print "                            " . $captureFee->getAmount() . PHP_EOL;
                }
                if ($captureFee->isSetCurrencyCode()) {
                    print "                        CurrencyCode" . PHP_EOL;
                    print "                            " . $captureFee->getCurrencyCode() . PHP_EOL;
                }
            } 
            if ($captureDetails->isSetIdList()) { 
                print "                    IdList" . PHP_EOL;
                $idList = $captureDetails->getIdList();
                $memberList  =  $idList->getmember();
                foreach ($memberList as $member) { 
                    print "                        member" . PHP_EOL;
                    print "                            " . $member;
                }	
            } 
            if ($captureDetails->isSetCreationTimestamp()) {
                print "                    CreationTimestamp" . PHP_EOL;
                print "                        " . $captureDetails->getCreationTimestamp() . PHP_EOL;
            }
            if ($captureDetails->isSetCaptureStatus()) { 
                print "                    CaptureStatus" . PHP_EOL;
                $captureStatus = $captureDetails->getCaptureStatus();
                if ($captureStatus->isSetState()) {
                    print "                        State" . PHP_EOL;
                    print "                            " . $captureStatus->getState() . PHP_EOL;
                }
                if ($captureStatus->isSetLastUpdateTimestamp()) {
                    print "                        LastUpdateTimestamp" . PHP_EOL;
                    print "                            " . $captureStatus->getLastUpdateTimestamp() . PHP_EOL;
                }
                if ($captureStatus->isSetReasonCode()) {
                    print "                        ReasonCode" . PHP_EOL;
                    print "                            " . $captureStatus->getReasonCode() . PHP_EOL;
                }
                if ($captureStatus->isSetReasonDescription()) {
                    print "                        ReasonDescription" . PHP_EOL;
                    print "                            " . $captureStatus->getReasonDescription() . PHP_EOL;
                }
                if ($captureDetails->isSetSoftDescriptor()) {
                    print "                        SoftDescriptor" . PHP_EOL;
                    print "                            " . $captureDetails->getSoftDescriptor() . PHP_EOL;
                }
            }
            if($captureDetails->isSetProviderCreditSummaryList()){
            	print "                    ProviderCreditSummaryList" . PHP_EOL;
            	$providerCreditSummaryList = $captureDetails->getProviderCreditSummaryList();
            	if($providerCreditSummaryList->isSetmember()){
            		$values = $providerCreditSummaryList->getmember();
            		foreach($values as $value){
            			print "                        ProviderCreditSummary" . PHP_EOL;
            			if($value->isSetProviderId()){
            				print "                            ProviderId" . PHP_EOL;
            				print "                                ".$value->getProviderId() . PHP_EOL;
            			}
            			if($value->isSetProviderCreditId()){
            				print "                            ProviderCreditId" . PHP_EOL;
            				print "                                ".$value->getProviderCreditId() . PHP_EOL;
            			}
            		}
            	}
            } 
        } 
    } 
    if ($response->isSetResponseMetadata()) { 
        print "            ResponseMetadata" . PHP_EOL;
        $responseMetadata = $response->getResponseMetadata();
        if ($responseMetadata->isSetRequestId()) {
            print "                RequestId" . PHP_EOL;
            print "                    " . $responseMetadata->getRequestId() . PHP_EOL;
        }
    } 

   	print "            ResponseHeaderMetadata: " . 
    $response->getResponseHeaderMetadata() . PHP_EOL;
   	return $response;
}
?>