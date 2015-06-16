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
require_once 'OffAmazonPaymentsNotifications/Model/AuthorizationNotification.php';

/**
 * Class for handling an authorization notification and print the 
 * contents to the log file
 * 
 */
class OffAmazonPaymentsNotifications_Samples_AuthorizationNotificationSample 
    extends OffAmazonPaymentsNotifications_Samples_NotificationSample
{
    /**
     * Create a new instance of the Authorization notification sample
     * 
     * @param OffAmazonPaymentsNotifications_Model_AuthorizationNotification $notification notification
     * 
     * @return void
     */
    public function __construct(
        OffAmazonPaymentsNotifications_Model_AuthorizationNotification $notification
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
        if (!$this->notification->getAuthorizationDetails()->isSetAmazonAuthorizationId()) {
            throw new InvalidArgumentException("AuthorizationAuthorizationId is NULL");
        } 
        
        return $this->notification->getAuthorizationDetails()->getAmazonAuthorizationId()
            . "_AuthorizationNotification.txt";
    }
    
    /**
     * Log the authorization detail contents
     * 
     * @return void
     */
    public function logNotificationContents() 
    {
        $this->ipnLogFile->writeLine("Authorization Notification @ ".date("Y-m-d H:i:s") . " (GMT)");
        $this->ipnLogFile->writeLine("=============================================================================");
        if ($this->notification->isSetAuthorizationDetails()) {
            $this->ipnLogFile->writeLine("    AuthorizeDetails");
            $authorizationDetails = $this->notification->getAuthorizationDetails();
            if ($authorizationDetails->isSetAmazonAuthorizationId()) {
                $this->ipnLogFile->writeLine("    AmazonAuthorizationId");
                $this->ipnLogFile->writeLine("          " . $authorizationDetails->getAmazonAuthorizationId());
            }
            if ($authorizationDetails->isSetAuthorizationReferenceId()) {
                $this->ipnLogFile->writeLine("      AuthorizationReferenceId");
                $this->ipnLogFile->writeLine("          " . $authorizationDetails->getAuthorizationReferenceId());
            }
            if ($authorizationDetails->isSetAuthorizationAmount()) {
                $this->ipnLogFile->writeLine("      AuthorizationAmount");
                $authorizationAmount = $authorizationDetails->getAuthorizationAmount();
                if ($authorizationAmount->isSetAmount()) {
                    $this->ipnLogFile->writeLine("          Amount");
                    $this->ipnLogFile->writeLine("              " . $authorizationAmount->getAmount());
                }
                if ($authorizationAmount->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("          CurrencyCode");
                    $this->ipnLogFile->writeLine("              " . $authorizationAmount->getCurrencyCode());
                }
            }
            if ($authorizationDetails->isSetCapturedAmount()) {
                $this->ipnLogFile->writeLine("       CapturedAmount");
                $capturedAmount = $authorizationDetails->getCapturedAmount();
                if ($capturedAmount->isSetAmount()) {
                    $this->ipnLogFile->writeLine("          Amount");
                    $this->ipnLogFile->writeLine("              " . $capturedAmount->getAmount());
                }
                if ($capturedAmount->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("          CurrencyCode");
                    $this->ipnLogFile->writeLine("             " . $capturedAmount->getCurrencyCode());
                }
            }
            if ($authorizationDetails->isSetAuthorizationFee()) {
                $this->ipnLogFile->writeLine("      AuthorizationFee");
                $authorizationFee = $authorizationDetails->getAuthorizationFee();
                if ($authorizationFee->isSetAmount()) {
                    $this->ipnLogFile->writeLine("          Amount");
                    $this->ipnLogFile->writeLine("              " . $authorizationFee->getAmount());
                }
                if ($authorizationFee->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("          CurrencyCode");
                    $this->ipnLogFile->writeLine("              " . $authorizationFee->getCurrencyCode());
                }
            }
            if ($authorizationDetails->isSetIdList()) {
                $this->ipnLogFile->writeLine("      IdList");
                $idList = $authorizationDetails->getIdList();
                $memberList  =  $idList->getId();
                foreach ($memberList as $member) {
                    $this->ipnLogFile->writeLine("          member");
                    $this->ipnLogFile->writeLine("              " . $member);
                }
            }
            if ($authorizationDetails->isSetCreationTimestamp()) {
                $this->ipnLogFile->writeLine("      CreationTimestamp");
                $this->ipnLogFile->writeLine("           " . $authorizationDetails->getCreationTimestamp());
            }
            if ($authorizationDetails->isSetExpirationTimestamp()) {
                $this->ipnLogFile->writeLine("      ExpirationTimestamp");
                $this->ipnLogFile->writeLine("           " . $authorizationDetails->getExpirationTimestamp());
            }
            if ($authorizationDetails->isSetAddressVerificationCode()) {
            	$this->ipnLogFile->writeLine("      AddressVerificationCode");
            	$this->ipnLogFile->writeLine("           " . $authorizationDetails->getAddressVerificationCode());
            }
            if ($authorizationDetails->isSetAuthorizationStatus()) {
                $this->ipnLogFile->writeLine("      AuthorizationStatus");
                $authorizationStatus = $authorizationDetails->getAuthorizationStatus();
                if ($authorizationStatus->isSetState()) {
                    $this->ipnLogFile->writeLine("          State");
                    $this->ipnLogFile->writeLine("              " . $authorizationStatus->getState());
                }
                if ($authorizationStatus->isSetLastUpdateTimestamp()) {
                    $this->ipnLogFile->writeLine("          LastUpdateTimestamp");
                    $this->ipnLogFile->writeLine("              " . $authorizationStatus->getLastUpdateTimestamp());
                }
                if ($authorizationStatus->isSetReasonCode()) {
                    $this->ipnLogFile->writeLine("          ReasonCode");
                    $this->ipnLogFile->writeLine("              " . $authorizationStatus->getReasonCode());
                }
                if ($authorizationStatus->isSetReasonDescription()) {
                    $this->ipnLogFile->writeLine("          ReasonDescription");
                    $this->ipnLogFile->writeLine("              " . $authorizationStatus->getReasonDescription());
                }
            }
            if ($authorizationDetails->isSetOrderItemCategories()) {
                $this->ipnLogFile->writeLine("       OrderItemCategories");
                $orderItemCategories = $authorizationDetails->getOrderItemCategories();
                $orderItemCategoryList  =  $orderItemCategories->getOrderItemCategory();
                foreach ($orderItemCategoryList as $orderItemCategory) {
                    $this->ipnLogFile->writeLine("          OrderItemCategory");
                    $this->ipnLogFile->writeLine("              " . $orderItemCategory);
                }
            }
            if ($authorizationDetails->isSetCaptureNow()) {
                $this->ipnLogFile->writeLine("      CaptureNow");
                $this->ipnLogFile->writeLine("          " . $authorizationDetails->getCaptureNow());
            }
            if ($authorizationDetails->isSetSoftDescriptor()) {
                $this->ipnLogFile->writeLine("      SoftDescriptor");
                $this->ipnLogFile->writeLine("          " . $authorizationDetails->getSoftDescriptor());
            }
        }
    }
}
?>