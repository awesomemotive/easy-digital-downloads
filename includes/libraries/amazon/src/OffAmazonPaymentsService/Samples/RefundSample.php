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
 * Refund  Sample
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 

/**
 * Throw an error if the refund is not in the
 * completed status
 *
 * @param OffAmazonPaymentsService_Model_RefundDetails $response service response
 * @throws ErrorException
 */
function validateThatRefundIsCompleted($refundDetails)
{
    if (strcasecmp($refundDetails->getRefundStatus()->getState(),"Completed") != 0) {
        throw new ErrorException(
            "Error with refund " .
            $refundDetails->getRefundReferenceId() . " - state is " .
            $refundDetails->getRefundStatus()->getState() . PHP_EOL
        );
    }
}
               
/**
  * Refund Action Sample
  
  * @param OffAmazonPaymentsService_Interface $service instance of OffAmazonPaymentsService_Interface
  * @param mixed $request OffAmazonPaymentsService_Model_Refund or array of parameters
  */
function printRefundResponse($response) 
{

                print "Service Response" . PHP_EOL;
                print "=============================================================================" . PHP_EOL;

                print "        RefundResponse" . PHP_EOL;
                if ($response->isSetRefundResult()) { 
                    print "            RefundResult" . PHP_EOL;
                    $refundResult = $response->getRefundResult();
                    if ($refundResult->isSetRefundDetails()) { 
                        print "                RefundDetails" . PHP_EOL;
                        $refundDetails = $refundResult->getRefundDetails();
                        if ($refundDetails->isSetAmazonRefundId()) 
                        {
                            print "                    AmazonRefundId" . PHP_EOL;
                            print "                        " . $refundDetails->getAmazonRefundId() . PHP_EOL;
                        }
                        if ($refundDetails->isSetRefundReferenceId()) 
                        {
                            print "                    RefundReferenceId" . PHP_EOL;
                            print "                        " . $refundDetails->getRefundReferenceId() . PHP_EOL;
                        }
                        if ($refundDetails->isSetSellerRefundNote()) 
                        {
                            print "                    SellerRefundNote" . PHP_EOL;
                            print "                        " . $refundDetails->getSellerRefundNote() . PHP_EOL;
                        }
                        if ($refundDetails->isSetRefundType()) 
                        {
                            print "                    RefundType" . PHP_EOL;
                            print "                        " . $refundDetails->getRefundType() . PHP_EOL;
                        }
                        if ($refundDetails->isSetRefundAmount()) { 
                            print "                    RefundAmount" . PHP_EOL;
                            $refundAmount = $refundDetails->getRefundAmount();
                            if ($refundAmount->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $refundAmount->getAmount() . PHP_EOL;
                            }
                            if ($refundAmount->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $refundAmount->getCurrencyCode() . PHP_EOL;
                            }
                        } 
                        if ($refundDetails->isSetFeeRefunded()) { 
                            print "                    FeeRefunded" . PHP_EOL;
                            $feeRefunded = $refundDetails->getFeeRefunded();
                            if ($feeRefunded->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $feeRefunded->getAmount() . PHP_EOL;
                            }
                            if ($feeRefunded->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $feeRefunded->getCurrencyCode() . PHP_EOL;
                            }
                        } 
                        if ($refundDetails->isSetCreationTimestamp()) 
                        {
                            print "                    CreationTimestamp" . PHP_EOL;
                            print "                        " . $refundDetails->getCreationTimestamp() . PHP_EOL;
                        }
                        if ($refundDetails->isSetRefundStatus()) { 
                            print "                    RefundStatus" . PHP_EOL;
                            $refundStatus = $refundDetails->getRefundStatus();
                            if ($refundStatus->isSetState()) 
                            {
                                print "                        State" . PHP_EOL;
                                print "                            " . $refundStatus->getState() . PHP_EOL;
                            }
                            if ($refundStatus->isSetLastUpdateTimestamp()) 
                            {
                                print "                        LastUpdateTimestamp" . PHP_EOL;
                                print "                            " . $refundStatus->getLastUpdateTimestamp() . PHP_EOL;
                            }
                            if ($refundStatus->isSetReasonCode()) 
                            {
                                print "                        ReasonCode" . PHP_EOL;
                                print "                            " . $refundStatus->getReasonCode() . PHP_EOL;
                            }
                            if ($refundStatus->isSetReasonDescription()) 
                            {
                                print "                        ReasonDescription" . PHP_EOL;
                                print "                            " . $refundStatus->getReasonDescription() . PHP_EOL;
                            }
                        }
                        if ($refundDetails->isSetSoftDescriptor())
                        {
                            print "        SoftDescriptor" . PHP_EOL;
                            print "            " . $refundDetails->getSoftDescriptor() . PHP_EOL;
                        }
                        if($refundDetails->isSetProviderCreditReversalSummaryList()){
                        	print "                    ProviderCreditReversalSummaryList" . PHP_EOL;
                        	$providerCreditReversalSummaryList = $refundDetails->getProviderCreditReversalSummaryList();
                        	if($providerCreditReversalSummaryList->isSetProviderCreditReversalSummary()){
                        		$values = $providerCreditReversalSummaryList->getProviderCreditReversalSummary();
                        		foreach($values as $value){
                        			print "                        ProviderCreditReversalSummary" . PHP_EOL;
                        			if($value->isSetProviderId()){
                        				print "                            ProviderId" . PHP_EOL;
                        				print "                                ".$value->getProviderId() . PHP_EOL;
                        			}
                        			if($value->isSetProviderCreditReversalId()){
                        				print "                            ProviderCreditReversalId" . PHP_EOL;
                        				print "                                ".$value->getProviderCreditReversalId() . PHP_EOL;
                        			}
                        		}
                        	}
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 
                    print "            ResponseMetadata" . PHP_EOL;
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        print "                RequestId" . PHP_EOL;
                        print "                    " . $responseMetadata->getRequestId() . PHP_EOL;
                    }
                } 

   	print "            ResponseHeaderMetadata: " . 
    $response->getResponseHeaderMetadata() . PHP_EOL;
        	
   	return $response;
}
                                            
?>