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
 * Print the GetBillingAgreementDetails response to the console
 *
 * @param OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse $response
 *            response object
 *            
 * @return void
 */
function printGetBillingAgreementDetailsResponse ($response)
{
    echo ("Service Response\n");
    echo ("=============================================================================\n");
    
    echo ("        GetBillingAgreementDetailsResponse\n");
    if ($response->isSetGetBillingAgreementDetailsResult()) {
        echo ("            GetBillingAgreementDetailsResult\n");
        $getBillingAgreementDetailsResult = $response->getGetBillingAgreementDetailsResult();
        if ($getBillingAgreementDetailsResult->isSetBillingAgreementDetails()) {
            echo ("                BillingAgreementDetails\n");
            $billingAgreementDetails = $getBillingAgreementDetailsResult->getBillingAgreementDetails();
            if ($billingAgreementDetails->isSetAmazonBillingAgreementId()) {
                echo ("                    AmazonBillingAgreementId\n");
                echo ("                        " .
                         $billingAgreementDetails->getAmazonBillingAgreementId() . "\n");
            }
            if ($billingAgreementDetails->isSetBillingAgreementLimits()) {
                echo ("                    BillingAgreementLimits\n");
                $billingAgreementLimits = $billingAgreementDetails->getBillingAgreementLimits();
                if ($billingAgreementLimits->isSetAmountLimitPerTimePeriod()) {
                    echo ("                        AmountLimitPerTimePeriod\n");
                    $amountLimitPerTimePeriod = $billingAgreementLimits->getAmountLimitPerTimePeriod();
                    if ($amountLimitPerTimePeriod->isSetAmount()) {
                        echo ("                            Amount\n");
                        echo ("                                " .
                                 $amountLimitPerTimePeriod->getAmount() . "\n");
                    }
                    if ($amountLimitPerTimePeriod->isSetCurrencyCode()) {
                        echo ("                            CurrencyCode\n");
                        echo ("                                " .
                                 $amountLimitPerTimePeriod->getCurrencyCode() . "\n");
                    }
                }
                if ($billingAgreementLimits->isSetTimePeriodStartDate()) {
                    echo ("                        TimePeriodStartDate\n");
                    echo ("                            " .
                             $billingAgreementLimits->getTimePeriodStartDate() . "\n");
                }
                if ($billingAgreementLimits->isSetTimePeriodEndDate()) {
                    echo ("                        TimePeriodEndDate\n");
                    echo ("                            " .
                             $billingAgreementLimits->getTimePeriodEndDate() . "\n");
                }
                if ($billingAgreementLimits->isSetCurrentRemainingBalance()) {
                    echo ("                        CurrentRemainingBalance\n");
                    $currentRemainingBalance = $billingAgreementLimits->getCurrentRemainingBalance();
                    if ($currentRemainingBalance->isSetAmount()) {
                        echo ("                            Amount\n");
                        echo ("                                " .
                                 $currentRemainingBalance->getAmount() . "\n");
                    }
                    if ($currentRemainingBalance->isSetCurrencyCode()) {
                        echo ("                            CurrencyCode\n");
                        echo ("                                " .
                                 $currentRemainingBalance->getCurrencyCode() . "\n");
                    }
                }
            }
            if ($billingAgreementDetails->isSetBuyer()) {
                echo ("                    Buyer\n");
                $buyer = $billingAgreementDetails->getBuyer();
                if ($buyer->isSetName()) {
                    echo ("                        Name\n");
                    echo ("                            " . $buyer->getName() . "\n");
                }
                if ($buyer->isSetEmail()) {
                    echo ("                        Email\n");
                    echo ("                            " . $buyer->getEmail() . "\n");
                }
                if ($buyer->isSetPhone()) {
                    echo ("                        Phone\n");
                    echo ("                            " . $buyer->getPhone() . "\n");
                }
            }
            if ($billingAgreementDetails->isSetSellerNote()) {
                echo ("                    SellerNote\n");
                echo ("                        " . $billingAgreementDetails->getSellerNote() . "\n");
            }
            if ($billingAgreementDetails->isSetPlatformId()) {
                echo ("                    PlatformId\n");
                echo ("                        " . $billingAgreementDetails->getPlatformId() . "\n");
            }
            if ($billingAgreementDetails->isSetDestination()) {
                echo ("                    Destination\n");
                $destination = $billingAgreementDetails->getDestination();
                if ($destination->isSetDestinationType()) {
                    echo ("                        DestinationType\n");
                    echo ("                            " . $destination->getDestinationType() . "\n");
                }
                if ($destination->isSetPhysicalDestination()) {
                    echo ("                        PhysicalDestination\n");
                    $physicalDestination = $destination->getPhysicalDestination();
                    if ($physicalDestination->isSetName()) {
                        echo ("                            Name\n");
                        echo ("                                " . $physicalDestination->getName() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetAddressLine1()) {
                        echo ("                            AddressLine1\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine1() . "\n");
                    }
                    if ($physicalDestination->isSetAddressLine2()) {
                        echo ("                            AddressLine2\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine2() . "\n");
                    }
                    if ($physicalDestination->isSetAddressLine3()) {
                        echo ("                            AddressLine3\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine3() . "\n");
                    }
                    if ($physicalDestination->isSetCity()) {
                        echo ("                            City\n");
                        echo ("                                " . $physicalDestination->getCity() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetCounty()) {
                        echo ("                            County\n");
                        echo ("                                " . $physicalDestination->getCounty() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetDistrict()) {
                        echo ("                            District\n");
                        echo ("                                " .
                                 $physicalDestination->getDistrict() . "\n");
                    }
                    if ($physicalDestination->isSetStateOrRegion()) {
                        echo ("                            StateOrRegion\n");
                        echo ("                                " .
                                 $physicalDestination->getStateOrRegion() . "\n");
                    }
                    if ($physicalDestination->isSetPostalCode()) {
                        echo ("                            PostalCode\n");
                        echo ("                                " .
                                 $physicalDestination->getPostalCode() . "\n");
                    }
                    if ($physicalDestination->isSetCountryCode()) {
                        echo ("                            CountryCode\n");
                        echo ("                                " .
                                 $physicalDestination->getCountryCode() . "\n");
                    }
                    if ($physicalDestination->isSetPhone()) {
                        echo ("                            Phone\n");
                        echo ("                                " . $physicalDestination->getPhone() .
                                 "\n");
                    }
                }
            }
            if ($billingAgreementDetails->isSetBillingAddress()) {
                echo ("                    BillingAddress\n");
                $billingAddress = $billingAgreementDetails->getBillingAddress();
                if ($billingAddress->isSetAddressType()) {
                    echo ("                        AddressType\n");
                    echo ("                            " . $billingAddress->getAddressType() . "\n");
                }
                if ($billingAddress->isSetPhysicalAddress()) {
                    echo ("                        PhysicalAddress\n");
                    $physicalAddress = $billingAddress->getPhysicalAddress();
                    if ($physicalAddress->isSetName()) {
                        echo ("                            Name\n");
                        echo ("                                " . $physicalAddress->getName() .
                                 "\n");
                    }
                    if ($physicalAddress->isSetAddressLine1()) {
                        echo ("                            AddressLine1\n");
                        echo ("                                " .
                                 $physicalAddress->getAddressLine1() . "\n");
                    }
                    if ($physicalAddress->isSetAddressLine2()) {
                        echo ("                            AddressLine2\n");
                        echo ("                                " .
                                 $physicalAddress->getAddressLine2() . "\n");
                    }
                    if ($physicalAddress->isSetAddressLine3()) {
                        echo ("                            AddressLine3\n");
                        echo ("                                " .
                                 $physicalAddress->getAddressLine3() . "\n");
                    }
                    if ($physicalAddress->isSetCity()) {
                        echo ("                            City\n");
                        echo ("                                " . $physicalAddress->getCity() .
                                 "\n");
                    }
                    if ($physicalAddress->isSetCounty()) {
                        echo ("                            County\n");
                        echo ("                                " . $physicalAddress->getCounty() .
                                 "\n");
                    }
                    if ($physicalAddress->isSetDistrict()) {
                        echo ("                            District\n");
                        echo ("                                " .
                                 $physicalAddress->getDistrict() . "\n");
                    }
                    if ($physicalAddress->isSetStateOrRegion()) {
                        echo ("                            StateOrRegion\n");
                        echo ("                                " .
                                 $physicalAddress->getStateOrRegion() . "\n");
                    }
                    if ($physicalAddress->isSetPostalCode()) {
                        echo ("                            PostalCode\n");
                        echo ("                                " .
                                 $physicalAddress->getPostalCode() . "\n");
                    }
                    if ($physicalAddress->isSetCountryCode()) {
                        echo ("                            CountryCode\n");
                        echo ("                                " .
                                 $physicalAddress->getCountryCode() . "\n");
                    }
                    if ($physicalAddress->isSetPhone()) {
                        echo ("                            Phone\n");
                        echo ("                                " . $physicalAddress->getPhone() .
                                 "\n");
                    }
                }
            }
            if ($billingAgreementDetails->isSetReleaseEnvironment()) {
                echo ("                    ReleaseEnvironment\n");
                echo ("                        " . $billingAgreementDetails->getReleaseEnvironment() .
                         "\n");
            }
            if ($billingAgreementDetails->isSetSellerBillingAgreementAttributes()) {
                echo ("                    SellerBillingAgreementAttributes\n");
                $sellerBillingAgreementAttributes = $billingAgreementDetails->getSellerBillingAgreementAttributes();
                if ($sellerBillingAgreementAttributes->isSetSellerBillingAgreementId()) {
                    echo ("                        SellerBillingAgreementId\n");
                    echo ("                            " .
                             $sellerBillingAgreementAttributes->getSellerBillingAgreementId() . "\n");
                }
                if ($sellerBillingAgreementAttributes->isSetStoreName()) {
                    echo ("                        StoreName\n");
                    echo ("                            " .
                             $sellerBillingAgreementAttributes->getStoreName() . "\n");
                }
                if ($sellerBillingAgreementAttributes->isSetCustomInformation()) {
                    echo ("                        CustomInformation\n");
                    echo ("                            " .
                             $sellerBillingAgreementAttributes->getCustomInformation() . "\n");
                }
            }
            if ($billingAgreementDetails->isSetBillingAgreementStatus()) {
                echo ("                    BillingAgreementStatus\n");
                $billingAgreementStatus = $billingAgreementDetails->getBillingAgreementStatus();
                if ($billingAgreementStatus->isSetState()) {
                    echo ("                        State\n");
                    echo ("                            " . $billingAgreementStatus->getState() . "\n");
                }
                if ($billingAgreementStatus->isSetLastUpdatedTimestamp()) {
                    echo ("                        LastUpdatedTimestamp\n");
                    echo ("                            " .
                             $billingAgreementStatus->getLastUpdatedTimestamp() . "\n");
                }
                if ($billingAgreementStatus->isSetReasonCode()) {
                    echo ("                        ReasonCode\n");
                    echo ("                            " . $billingAgreementStatus->getReasonCode() .
                             "\n");
                }
                if ($billingAgreementStatus->isSetReasonDescription()) {
                    echo ("                        ReasonDescription\n");
                    echo ("                            " .
                             $billingAgreementStatus->getReasonDescription() . "\n");
                }
            }
            if ($billingAgreementDetails->isSetConstraints()) {
                echo ("                    Constraints\n");
                $constraints = $billingAgreementDetails->getConstraints();
                $constraintList = $constraints->getConstraint();
                foreach ($constraintList as $constraint) {
                    echo ("                        Constraint\n");
                    if ($constraint->isSetConstraintID()) {
                        echo ("                            ConstraintID\n");
                        echo ("                                " . $constraint->getConstraintID() .
                                 "\n");
                    }
                    if ($constraint->isSetDescription()) {
                        echo ("                            Description\n");
                        echo ("                                " . $constraint->getDescription() .
                                 "\n");
                    }
                }
            }
            if ($billingAgreementDetails->isSetCreationTimestamp()) {
                echo ("                    CreationTimestamp\n");
                echo ("                        " . $billingAgreementDetails->getCreationTimestamp() .
                         "\n");
            }
            if ($billingAgreementDetails->isSetExpirationTimestamp()) {
                echo ("                    ExpirationTimestamp\n");
                echo ("                        " . $billingAgreementDetails->getExpirationTimestamp() .
                         "\n");
            }
            if ($billingAgreementDetails->isSetBillingAgreementConsent()) {
                echo ("                    BillingAgreementConsent\n");
                echo ("                        " .
                         $billingAgreementDetails->getBillingAgreementConsent() . "\n");
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
                                                    
