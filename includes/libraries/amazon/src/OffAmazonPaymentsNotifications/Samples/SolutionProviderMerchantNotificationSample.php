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
require_once 'OffAmazonPaymentsNotifications/Model/SolutionProviderMerchantNotification.php';

/**
 * Class for handling an capture notification and $this->ipnLogFile->writeLine( the
 * contents to the log file
 */
class OffAmazonPaymentsNotifications_Samples_SolutionProviderMerchantNotificationSample extends OffAmazonPaymentsNotifications_Samples_NotificationSample {
	/**
	 * Create a new instance of the SolutionProviderMerchant notification sample
	 *
	 * @param OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification $notification
	 *        	notification
	 *        	
	 * @return void
	 */
	public function __construct(OffAmazonPaymentsNotifications_Model_SolutionProviderMerchantNotification $notification) {
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
		if (! $this->notification->getMerchantRegistrationDetails()->isSetSellerId()) {
			throw new InvalidArgumentException ( "SellerId is NULL" );
		}
		
		return $this->notification->getMerchantRegistrationDetails()->getSellerId(). "_SolutionProviderMerchantNotification.txt";
	}
	

	protected function logNotificationContents() {
		$this->ipnLogFile->writeLine ( "SolutionProviderMerchant Notification @ " . date ( "Y-m-d H:i:s" ) . " (GMT)" );
		$this->ipnLogFile->writeLine ( "=============================================================================" );
		
		if ($this->notification->isSetMerchantRegistrationDetails ()) {
			$this->ipnLogFile->writeLine ( "                MerchantRegistrationDetails" );
			$merchantRegistrationDetails = $this->notification->getMerchantRegistrationDetails ();
			if ($merchantRegistrationDetails->isSetSellerId()) {
				$this->ipnLogFile->writeLine ( "                	SellerId" );
				$this->ipnLogFile->writeLine ( "                		". $merchantRegistrationDetails->getSellerId() );
			}
			if ($merchantRegistrationDetails->isSetType()) {
				$this->ipnLogFile->writeLine ( "                	Type" );
				$this->ipnLogFile->writeLine ( "                		". $merchantRegistrationDetails->getType() );
			}
			if ($merchantRegistrationDetails->isSetOptions()) {
				$this->ipnLogFile->writeLine ( "                	Options" );
				$options = $merchantRegistrationDetails->getOptions();
				if($options->isSetSolutionProviderOption()){
					$memberList = $options->getSolutionProviderOption();
					foreach ( $memberList as $solutionProviderOption ) {
						$this->ipnLogFile->writeLine ( "                        SolutionProviderOption" );
						if($solutionProviderOption->isSetname()){
							$this->ipnLogFile->writeLine ( "                        	name" );
							$this->ipnLogFile->writeLine ( "                        		".$solutionProviderOption->getname());
						}
						if($solutionProviderOption->isSetvalue()){
							$this->ipnLogFile->writeLine ( "                        	value" );
							$this->ipnLogFile->writeLine ( "                        		".$solutionProviderOption->getvalue());
						}
					}	
				}
			}
		}
	}
}
?>