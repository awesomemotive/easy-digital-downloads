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
require_once realpath ( dirname ( __FILE__ ) . "/.config.inc.php" );
require_once 'OffAmazonPaymentsNotifications/Samples/NotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalNotification.php';

/**
 * Class for handling an capture notification and $this->ipnLogFile->writeLine( the
 * contents to the log file
 */
class OffAmazonPaymentsNotifications_Samples_ProviderCreditReversalNotificationSample extends OffAmazonPaymentsNotifications_Samples_NotificationSample {
	/**
	 * Create a new instance of the ProviderCreditReversal notification sample
	 *
	 * @param OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification $notification
	 *        	notification
	 *        	
	 * @return void
	 */
	public function __construct(OffAmazonPaymentsNotifications_Model_ProviderCreditReversalNotification $notification) {
		parent::__construct ( $notification );
	}
	
	/**
	 * Extract the name of the log file based on the notification
	 *
	 * @throws InvalidArgumentException
	 *
	 * @return string
	 */
	protected function getLogFileName() {
		if (! $this->notification->getProviderCreditReversalDetails ()->isSetAmazonProviderCreditReversalId ()) {
			throw new InvalidArgumentException ( "ProviderCreditReversalId is NULL" );
		}
		
		return $this->notification->getProviderCreditReversalDetails ()->getAmazonProviderCreditReversalId () . "_ProviderCreditReversalNotification.txt";
	}
	
	/**
	 * Log the notification contents
	 *
	 * @return void
	 */
	protected function logNotificationContents() {
		$this->ipnLogFile->writeLine ( "ProviderCreditReversal Notification @ " . date ( "Y-m-d H:i:s" ) . " (GMT)" );
		$this->ipnLogFile->writeLine ( "=============================================================================" );
		
		if ($this->notification->isSetProviderCreditReversalDetails ()) {
			$this->ipnLogFile->writeLine ( "                ProviderCreditReversalDetails" );
			$providerCreditReversalDetails = $this->notification->getProviderCreditReversalDetails ();
			if ($providerCreditReversalDetails->isSetAmazonProviderCreditReversalId ()) {
				$this->ipnLogFile->writeLine ( "                    AmazonProviderCreditReversalId" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditReversalDetails->getAmazonProviderCreditReversalId () );
			}
			if ($providerCreditReversalDetails->isSetCreditReversalReferenceId ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalReferenceId" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditReversalDetails->getCreditReversalReferenceId () );
			}
			if ($providerCreditReversalDetails->isSetCreditReversalAmount ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalAmount" );
				$creditReversalAmount = $providerCreditReversalDetails->getCreditReversalAmount ();
				if ($creditReversalAmount->isSetAmount ()) {
					$this->ipnLogFile->writeLine ( "                        Amount" );
					$this->ipnLogFile->writeLine ( "                            " . $creditReversalAmount->getAmount () );
				}
				if ($creditReversalAmount->isSetCurrencyCode ()) {
					$this->ipnLogFile->writeLine ( "                        CurrencyCode" );
					$this->ipnLogFile->writeLine ( "                            " . $creditReversalAmount->getCurrencyCode () );
				}
			}
			if ($providerCreditReversalDetails->isSetCreationTimestamp ()) {
				$this->ipnLogFile->writeLine ( "                    CreationTimestamp" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditReversalDetails->getCreationTimestamp () );
			}
			if ($providerCreditReversalDetails->isSetCreditReversalStatus ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalStatus" );
				$creditStatus = $providerCreditReversalDetails->getCreditReversalStatus ();
				if ($creditStatus->isSetState ()) {
					$this->ipnLogFile->writeLine ( "                        State" );
					$this->ipnLogFile->writeLine ( "                            " . $creditStatus->getState () );
				}
				if ($creditStatus->isSetLastUpdateTimestamp ()) {
					$this->ipnLogFile->writeLine ( "                        LastUpdateTimestamp" );
					$this->ipnLogFile->writeLine ( "                            " . $creditStatus->getLastUpdateTimestamp () );
				}
				if ($creditStatus->isSetReasonCode ()) {
					$this->ipnLogFile->writeLine ( "                        ReasonCode" );
					$this->ipnLogFile->writeLine ( "                            " . $creditStatus->getReasonCode () );
				}
				if ($creditStatus->isSetReasonDescription ()) {
					$this->ipnLogFile->writeLine ( "                        ReasonDescription" );
					$this->ipnLogFile->writeLine ( "                            " . $creditStatus->getReasonDescription () );
				}
			}
			if ($providerCreditReversalDetails->isSetCreditReversalNote ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalNote" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditReversalDetails->getCreditReversalNote () );
			}
		}
	}
}
?>