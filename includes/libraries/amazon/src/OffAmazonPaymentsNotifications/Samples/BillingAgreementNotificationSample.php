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
require_once 'OffAmazonPaymentsNotifications/Samples/NotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreementNotification.php';

/**
 * Class for handling an order reference notification and print the
 * contents to the log file
 *
 */
class OffAmazonPaymentsNotifications_Samples_BillingAgreementNotificationSample extends OffAmazonPaymentsNotifications_Samples_NotificationSample
{

    /**
     * Create a new instance of the billing agreement notification sample
     *
     * @param OffAmazonPaymentsNotifications_Model_BillingAgreementNotification $notification notification
     *
     * @return void
     */
    public function __construct (
            OffAmazonPaymentsNotifications_Model_BillingAgreementNotification $notification)
    {
        parent::__construct($notification);
    }

    /**
     * Extract the name of the log file based on the notification
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getLogFileName ()
    {
        if (! $this->notification->getBillingAgreement()->isSetAmazonBillingAgreementId()) {
            throw new InvalidArgumentException("BillingAgreementId is NULL");
        }
        
        return $this->notification->getBillingAgreement()->getAmazonBillingAgreementId() .
                 "_BillingAgreementNotification.txt";
    }

    /**
     * Log the notification contents
     *
     * @return void
     */
    protected function logNotificationContents ()
    {
        $this->ipnLogFile->writeLine("BillingAgreement @ " . date("Y-m-d H:i:s"));
        $this->ipnLogFile->writeLine(
                "=============================================================================");
        if ($this->notification->isSetBillingAgreement()) {
            $this->ipnLogFile->writeLine("  BillingAgreement");
            $billingAgreement = $this->notification->getBillingAgreement();
            if ($billingAgreement->isSetAmazonBillingAgreementId()) {
                $this->ipnLogFile->writeLine("      AmazonBillingAgreementId");
                $this->ipnLogFile->writeLine(
                        "          " . $billingAgreement->getAmazonBillingAgreementId());
            }
            if ($billingAgreement->isSetSellerBillingAgreementAttributes()) {
                $this->ipnLogFile->writeLine("  SellerBillingAgreementAttributes");
                $sellerBillingAgreementAttributes = $billingAgreement->getSellerBillingAgreementAttributes();
                if ($sellerBillingAgreementAttributes->isSetSellerId()) {
                    $this->ipnLogFile->writeLine("      SellerId");
                    $this->ipnLogFile->writeLine(
                            "          " . $sellerBillingAgreementAttributes->getSellerId());
                }
                if ($sellerBillingAgreementAttributes->isSetSellerBillingAgreementId()) {
                    $this->ipnLogFile->writeLine("      SellerBillingAgreementId");
                    $this->ipnLogFile->writeLine(
                            "          " .
                                     $sellerBillingAgreementAttributes->getSellerBillingAgreementId());
                }
            }
            if ($billingAgreement->isSetBillingAgreementLimits()) {
                $this->ipnLogFile->writeLine("  BillingAgreementLimits");
                $billingAgreementLimits = $billingAgreement->getBillingAgreementLimits();
                if ($billingAgreementLimits->isSetAmountLimitPerTimePeriod()) {
                    $this->ipnLogFile->writeLine("      AmountLimitPerTimePeriod");
                    $amountLimitPerTimePeriod = $billingAgreementLimits->getAmountLimitPerTimePeriod();
                    if ($amountLimitPerTimePeriod->isSetAmount()) {
                        $this->ipnLogFile->writeLine("          Amount");
                        $this->ipnLogFile->writeLine(
                                "              " . $amountLimitPerTimePeriod->getAmount());
                    }
                    if ($amountLimitPerTimePeriod->isSetCurrencyCode()) {
                        $this->ipnLogFile->writeLine("          CurrencyCode");
                        $this->ipnLogFile->writeLine(
                                "              " . $amountLimitPerTimePeriod->getCurrencyCode());
                    }
                }
                if ($billingAgreementLimits->isSetTimePeriodStartDate()) {
                    $this->ipnLogFile->writeLine("      TimePeriodStartDate");
                    $this->ipnLogFile->writeLine(
                            "          " . $billingAgreementLimits->getTimePeriodStartDate());
                }
                if ($billingAgreementLimits->isSetTimePeriodEndDate()) {
                    $this->ipnLogFile->writeLine("      TimePeriodEndDate");
                    $this->ipnLogFile->writeLine(
                            "          " . $billingAgreementLimits->getTimePeriodEndDate());
                }
                if ($billingAgreementLimits->isSetCurrentRemainingBalance()) {
                    $this->ipnLogFile->writeLine("      CurrentRemainingBalance");
                    $currentRemainingBalance = $billingAgreementLimits->getCurrentRemainingBalance();
                    if ($currentRemainingBalance->isSetAmount()) {
                        $this->ipnLogFile->writeLine("          Amount");
                        $this->ipnLogFile->writeLine(
                                "              " . $currentRemainingBalance->getAmount());
                    }
                    if ($currentRemainingBalance->isSetCurrencyCode()) {
                        $this->ipnLogFile->writeLine("          CurrencyCode");
                        $this->ipnLogFile->writeLine(
                                "              " . $currentRemainingBalance->getCurrencyCode());
                    }
                }
            }
            if ($billingAgreement->isSetBillingAgreementStatus()) {
                $this->ipnLogFile->writeLine("  BillingAgreementStatus");
                $billingAgreementStatus = $billingAgreement->getBillingAgreementStatus();
                if ($billingAgreementStatus->isSetState()) {
                    $this->ipnLogFile->writeLine("          State");
                    $this->ipnLogFile->writeLine("          " . $billingAgreementStatus->getState());
                }
                if ($billingAgreementStatus->isSetLastUpdateTimestamp()) {
                    $this->ipnLogFile->writeLine("          LastUpdateTimestamp");
                    $this->ipnLogFile->writeLine(
                            "          " . $billingAgreementStatus->getLastUpdateTimestamp());
                }
                if ($billingAgreementStatus->isSetReasonCode()) {
                    $this->ipnLogFile->writeLine("          ReasonCode");
                    $this->ipnLogFile->writeLine(
                            "          " . $billingAgreementStatus->getReasonCode());
                }
                if ($billingAgreementStatus->isSetReasonDescription()) {
                    $this->ipnLogFile->writeLine("          ReasonDescription");
                    $this->ipnLogFile->writeLine(
                            "          " . $billingAgreementStatus->getReasonDescription());
                }
            }
            if ($billingAgreement->isSetCreationTimestamp()) {
                $this->ipnLogFile->writeLine("  CreationTimestamp");
                $this->ipnLogFile->writeLine("      " . $billingAgreement->getCreationTimestamp());
            }
            if ($billingAgreement->isSetBillingAgreementConsent()) {
                $this->ipnLogFile->writeLine("  BillingAgreementConsent");
                $this->ipnLogFile->writeLine(
                        "      " . $billingAgreement->getBillingAgreementConsent());
            }
        }
    }
}
?>