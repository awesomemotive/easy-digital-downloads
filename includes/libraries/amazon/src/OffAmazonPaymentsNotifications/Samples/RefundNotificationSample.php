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
require_once 'OffAmazonPaymentsNotifications/Model/RefundNotification.php';

/**
 * Class for handling a refund notification and print the
 * contents to the log file
 *
 */
class OffAmazonPaymentsNotifications_Samples_RefundNotificationSample
    extends OffAmazonPaymentsNotifications_Samples_NotificationSample
{
    /**
     * Create a new instance of the Refund notification sample
     *
     * @param OffAmazonPaymentsNotifications_Model_RefundNotification $notification notification
     *
     * @return void
     */
    public function __construct(
        OffAmazonPaymentsNotifications_Model_RefundNotification $notification
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
    protected function getLogFileName() {
        if (!$this->notification->getRefundDetails()->isSetAmazonRefundId()) {
            throw new InvalidArgumentException("RefundId is NULL");
        }
        
        return $this->notification->getRefundDetails()->getAmazonRefundId()."_RefundNotification.txt";
    }
    
    /**
     * Log the notification contents
     *
     * @return void
     */
    protected function logNotificationContents()
    {
        $this->ipnLogFile->writeLine("Refund Notification @ ".date("Y-m-d H:i:s") . " (GMT)");
        $this->ipnLogFile->writeLine("=============================================================================");
        if ($this->notification->isSetRefundDetails()) {
            $this->ipnLogFile->writeLine("  RefundDetails");
            $refundDetails = $this->notification->getRefundDetails();
            if ($refundDetails->isSetAmazonRefundId()) {
                $this->ipnLogFile->writeLine("      AmazonRefundId");
                $this->ipnLogFile->writeLine("          " . $refundDetails->getAmazonRefundId());
            }
            if ($refundDetails->isSetRefundReferenceId()) {
                $this->ipnLogFile->writeLine("  RefundReferenceId");
                $this->ipnLogFile->writeLine("      " . $refundDetails->getRefundReferenceId());
            }
            if ($refundDetails->isSetRefundType()) {
                $this->ipnLogFile->writeLine("  RefundType");
                $this->ipnLogFile->writeLine("      " . $refundDetails->getRefundType());
            }
            if ($refundDetails->isSetRefundAmount()) {
                $this->ipnLogFile->writeLine("  RefundAmount");
                $refundAmount = $refundDetails->getRefundAmount();
                if ($refundAmount->isSetAmount()) {
                    $this->ipnLogFile->writeLine("      Amount");
                    $this->ipnLogFile->writeLine("          " . $refundAmount->getAmount());
                }
                if ($refundAmount->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("      CurrencyCode");
                    $this->ipnLogFile->writeLine("          " . $refundAmount->getCurrencyCode());
                }
            }
            if ($refundDetails->isSetFeeRefunded()) {
                $this->ipnLogFile->writeLine("  FeeRefunded");
                $feeRefunded = $refundDetails->getFeeRefunded();
                if ($feeRefunded->isSetAmount()) {
                    $this->ipnLogFile->writeLine("      Amount");
                    $this->ipnLogFile->writeLine("          " . $feeRefunded->getAmount());
                }
                if ($feeRefunded->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("      CurrencyCode");
                    $this->ipnLogFile->writeLine("          " . $feeRefunded->getCurrencyCode());
                }
            }
            if ($refundDetails->isSetCreationTimestamp()) {
                $this->ipnLogFile->writeLine("  CreationTimestamp");
                $this->ipnLogFile->writeLine("      " . $refundDetails->getCreationTimestamp());
            }
            if ($refundDetails->isSetRefundStatus()) {
                $this->ipnLogFile->writeLine("  RefundStatus");
                $refundStatus = $refundDetails->getRefundStatus();
                if ($refundStatus->isSetState()) {
                    $this->ipnLogFile->writeLine("      State");
                    $this->ipnLogFile->writeLine("          " . $refundStatus->getState());
                } 
                if ($refundStatus->isSetLastUpdateTimestamp()) {
                    $this->ipnLogFile->writeLine("      LastUpdateTimestamp");
                    $this->ipnLogFile->writeLine("          " . $refundStatus->getLastUpdateTimestamp());
                }
                if ($refundStatus->isSetReasonCode()) {
                    $this->ipnLogFile->writeLine("      ReasonCode");
                    $this->ipnLogFile->writeLine("          " . $refundStatus->getReasonCode());
                }
                if ($refundStatus->isSetReasonDescription()) {
                    $this->ipnLogFile->writeLine("      ReasonDescription");
                    $this->ipnLogFile->writeLine("          " . $refundStatus->getReasonDescription());
                }
            }
            if($refundDetails->isSetProviderCreditReversalSummaryList()){
            	 $this->ipnLogFile->writeLine( "      ProviderCreditReversalSummaryList" );
            	$providerCreditReversalSummaryList = $refundDetails->getProviderCreditReversalSummaryList();
            	if($providerCreditReversalSummaryList->isSetProviderCreditReversalSummary()){
            		$values = $providerCreditReversalSummaryList->getProviderCreditReversalSummary();
            		foreach($values as $value){
            			 $this->ipnLogFile->writeLine( "          ProviderCreditReversalSummary" );
            			if($value->isSetProviderSellerId()){
            				 $this->ipnLogFile->writeLine( "              ProviderSellerId" );
            				 $this->ipnLogFile->writeLine( "                  ".$value->getProviderSellerId() );
            			}
            			if($value->isSetProviderCreditReversalId()){
            				 $this->ipnLogFile->writeLine( "              ProviderCreditReversalId" );
            				 $this->ipnLogFile->writeLine( "                  ".$value->getProviderCreditReversalId() );
            			}
            		}
            	}
            }
            if ($refundDetails->isSetSoftDescriptor()) {
                $this->ipnLogFile->writeLine("  SoftDescriptor");
                $this->ipnLogFile->writeLine("      " . $refundDetails->getSoftDescriptor());
            }
        }
    }
}
?>