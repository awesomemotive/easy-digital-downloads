<?php
/*
 * Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved. Licensed under the Apache License, Version 2.0 (the "License"); You may not use this file except in compliance with the License. You may obtain a copy of the License at: http://aws.amazon.com/apache2.0 This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */

/*
 * Straight checkout example command line example
 *
 * This class drives the simple checkout example from a command line interface
 * See SimpleCheckoutExample.php for more information
 */

require_once realpath ( dirname ( __FILE__ ) ) . '/.config.inc.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/GetOrderReferenceDetailsSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/SetOrderReferenceDetailsSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/ConfirmOrderReferenceSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/AuthorizeSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/GetAuthorizationDetailsSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/GetCaptureDetailsSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/CaptureSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/CloseOrderReferenceSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/GetProviderCreditDetailsSample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/CLIExample.php';
require_once realpath ( dirname ( __FILE__ ) ) . '/SimpleCheckoutExample.php';

/*
 * ProviderCheckoutExampleCLI class captures input from stdin and prints to stdout, and drives the simple checkout example
 */
class ProviderCheckoutExampleCLI extends CLIExample {
	/**
	 * Create a new instance of the cli example and
	 * validate command line arguments
	 *
	 * @param array $argv
	 *        	arguments to the appplication passed from the command line
	 */
	public function __construct($argv) {
		if (count ( $argv ) < 1) {
			print "Missing mandatory argument: " . "please provide an amazonOrderReferenceId";
			exit ( 0 );
		}
		
		/*
		 * Instantiate Implementation of OffAmazonPaymentsService client Merchant ids and access keys are defined in the constants are defined in the OffAmazonPaymentsService.config.inc.php file in the parent directory of this server
		 */
		$this->exampleClass = new SimpleCheckoutExample ( new OffAmazonPaymentsService_Client (), $argv [1] );
	}
	
	/**
	 * Run all the steps for the sample in sequence
	 */
	public function runSample() {
		$orderTotal = $this->_calculateOrderTotalBasedOnBuyerDestinationAddress ();
		$this->_addOrderTotalAndSellerInformationToOrder($orderTotal);
		$this->_confirmOrderReference();
		$amazonAuthorizationId = $this->_authorizeBasedOnOptionSelected($orderTotal);
		$amazonCaptureId = $this->_captureOrderAmount($orderTotal, $amazonAuthorizationId);
		$providerCreditSummaryList = $this->_waitForProviderCreditSummaryList($amazonCaptureId);
		$this->_getProviderCreditDetails($providerCreditSummaryList);
		$this->_closeOrderReference();
		print "Provider Checkout Completed" . PHP_EOL;
	}
	
    /**
     * Retreive the current information about the order as indicated by the buyer
     * and calculate the total amount to charge, based on address destination state 
     * and country
     * 
     * @return string total amount for the order that the merchant will 
     *         charge the buyer
     */
    private function _calculateOrderTotalBasedOnBuyerDestinationAddress() 
    {
        $response = $this->callStepAndCheckForException('getOrderReferenceDetails');
        printGetOrderReferenceDetailsResponse($response);
        
        $orderTotalPreTaxAndShipping 
             = $this->_getPreTaxAndShippingOrderAmountFromStdIn();
        $shippingType = $this->_getShippingTypeFromStdIn();
        return $this->exampleClass->calculateOrderTotalBasedOnBuyerDetails(
            $response->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails(),
            $orderTotalPreTaxAndShipping, 
            $shippingType
        );
    }
    
    /**
     * Add order information by making the call to setOrderReferenceDetails with
     * the total order amount, as well as notes describing the order information
     * 
     * @param float $orderTotal total value of the order, incl shipping and tax
     * 
     */
    private function _addOrderTotalAndSellerInformationToOrder($orderTotal)
    {
        $response
            = $this->callStepAndCheckForException(
                'addOrderTotalAndSellerInformationToOrder', 
                array($orderTotal)
            );
        printSetOrderReferenceDetailsResponse($response);
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     *
     */
    private function _confirmOrderReference()
    {
        $response = $this->callStepAndCheckForException('confirmOrderReference');
        printConfirmOrderReferenceResponse($response);
    }
    
    /**
     * Perform Authorize using Regular Authorization or Fast Authorization
     * 
     * @param float $authorizationAmount amount to authorize from the buyer
     * 
     * @return string amazonAuthorizationId amazon generated authorization id reference
     */
    private function _authorizeBasedOnOptionSelected($authorizationAmount)
    {
        $authOption = $this->_getAuthOptionFromStdIn();
        $amazonAuthorizationId = $this->_authorizeOrderAmount($authorizationAmount, $authOption);
        if($authOption != 2){
            //When Asynchronous Authorization is used, wait until the Authorization is completed.
            //After the Authorization is out of the Pending state, we can continue.
            //This is not needed for Fast Authorization since it has a synchronous response.
            $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId);
        }
        return $amazonAuthorizationId;
    }
    
    /**
     * Perform the authorize call for the order
     * 
     * @param float $authorizationAmount amount to authorize from the buyer
     * 
     * @return string amazonAuthorizationId amazon generated authorization id reference
     */
    private function _authorizeOrderAmount($authorizationAmount, $authOption = 1)
    {
        $response = $this->callStepAndCheckForException(
            'authorizeOrderAmount', 
            array($authorizationAmount, $authOption)
        );
        printAuthorizeResponse($response);
        return $response->getAuthorizeResult()->getAuthorizationDetails()->getAmazonAuthorizationId();
    }
    
    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated
     * Throw an error if the status is not equal to Open 
     * 
     * @param string $amazonAuthorizationId authorization transaction to query
     *
     */
    private function _waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId)
    {
        $response 
            = $this->callStepAndCheckForException(
                'waitUntilAuthorizationProcessingIsCompleted', 
                array($amazonAuthorizationId)
            );
        printGetAuthorizationDetailsResponse($response);
        validateThatAuthorizationIsOpen($response);
    }
    
    /**
     * Perform the capture call for the order
     * Throw an exception if the capture is not processed, as this is the
     * expected result
     *
     * @param float  $captureAmount         amount to capture from the buyer
     * @param string $amazonAuthorizationId auth id to perform the capture on
     *
     * @return no response
     */
    private function _captureOrderAmount($captureAmount, $amazonAuthorizationId)
    {	$providerCreditInfo = $this->_getProviderIdAndCreditAmountFromStdIn();
        $response = $this->callStepAndCheckForException(
            'captureOrderAmount', 
            array($captureAmount, $amazonAuthorizationId, $providerCreditInfo)
        );
        
        validateThatCaptureIsCompleted($response->getCaptureResult());
        printCaptureResponse($response);
        return $response->getCaptureResult()->getCaptureDetails()->getAmazonCaptureId();
    }

	
	private function _waitForProviderCreditSummaryList($amazonCaptureId){
		
		$maxSleepTime = 60;
		$getCaptureDetailsResponse = null;
		$providerCreditSummaryList = null;
        $providerCreditSummaryMember = null;
		while (is_null($providerCreditSummaryList) && $maxSleepTime>0 ) {
			sleep(5);
			$maxSleepTime -= 5;
			$getCaptureDetailsResponse = $this->callStepAndCheckForException('getCaptureDetails',array($amazonCaptureId));
            $providerCreditSummaryList = $getCaptureDetailsResponse->getGetCaptureDetailsResult()->getCaptureDetails()->getProviderCreditSummaryList();              
        }	
		validateThatCaptureIsCompleted($getCaptureDetailsResponse->getGetCaptureDetailsResult());
		printGetCaptureDetailsResponse($getCaptureDetailsResponse);	
		if (is_null($providerCreditSummaryList)) {
            throw new ErrorException("No providerCreditSummaryList found in getCaptureDetails response");
        }
		return $providerCreditSummaryList;
	}
	
	/**
	 */
	private function _getProviderCreditDetails($providerCreditSummaryList) {
		if (! is_null ( $providerCreditSummaryList ) && $providerCreditSummaryList->isSetmember ()) {
			$values = $providerCreditSummaryList->getmember ();
			foreach ( $values as $value ) {
				if ($value->isSetProviderCreditId ()) {
					$providerCreditId = $value->getProviderCreditId ();
					$response = $this->callStepAndCheckForException ( 'getProviderCreditDetails', array (
							$providerCreditId 
					) );
					printProviderCreditDetailsResponse ( $response );
				}
			}
		}
	}
	
    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     * 
     * @return no value
     */
    private function _closeOrderReference()
    {
        $response = $this->callStepAndCheckForException('closeOrderReference');  
    }
        
    /**
     * Capture the pre tax order amount from standard input,
     * making sure that it is a numeric string
     * 
     * @return string total amount of the order before tax and shipping charges
     */
    private function _getPreTaxAndShippingOrderAmountFromStdIn() 
    {
        print PHP_EOL . "-------------------------------------------" . PHP_EOL;
        print "Enter the pre tax amount to charge for the order as a number (to 2 decimal places): ";
        do {
            $orderAmount = trim(fgets(STDIN));
        } while (!is_numeric($orderAmount));

        return $orderAmount;
    }

    /**
     * Capture the shipping type for this order, which determines
     * the shipping charge
     * 
     * @return number selected shipping type index
     */
    private function _getShippingTypeFromStdIn() 
    {
        print PHP_EOL . "Select a shipping option for the order:" . PHP_EOL;
        print "\t 1 - Overnight shipping" . PHP_EOL;
        print "\t 2 - 2-day shipping" . PHP_EOL;
        print "\t 3 - 5-day shipping" . PHP_EOL;
        print ">>";

        do {
            $shippingType = trim(fgets(STDIN));
        } while (!is_numeric($shippingType) and 
                ($shippingType < 1 or $shippingType > 3));

        return $shippingType - 1;
    }


	/**
	 * Get providerIdList for this order
	 */
	private function _getProviderIdAndCreditAmountFromStdIn() {
		print PHP_EOL . "Enter provider id >>";
		do {
            $providerId = trim(fgets(STDIN));
        } while (!isset($providerId) || $providerId === '');
		
		print PHP_EOL . "Enter provider credit amount >>";
		do {
		$providerCreditAmount = trim(fgets(STDIN));
		} while (!is_numeric($providerCreditAmount));
		
		return array($providerId, $providerCreditAmount);
	}
	
	/**
	 * Use Regular Authorization or Fast Authorization?
	 *
	 * @return number , 1 for regular authorization and 2 for fast authorization.
	 */
	private function _getAuthOptionFromStdIn() {
		print PHP_EOL . "-------------------------------------------" . PHP_EOL;
		print "\t 1 - Use Regular Authorization (Asynchronous Response) [Default]" . PHP_EOL;
		print "\t 2 - Use Fast Authorization (Synchronous Response)" . PHP_EOL;
		print ">>";
		do {
			$authOption = trim ( fgets ( STDIN ) );
		} while (!is_numeric($authOption) and
				($authOption < 1 or $authOption > 2));
		
		return $authOption;
	}
	
};
$providerCheckoutExample = new ProviderCheckoutExampleCLI($argv);
$providerCheckoutExample->runSample();

?>