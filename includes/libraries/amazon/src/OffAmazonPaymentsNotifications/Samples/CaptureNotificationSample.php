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
require_once 'OffAmazonPaymentsNotifications/Model/CaptureNotification.php';

/**
 * Class for handling an capture notification and print the
 * contents to the log file
 *
 */
class OffAmazonPaymentsNotifications_Samples_CaptureNotificationSample
    extends OffAmazonPaymentsNotifications_Samples_NotificationSample
{
    /**
     * Create a new instance of the Capture notification sample
     *
     * @param OffAmazonPaymentsNotifications_Model_CaptureNotification $notification notification
     *
     * @return void
     */
    public function __construct(
        OffAmazonPaymentsNotifications_Model_CaptureNotification $notification
    ) {
        parent::__construct($notification);
    }
    
    /**
     * Extract the name of the log file based on the notification
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getLogFileName() 
    {
        if (! $this->notification->getCaptureDetails()->isSetAmazonCaptureId()) {
            throw new InvalidArgumentException("CaptureId is NULL");
        }

        return $this->notification->getCaptureDetails()->getAmazonCaptureId()."_CaptureNotification.txt";
    }
    
    /**
     * Log the notification contents
     *
     * @return void
     */
    protected function logNotificationContents()
    {
        $this->ipnLogFile->writeLine("Capture Notification @ ".date("Y-m-d H:i:s") . " (GMT)");
        $this->ipnLogFile->writeLine("=============================================================================");
        if ($this->notification->isSetCaptureDetails()) {
            $this->ipnLogFile->writeLine(" CaptureDetails");
            $captureDetails = $this->notification->getCaptureDetails();
            if ($captureDetails->isSetAmazonCaptureId()) {
                $this->ipnLogFile->writeLine(" AmazonCaptureId");
                $this->ipnLogFile->writeLine("          " . $captureDetails->getAmazonCaptureId());
            }
            if ($captureDetails->isSetCaptureReferenceId()) {
                $this->ipnLogFile->writeLine("     CaptureReferenceId");
                $this->ipnLogFile->writeLine("          " . $captureDetails->getCaptureReferenceId());
            }
            if ($captureDetails->isSetCaptureAmount()) {
                $this->ipnLogFile->writeLine("     CaptureAmount");
                $captureAmount = $captureDetails->getCaptureAmount();
                if ($captureAmount->isSetAmount()) {
                    $this->ipnLogFile->writeLine("         Amount");
                    $this->ipnLogFile->writeLine("             " . $captureAmount->getAmount());
                }
                if ($captureAmount->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("      CurrencyCode");
                    $this->ipnLogFile->writeLine("          " . $captureAmount->getCurrencyCode());
                }
            }
            if ($captureDetails->isSetRefundedAmount()) {
                $this->ipnLogFile->writeLine("      RefundedAmount");
                $refundedAmount = $captureDetails->getRefundedAmount();
                if ($refundedAmount->isSetAmount()) {
                    $this->ipnLogFile->writeLine("          Amount");
                    $this->ipnLogFile->writeLine("              " . $refundedAmount->getAmount());
                }
                if ($refundedAmount->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("          CurrencyCode");
                    $this->ipnLogFile->writeLine("              " . $refundedAmount->getCurrencyCode());
                }
            }
            if ($captureDetails->isSetCaptureFee()) {
                $this->ipnLogFile->writeLine("      CaptureFee");
                $captureFee = $captureDetails->getCaptureFee();
                if ($captureFee->isSetAmount()) {
                    $this->ipnLogFile->writeLine("          Amount");
                    $this->ipnLogFile->writeLine("              " . $captureFee->getAmount());
                }
                if ($captureFee->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("         CurrencyCode");
                    $this->ipnLogFile->writeLine("             " . $captureFee->getCurrencyCode());
                }
            }
            if ($captureDetails->isSetIdList()) {
                $this->ipnLogFile->writeLine("  IdList");
                $idList = $captureDetails->getIdList();
                $memberList  =  $idList->getId();
                foreach ($memberList as $member) {
                    $this->ipnLogFile->writeLine("      member");
                    $this->ipnLogFile->writeLine("              " . $member);
                }
            }
            if ($captureDetails->isSetCreationTimestamp()) {
                $this->ipnLogFile->writeLine("     CreationTimestamp");
                $this->ipnLogFile->writeLine("         " . $captureDetails->getCreationTimestamp());
            }
            if ($captureDetails->isSetCaptureStatus()) {
                $this->ipnLogFile->writeLine("    CaptureStatus");
                $captureStatus = $captureDetails->getCaptureStatus();
                if ($captureStatus->isSetState()) {
                    $this->ipnLogFile->writeLine("          State");
                    $this->ipnLogFile->writeLine("              " . $captureStatus->getState());
                }
                if ($captureStatus->isSetLastUpdateTimestamp()) {
                    $this->ipnLogFile->writeLine("         LastUpdateTimestamp");
                    $this->ipnLogFile->writeLine("             " . $captureStatus->getLastUpdateTimestamp());
                }
                if ($captureStatus->isSetReasonCode()) {
                    $this->ipnLogFile->writeLine("          ReasonCode");
                    $this->ipnLogFile->writeLine("              " . $captureStatus->getReasonCode());
                }
                if ($captureStatus->isSetReasonDescription()) {
                    $this->ipnLogFile->writeLine("          ReasonDescription");
                    $this->ipnLogFile->writeLine("              " . $captureStatus->getReasonDescription());
                }
            }
            if($captureDetails->isSetProviderCreditSummaryList()){
            	$this->ipnLogFile->writeLine( "          ProviderCreditSummaryList");
            	$providerCreditSummaryList = $captureDetails->getProviderCreditSummaryList();
            	if($providerCreditSummaryList->isSetProviderCreditSummary()){
            		$values = $providerCreditSummaryList->getProviderCreditSummary();
            		foreach($values as $value){
            			$this->ipnLogFile->writeLine( "              ProviderCreditSummary");
            			if($value->isSetProviderSellerId()){
            				$this->ipnLogFile->writeLine( "                  ProviderSellerId");
            				$this->ipnLogFile->writeLine( "                      ".$value->getProviderSellerId());
            			}
            			if($value->isSetProviderCreditId()){
            				$this->ipnLogFile->writeLine( "                  ProviderCreditId");
            				$this->ipnLogFile->writeLine( "                      ".$value->getProviderCreditId());
            			}
            		}
            	}
            }
            if ($captureDetails->isSetSoftDescriptor()) {
                $this->ipnLogFile->writeLine("      SoftDescriptor");
                $this->ipnLogFile->writeLine("          " . $captureDetails->getSoftDescriptor());
            }
        
        }
    }
}
?>