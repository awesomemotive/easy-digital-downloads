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
 * Get Authorization Details  Sample
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 

/**
 * Throw an error if the response does not have an authorization status
 * of open
 * 
 * @param OffAmazonPaymentsService_Model_GetAuthorizationDetails response
 * @throws ErrorException
 */
function validateThatAuthorizationIsOpen($response)
{
    $authorizationDetails = $response->getGetAuthorizationDetailsResult()
        ->getAuthorizationDetails();
     
    if (strcasecmp($authorizationDetails->getAuthorizationStatus()->getState(), "Open") != 0) {
        throw new ErrorException(
            "Error with authorization " .
            $amazonAuthorizationReferenceId . " - state is " .
            $authorizationDetails->getAuthorizationStatus()->getState() . PHP_EOL
        );
    }
}  
                                                     
/**
  * Get Authorization Details Action Sample
  
  * @param OffAmazonPaymentsService_Interface $service instance of OffAmazonPaymentsService_Interface
  * @param mixed $request OffAmazonPaymentsService_Model_GetAuthorizationDetails or array of parameters
  */
function printGetAuthorizationDetailsResponse($response) 
{

                print "Service Response" . PHP_EOL;
                print "=============================================================================" . PHP_EOL;

                print "        GetAuthorizationDetailsResponse" . PHP_EOL;
                if ($response->isSetGetAuthorizationDetailsResult()) { 
                    print "            GetAuthorizationDetailsResult" . PHP_EOL;
                    $getAuthorizationDetailsResult = $response->getGetAuthorizationDetailsResult();
                    if ($getAuthorizationDetailsResult->isSetAuthorizationDetails()) { 
                        print "                AuthorizationDetails" . PHP_EOL;
                        $authorizationDetails = $getAuthorizationDetailsResult->getAuthorizationDetails();
                        if ($authorizationDetails->isSetAmazonAuthorizationId()) 
                        {
                            print "                    AmazonAuthorizationId" . PHP_EOL;
                            print "                        " . $authorizationDetails->getAmazonAuthorizationId() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetAuthorizationReferenceId()) 
                        {
                            print "                    AuthorizationReferenceId" . PHP_EOL;
                            print "                        " . $authorizationDetails->getAuthorizationReferenceId() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetAuthorizationBillingAddress())
                        {
                        	print "                    AuthorizationBillingAddress" . PHP_EOL;
                        	$authorizationBillingAddress = $authorizationDetails->getAuthorizationBillingAddress();
                        	if ($authorizationBillingAddress->isSetName())
                        	{
                        		print "                            Name" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getName() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetAddressLine1())
                        	{
                        		print "                            AddressLine1" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getAddressLine1() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetAddressLine2())
                        	{
                        		print "                            AddressLine2" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getAddressLine2() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetAddressLine3())
                        	{
                        		print "                            AddressLine3" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getAddressLine3() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetCity())
                        	{
                        		print "                            City" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getCity() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetCounty())
                        	{
                        		print "                            County" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getCounty() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetDistrict())
                        	{
                        		print "                            District" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getDistrict() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetStateOrRegion())
                        	{
                        		print "                            StateOrRegion" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getStateOrRegion() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetPostalCode())
                        	{
                        		print "                            PostalCode" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getPostalCode() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetCountryCode())
                        	{
                        		print "                            CountryCode" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getCountryCode() . PHP_EOL;
                        	}
                        	if ($authorizationBillingAddress->isSetPhone())
                        	{
                        		print "                            Phone" . PHP_EOL;
                        		print "                                " . $authorizationBillingAddress->getPhone() . PHP_EOL;
                        	}
                        }
                        if ($authorizationDetails->isSetSellerAuthorizationNote()) 
                        {
                            print "                    SellerAuthorizationNote" . PHP_EOL;
                            print "                        " . $authorizationDetails->getSellerAuthorizationNote() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetAuthorizationAmount()) { 
                            print "                    AuthorizationAmount" . PHP_EOL;
                            $authorizationAmount = $authorizationDetails->getAuthorizationAmount();
                            if ($authorizationAmount->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $authorizationAmount->getAmount() . PHP_EOL;
                            }
                            if ($authorizationAmount->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $authorizationAmount->getCurrencyCode() . PHP_EOL;
                            }
                        } 
                        if ($authorizationDetails->isSetCapturedAmount()) { 
                            print "                    CapturedAmount" . PHP_EOL;
                            $capturedAmount = $authorizationDetails->getCapturedAmount();
                            if ($capturedAmount->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $capturedAmount->getAmount() . PHP_EOL;
                            }
                            if ($capturedAmount->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $capturedAmount->getCurrencyCode() . PHP_EOL;
                            }
                        } 
                        if ($authorizationDetails->isSetAuthorizationFee()) { 
                            print "                    AuthorizationFee" . PHP_EOL;
                            $authorizationFee = $authorizationDetails->getAuthorizationFee();
                            if ($authorizationFee->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $authorizationFee->getAmount() . PHP_EOL;
                            }
                            if ($authorizationFee->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $authorizationFee->getCurrencyCode() . PHP_EOL;
                            }
                        } 
                        if ($authorizationDetails->isSetIdList()) { 
                            print "                    IdList" . PHP_EOL;
                            $idList = $authorizationDetails->getIdList();
                            $memberList  =  $idList->getmember();
                            foreach ($memberList as $member) { 
                                print "                        member" . PHP_EOL;
                                print "                            " . $member;
                            }	
                        } 
                        if ($authorizationDetails->isSetCreationTimestamp()) 
                        {
                            print "                    CreationTimestamp" . PHP_EOL;
                            print "                        " . $authorizationDetails->getCreationTimestamp() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetExpirationTimestamp()) 
                        {
                            print "                    ExpirationTimestamp" . PHP_EOL;
                            print "                        " . $authorizationDetails->getExpirationTimestamp() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetAuthorizationStatus()) { 
                            print "                    AuthorizationStatus" . PHP_EOL;
                            $authorizationStatus = $authorizationDetails->getAuthorizationStatus();
                            if ($authorizationStatus->isSetState()) 
                            {
                                print "                        State" . PHP_EOL;
                                print "                            " . $authorizationStatus->getState() . PHP_EOL;
                            }
                            if ($authorizationStatus->isSetLastUpdateTimestamp()) 
                            {
                                print "                        LastUpdateTimestamp" . PHP_EOL;
                                print "                            " . $authorizationStatus->getLastUpdateTimestamp() . PHP_EOL;
                            }
                            if ($authorizationStatus->isSetReasonCode()) 
                            {
                                print "                        ReasonCode" . PHP_EOL;
                                print "                            " . $authorizationStatus->getReasonCode() . PHP_EOL;
                            }
                            if ($authorizationStatus->isSetReasonDescription()) 
                            {
                                print "                        ReasonDescription" . PHP_EOL;
                                print "                            " . $authorizationStatus->getReasonDescription() . PHP_EOL;
                            }
                        }
                        if ($authorizationDetails->isSetOrderItemCategories()) {
                            print "                     OrderItemCategories" . PHP_EOL;
                            $orderItemCategories = $authorizationDetails->getOrderItemCategories();
                            $orderItemCategoryList  =  $orderItemCategories->getOrderItemCategory();
                            foreach ($orderItemCategoryList as $orderItemCategory) {
                                print "                        OrderItemCategory" . PHP_EOL;
                                print "                            " . $orderItemCategory;
                            }
                        }
                        if ($authorizationDetails->isSetCaptureNow())
                        {
                            print "                    CaptureNow" . PHP_EOL;
                            print "                        " . $authorizationDetails->getCaptureNow() . PHP_EOL;
                        }
                        if ($authorizationDetails->isSetSoftDescriptor())
                        {
                            print "                    SoftDescriptor" . PHP_EOL;
                            print "                        " . $authorizationDetails->getSoftDescriptor() . PHP_EOL;
                        }
                        if($authorizationDetails->isSetAddressVerificationCode())
                        {
                        	print "                    AddressVerificationCode" . PHP_EOL;
                        	print "                        " . $authorizationDetails->getAddressVerificationCode() . PHP_EOL;
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