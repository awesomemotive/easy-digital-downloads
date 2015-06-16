<?php

/**
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
 */

/*
 * Refund example
 *
 * This demonstrates the scenario where merchant a merchant
 * needs to perform a refund on a previously captured amount 
 * for a closed order reference
 * 
 * This example will show the following service calls:
 *    - GetOrderReferenceDetails
 *    - GetCaptureDetails
 *    - Refund
 *    - GetRefundDetails
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';

 /**
  * RefundExample includes the logic
  * required to refund a capture,
  * which transfers the funds from
  * the merchant back to the buyer
  * 
  */
class RefundExample
{
    private $_sellerId;
    private $_service;
    private $_amazonOrderReferenceId;
    private $_amazonCaptureId;
    private $_refundReferenceId;
     
    /**
     * Create a new instance of the refund example case
     *
     * @param OffAmazonPaymentsService_Client $service                instance of the service
     *                                                                client
     * @param string                          $amazonOrderReferenceId an order reference object in
     *                                                                open or closed state to use in
     *                                                                the example
     * @param string                          $amazonCaptureId        a completed capture that was
     *                                                                performed on the order reference id
     * @param string                          $refundIdSuffix         suffixForTheRefundIdentifier                              
     *
     * @return new RefundExample
     */
    public function __construct($service, $amazonOrderReferenceId, $amazonCaptureId)
    {
        $this->_service = $service;
        $this->_amazonOrderReferenceId = $amazonOrderReferenceId;
        $this->_amazonCaptureId = $amazonCaptureId;
        $this->_refundReferenceId 
            = str_replace("-", "", $amazonCaptureId) . "r" . rand(1, 1000);
         
        $this->_sellerId
            = $this->_service->getMerchantValues()->getMerchantId();
    }
    
    /**
     * Get the order reference details to find to the state
     * of the order reference
     *
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse response
     */
    public function getOrderReferenceDetails()
    {
        $getOrderReferenceDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
        $getOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
        $getOrderReferenceDetailsRequest->setAmazonOrderReferenceId(
            $this->_amazonOrderReferenceId
        );
        
        return $this->_service->getOrderReferenceDetails(
            $getOrderReferenceDetailsRequest
        );
    }
    
    /**
     * Get the capture details to find out the
     * maximum amount that can be refunded
     *
     * @return OffAmazonPaymentsService_Model_GetCaptureDetailsResponse response
     */
    public function getCaptureDetailsRequest()
    {
        $getCaptureDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $getCaptureDetailsRequest->setSellerId($this->_sellerId);
        $getCaptureDetailsRequest->setAmazonCaptureId($this->_amazonCaptureId);
        
        return $this->_service->getCaptureDetails($getCaptureDetailsRequest);
    }
    
    /**
     * Perform the refund to transfer the amount from seller
     * to buyer
     * 
     * @param string $refundAmount   amount to refund to the buyer
     * @param string $refundCurrency currency of the refund
     * 
     * @return void
     */
    public function refundToBuyer($refundAmount, $refundCurrency, $providerCreditReversalInfo=null)
    {
        $refund = new OffAmazonPaymentsService_Model_Price();
        $refund->setCurrencyCode($refundCurrency);
        $refund->setAmount($refundAmount);
        
        $refundRequest = new OffAmazonPaymentsService_Model_RefundRequest();
        $refundRequest->setSellerId($this->_sellerId);
        $refundRequest->setAmazonCaptureId($this->_amazonCaptureId);
        $refundRequest->setRefundReferenceId($this->_refundReferenceId);
        $refundRequest->setRefundAmount($refund);
        
        if($providerCreditReversalInfo != null){
        		$refundRequest->setProviderCreditReversalList(new OffAmazonPaymentsService_Model_ProviderCreditReversalList());
        		$values = array();
                $providerCreditReversal = new OffAmazonPaymentsService_Model_ProviderCreditReversal();
                $providerCreditReversal->setProviderId($providerCreditReversalInfo[0]);
                $creditReversalAmount = new OffAmazonPaymentsService_Model_Price();
                $creditReversalAmount->setAmount($providerCreditReversalInfo[1]);
                $creditReversalAmount->setCurrencyCode($refundCurrency);
                $providerCreditReversal->setCreditReversalAmount($creditReversalAmount);
                array_push($values, $providerCreditReversal);
        		$refundRequest->getProviderCreditReversalList()->setmember($values);
        }   
        return $this->_service->refund($refundRequest);
    }
    
    /**
     * Poll the API for the status of the Refund Request, and continue
     * once the status has been updated
     * WARNING: This is not the way to integrate for production systems,
     * instead merchants should use IOPN to receive a callback once the
     * processing has been completed.
     * Note that Amazon reserves the right to throttle requests that
     * ignore this advice and poll for a response
     *
     * @param string $amazonRefundId refund id to query status of
     *
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsResponse response
     */
    public function waitUntilRefundProcessingIsCompleted($amazonRefundId)
    {        
        $getRefundDetailsResponse = null;
        do {
            sleep(5);
            $getRefundDetailsResponse 
                = $this->getRefundDetails($amazonRefundId);
        } while ((isset($getRefundDetailsResponse)) and
                (strcasecmp(
                    $getRefundDetailsResponse->getGetRefundDetailsResult()
                        ->getRefundDetails()->getRefundStatus()->getState(),
                    "Pending"
                )== 0
                )
          );
        
        if (is_null($getRefundDetailsResponse)) {
            throw new ErrorException(
                "No valid response from getRefundDetails request"
            );
        }
        
        return $getRefundDetailsResponse;
    } 
    
    /**
     * Perform the getRefundDetails request for the order
     *
     * @param string $amazonRefundId authorization transaction
     *                                               to query
     *
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsResponse response
     */
    public function getRefundDetails($amazonRefundId)
    {
        $getRefundDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetRefundDetailsRequest();
        $getRefundDetailsRequest->setSellerId($this->_sellerId);
        $getRefundDetailsRequest->setAmazonRefundId($amazonRefundId);
        return $this->_service->getRefundDetails($getRefundDetailsRequest);
    }
    
    /**
     * Perform the get capture details call for the order
     *
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function getCaptureDetails()
    {
        $captureDetailsRequest
            = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $captureDetailsRequest->setSellerId($this->_sellerId);
        $captureDetailsRequest->setAmazonCaptureId($this->_amazonCaptureId);
    
        return $this->_service->getCaptureDetails($captureDetailsRequest);
    }
    
    public function getProviderCreditReversalDetails($providerCreditReversalId){
    	$providerCreditReversalDetailsRequest = new OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest();
    	$providerCreditReversalDetailsRequest->setSellerId($this->_sellerId);
    	$providerCreditReversalDetailsRequest->setAmazonProviderCreditReversalId($providerCreditReversalId);
    	return $this->_service->getProviderCreditReversalDetails($providerCreditReversalDetailsRequest);
    }
}
?>