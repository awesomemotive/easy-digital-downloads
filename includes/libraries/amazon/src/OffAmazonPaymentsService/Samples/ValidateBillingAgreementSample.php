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
 * Print the printValidateBillingAgreement response to the console
 *
 * @param OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse $response
 *            response object
 *            
 * @return void
 */
function printValidateBillingAgreementResponse ($response)
{
    echo ("Service Response\n");
    echo ("=============================================================================\n");
    
    echo ("        ValidateBillingAgreementResponse\n");
    if ($response->isSetValidateBillingAgreementResult()) {
        echo ("            ValidateBillingAgreementResult\n");
        $validateBillingAgreementResult = $response->getValidateBillingAgreementResult();
        if ($validateBillingAgreementResult->isSetValidationResult()) {
            echo ("                ValidationResult\n");
            echo ("                    " . $validateBillingAgreementResult->getValidationResult() .
                     "\n");
        }
        if ($validateBillingAgreementResult->isSetFailureReasonCode()) {
            echo ("                FailureReasonCode\n");
            echo ("                    " . $validateBillingAgreementResult->getFailureReasonCode() .
                     "\n");
        }
        if ($validateBillingAgreementResult->isSetBillingAgreementStatus()) {
            echo ("                BillingAgreementStatus\n");
            $billingAgreementStatus = $validateBillingAgreementResult->getBillingAgreementStatus();
            if ($billingAgreementStatus->isSetState()) {
                echo ("                    State\n");
                echo ("                        " . $billingAgreementStatus->getState() . "\n");
            }
            if ($billingAgreementStatus->isSetLastUpdatedTimestamp()) {
                echo ("                    LastUpdatedTimestamp\n");
                echo ("                        " . $billingAgreementStatus->getLastUpdatedTimestamp() .
                         "\n");
            }
            if ($billingAgreementStatus->isSetReasonCode()) {
                echo ("                    ReasonCode\n");
                echo ("                        " . $billingAgreementStatus->getReasonCode() . "\n");
            }
            if ($billingAgreementStatus->isSetReasonDescription()) {
                echo ("                    ReasonDescription\n");
                echo ("                        " . $billingAgreementStatus->getReasonDescription() .
                         "\n");
            }
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