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
require_once realpath(dirname(__FILE__)) . '/.config.inc.php';

/**
 * Checks that the Authorize Response has all objects initialized, and returns
 * the amazon authorization id
 *
 * @param OffAmazonPaymentsService_Model_AuthorizeResult $response response
 *                                                                 object
 *
 * @throws ErrorException
 *
 * @return AmazonAuthorizationId
 */
function getAmazonAuthorizationIdFromAuthorizeOnBillingAgreementResponse ($response)
{
    if (is_null($response) || ! $response->isSetAuthorizeOnBillingAgreementResult() ||
             ! $response->getAuthorizeOnBillingAgreementResult()->isSetAuthorizationDetails() ||
             ! $response->getAuthorizeOnBillingAgreementResult()
                ->getAuthorizationDetails()
                ->isSetAmazonAuthorizationId()) {
        throw new ErrorException("Error with AuthorizeResponse - missing required fields");
    }
    
    return $response->getAuthorizeOnBillingAgreementResult()
        ->getAuthorizationDetails()
        ->getAmazonAuthorizationId();
}

/**
 * Print the AuthorizeOnBillingAgreement response to the console
 *
 * @param OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse $response
 *            response object
 *            
 * @return void
 */
function printAuthorizeOnBillingAgreementResponse ($response)
{
    echo ("Service Response\n");
    echo ("=============================================================================\n");
    
    echo ("        AuthorizeOnBillingAgreementResponse\n");
    if ($response->isSetAuthorizeOnBillingAgreementResult()) {
        echo ("            AuthorizeOnBillingAgreementResult\n");
        $authorizeOnBillingAgreementResult = $response->getAuthorizeOnBillingAgreementResult();
        if ($authorizeOnBillingAgreementResult->isSetAuthorizationDetails()) {
            echo ("                AuthorizationDetails\n");
            $authorizationDetails = $authorizeOnBillingAgreementResult->getAuthorizationDetails();
            if ($authorizationDetails->isSetAmazonAuthorizationId()) {
                echo ("                    AmazonAuthorizationId\n");
                echo ("                        " . $authorizationDetails->getAmazonAuthorizationId() .
                         "\n");
            }
            if ($authorizationDetails->isSetAuthorizationReferenceId()) {
                echo ("                    AuthorizationReferenceId\n");
                echo ("                        " .
                         $authorizationDetails->getAuthorizationReferenceId() . "\n");
            }
            if ($authorizationDetails->isSetAuthorizationBillingAddress()) {
                echo ("                    AuthorizationBillingAddress\n");
                $authorizationBillingAddress = $authorizationDetails->getAuthorizationBillingAddress();
                if ($authorizationBillingAddress->isSetName()) {
                    echo ("                        Name\n");
                    echo ("                            " . $authorizationBillingAddress->getName() .
                             "\n");
                }
                if ($authorizationBillingAddress->isSetAddressLine1()) {
                    echo ("                        AddressLine1\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getAddressLine1() . "\n");
                }
                if ($authorizationBillingAddress->isSetAddressLine2()) {
                    echo ("                        AddressLine2\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getAddressLine2() . "\n");
                }
                if ($authorizationBillingAddress->isSetAddressLine3()) {
                    echo ("                        AddressLine3\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getAddressLine3() . "\n");
                }
                if ($authorizationBillingAddress->isSetCity()) {
                    echo ("                        City\n");
                    echo ("                            " . $authorizationBillingAddress->getCity() .
                             "\n");
                }
                if ($authorizationBillingAddress->isSetCounty()) {
                    echo ("                        County\n");
                    echo ("                            " . $authorizationBillingAddress->getCounty() .
                             "\n");
                }
                if ($authorizationBillingAddress->isSetDistrict()) {
                    echo ("                        District\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getDistrict() . "\n");
                }
                if ($authorizationBillingAddress->isSetStateOrRegion()) {
                    echo ("                        StateOrRegion\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getStateOrRegion() . "\n");
                }
                if ($authorizationBillingAddress->isSetPostalCode()) {
                    echo ("                        PostalCode\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getPostalCode() . "\n");
                }
                if ($authorizationBillingAddress->isSetCountryCode()) {
                    echo ("                        CountryCode\n");
                    echo ("                            " .
                             $authorizationBillingAddress->getCountryCode() . "\n");
                }
                if ($authorizationBillingAddress->isSetPhone()) {
                    echo ("                        Phone\n");
                    echo ("                            " . $authorizationBillingAddress->getPhone() .
                             "\n");
                }
            }
            if ($authorizationDetails->isSetSellerAuthorizationNote()) {
                echo ("                    SellerAuthorizationNote\n");
                echo ("                        " .
                         $authorizationDetails->getSellerAuthorizationNote() . "\n");
            }
            if ($authorizationDetails->isSetAuthorizationAmount()) {
                echo ("                    AuthorizationAmount\n");
                $authorizationAmount = $authorizationDetails->getAuthorizationAmount();
                if ($authorizationAmount->isSetAmount()) {
                    echo ("                        Amount\n");
                    echo ("                            " . $authorizationAmount->getAmount() . "\n");
                }
                if ($authorizationAmount->isSetCurrencyCode()) {
                    echo ("                        CurrencyCode\n");
                    echo ("                            " . $authorizationAmount->getCurrencyCode() .
                             "\n");
                }
            }
            if ($authorizationDetails->isSetCapturedAmount()) {
                echo ("                    CapturedAmount\n");
                $capturedAmount = $authorizationDetails->getCapturedAmount();
                if ($capturedAmount->isSetAmount()) {
                    echo ("                        Amount\n");
                    echo ("                            " . $capturedAmount->getAmount() . "\n");
                }
                if ($capturedAmount->isSetCurrencyCode()) {
                    echo ("                        CurrencyCode\n");
                    echo ("                            " . $capturedAmount->getCurrencyCode() . "\n");
                }
            }
            if ($authorizationDetails->isSetAuthorizationFee()) {
                echo ("                    AuthorizationFee\n");
                $authorizationFee = $authorizationDetails->getAuthorizationFee();
                if ($authorizationFee->isSetAmount()) {
                    echo ("                        Amount\n");
                    echo ("                            " . $authorizationFee->getAmount() . "\n");
                }
                if ($authorizationFee->isSetCurrencyCode()) {
                    echo ("                        CurrencyCode\n");
                    echo ("                            " . $authorizationFee->getCurrencyCode() .
                             "\n");
                }
            }
            if ($authorizationDetails->isSetIdList()) {
                echo ("                    IdList\n");
                $idList = $authorizationDetails->getIdList();
                $memberList = $idList->getmember();
                foreach ($memberList as $member) {
                    echo ("                        member\n");
                    echo ("                            " . $member . "\n");
                }
            }
            if ($authorizationDetails->isSetCreationTimestamp()) {
                echo ("                    CreationTimestamp\n");
                echo ("                        " . $authorizationDetails->getCreationTimestamp() .
                         "\n");
            }
            if ($authorizationDetails->isSetExpirationTimestamp()) {
                echo ("                    ExpirationTimestamp\n");
                echo ("                        " . $authorizationDetails->getExpirationTimestamp() .
                         "\n");
            }
            if ($authorizationDetails->isSetAuthorizationStatus()) {
                echo ("                    AuthorizationStatus\n");
                $authorizationStatus = $authorizationDetails->getAuthorizationStatus();
                if ($authorizationStatus->isSetState()) {
                    echo ("                        State\n");
                    echo ("                            " . $authorizationStatus->getState() . "\n");
                }
                if ($authorizationStatus->isSetLastUpdateTimestamp()) {
                    echo ("                        LastUpdateTimestamp\n");
                    echo ("                            " .
                             $authorizationStatus->getLastUpdateTimestamp() . "\n");
                }
                if ($authorizationStatus->isSetReasonCode()) {
                    echo ("                        ReasonCode\n");
                    echo ("                            " . $authorizationStatus->getReasonCode() .
                             "\n");
                }
                if ($authorizationStatus->isSetReasonDescription()) {
                    echo ("                        ReasonDescription\n");
                    echo ("                            " .
                             $authorizationStatus->getReasonDescription() . "\n");
                }
            }
            if ($authorizationDetails->isSetOrderItemCategories()) {
                echo ("                    OrderItemCategories\n");
                $orderItemCategories = $authorizationDetails->getOrderItemCategories();
                $orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
                foreach ($orderItemCategoryList as $orderItemCategory) {
                    echo ("                        OrderItemCategory\n");
                    echo ("                            " . $orderItemCategory);
                }
            }
            if ($authorizationDetails->isSetCaptureNow()) {
                echo ("                    CaptureNow\n");
                echo ("                        " . $authorizationDetails->getCaptureNow() . "\n");
            }
            if ($authorizationDetails->isSetSoftDescriptor()) {
                echo ("                    SoftDescriptor\n");
                echo ("                        " . $authorizationDetails->getSoftDescriptor() . "\n");
            }
            if ($authorizationDetails->isSetAddressVerificationCode()) {
            	echo ("                    AddressVerificationCode\n");
            	echo ("                        " . $authorizationDetails->getAddressVerificationCode() . "\n");
            }
        }
        if ($authorizeOnBillingAgreementResult->isSetAmazonOrderReferenceId()) {
            echo ("                AmazonOrderReferenceId\n");
            echo ("                    " .
                     $authorizeOnBillingAgreementResult->getAmazonOrderReferenceId() . "\n");
        }
    }
    if ($response->isSetResponseMetadata()) {
        echo ("            ResponseMetadata\n");
        $responseMetadata = $response->getResponseMetadata();
        if ($responseMetadata->isSetRequestId()) {
            echo ("                RequestId\n");
            echo ("                    " . $responseMetadata->getRequestId() . "\n");
        }
    }
    
    echo ("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
}
?>