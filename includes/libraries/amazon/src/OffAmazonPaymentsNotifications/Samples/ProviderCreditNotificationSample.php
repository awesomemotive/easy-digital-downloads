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
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditNotification.php';

/**
 * Class for handling an capture notification and $this->ipnLogFile->writeLine( the
 * contents to the log file
 */
class OffAmazonPaymentsNotifications_Samples_ProviderCreditNotificationSample extends OffAmazonPaymentsNotifications_Samples_NotificationSample {
	/**
	 * Create a new instance of the ProviderCredit notification sample
	 *
	 * @param OffAmazonPaymentsNotifications_Model_ProviderCreditNotification $notification
	 *        	notification
	 *        	
	 * @return void
	 */
	public function __construct(OffAmazonPaymentsNotifications_Model_ProviderCreditNotification $notification) {
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
		if (! $this->notification->getProviderCreditDetails ()->isSetAmazonProviderCreditId ()) {
			throw new InvalidArgumentException ( "ProviderCreditId is NULL" );
		}
		
		return $this->notification->getProviderCreditDetails ()->getAmazonProviderCreditId () . "_ProviderCreditNotification.txt";
	}
	
	/**
	 * Log the notification contents
	 *
	 * @return void
	 */
	protected function logNotificationContents() {
		$this->ipnLogFile->writeLine ( "ProviderCredit Notification @ " . date ( "Y-m-d H:i:s" ) . " (GMT)" );
		$this->ipnLogFile->writeLine ( "=============================================================================" );
		
		if ($this->notification->isSetProviderCreditDetails ()) {
			$this->ipnLogFile->writeLine ( "                ProviderCreditDetails" );
			$providerCreditDetails = $this->notification->getProviderCreditDetails ();
			if ($providerCreditDetails->isSetAmazonProviderCreditId ()) {
				$this->ipnLogFile->writeLine ( "                    AmazonProviderCreditId" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditDetails->getAmazonProviderCreditId () );
			}
			if ($providerCreditDetails->isSetCreditAmount ()) {
				$this->ipnLogFile->writeLine ( "                    CreditAmount" );
				$creditAmount = $providerCreditDetails->getCreditAmount ();
				if ($creditAmount->isSetAmount ()) {
					$this->ipnLogFile->writeLine ( "                        Amount" );
					$this->ipnLogFile->writeLine ( "                            " . $creditAmount->getAmount () );
				}
				if ($creditAmount->isSetCurrencyCode ()) {
					$this->ipnLogFile->writeLine ( "                        CurrencyCode" );
					$this->ipnLogFile->writeLine ( "                            " . $creditAmount->getCurrencyCode () );
				}
			}
			if ($providerCreditDetails->isSetCreditReversalAmount ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalAmount" );
				$creditReversalAmount = $providerCreditDetails->getCreditReversalAmount ();
				if ($creditReversalAmount->isSetAmount ()) {
					$this->ipnLogFile->writeLine ( "                        Amount" );
					$this->ipnLogFile->writeLine ( "                            " . $creditReversalAmount->getAmount () );
				}
				if ($creditReversalAmount->isSetCurrencyCode ()) {
					$this->ipnLogFile->writeLine ( "                        CurrencyCode" );
					$this->ipnLogFile->writeLine ( "                            " . $creditReversalAmount->getCurrencyCode () );
				}
			}
			if ($providerCreditDetails->isSetCreditReversalIdList ()) {
				$this->ipnLogFile->writeLine ( "                    CreditReversalIdList" );
				$idList = $providerCreditDetails->getCreditReversalIdList ();
				foreach ( $idList as $member ) {
					$this->ipnLogFile->writeLine ( "                        member" );
					$this->ipnLogFile->writeLine ( "                            " . $member );
				}
			}
			if ($providerCreditDetails->isSetCreationTimestamp ()) {
				$this->ipnLogFile->writeLine ( "                    CreationTimestamp" );
				$this->ipnLogFile->writeLine ( "                        " . $providerCreditDetails->getCreationTimestamp () );
			}
			if ($providerCreditDetails->isSetCreditStatus ()) {
				$this->ipnLogFile->writeLine ( "                    CreditStatus" );
				$creditStatus = $providerCreditDetails->getCreditStatus ();
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
		}
	}
}
?>