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
  * Cancellation example
  *
  * This demonstrates a merchant use case where the order needs to be
  * cancelled before the first cancelOrderReference has been performed
  *
  * This is done using the CancelOrderReference call to cancel to order,
  * and can be called prior to the first cancelOrderReference has been performed.
  *
  * If a cancelOrderReference has been performed, then the merchant will need to
  * close the order reference if there is an outstanding total and perform a
  * refund on existing cancelOrderReferenced amounts if required.
  *
  * This example will show the following service calls:
  *    - SetOrderReferenceDetails
  *    - ConfirmOrderReference
  *    - Authorize
  *    - GetAuthorizeDetails
  *    - CancelOrderReference
  *    - GetOrderReferenceDetails
  */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 

/**
 * CancellationExample includes the logic
 * requiere to cancel an order with open
 * authorizations
 *
 */
class CancellationExample
{
   
    private $_sellerId;
    private $_service;
    private $_amazonOrderReferenceId;
    private $_authorizationReferenceId;
    private $_orderTotalAmount;
    
    /**
     * Create a new instance of the simple checkout example
     * case
     *
     * @param OffAmazonPaymentsService_Client $service                instance of the
     *                                                                service client
     * @param string 						  $amazonOrderReferenceId an order reference
     *                                                                object in draft
     *                                                                state to use in the example                                                                
     * @param string                          $orderTotalAmount       amount to authorize 
     *                                                                from the buyer
     *
     * @return new CancellationExample
     */
    public function __construct($service, $amazonOrderReferenceId, $orderTotalAmount)
    {
        $this->_service = $service;
        $this->_amazonOrderReferenceId = $amazonOrderReferenceId;
        
        /*
         * Setup shared identifiers for the series of transaction requests
         */
        $this->_authorizationReferenceId 
            = str_replace("-", "", $this->_amazonOrderReferenceId) . "a01";
        
        $this->_sellerId
            = $this->_service->getMerchantValues()->getMerchantId();
        
        $this->_orderTotalAmount = $orderTotalAmount;
        $this->_currencyCode = $service->getMerchantValues()->getCurrency();
    }
    
    /**
     * Add information to the payment contract so that it can be confirmed
     * in a later step
     * Simulates a merchant adding the order details to the payment contract
     *
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse 
     *     response
     */
    public function setupOrderReference()
    {
        $orderTotal = new OffAmazonPaymentsService_Model_OrderTotal();
        $orderTotal->setCurrencyCode($this->_currencyCode);
        $orderTotal->setAmount($this->_orderTotalAmount);
        
        $setOrderReferenceDetailsRequest 
            = new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $setOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
        $setOrderReferenceDetailsRequest
            ->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $setOrderReferenceDetailsRequest
            ->setOrderReferenceAttributes(
                new OffAmazonPaymentsService_Model_OrderReferenceAttributes()
            );
        $setOrderReferenceDetailsRequest
            ->getOrderReferenceAttributes()->setOrderTotal($orderTotal);
        
        return $this->_service->setOrderReferenceDetails(
            $setOrderReferenceDetailsRequest
        );
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse response
     */
    public function confirmOrderReference()
    {
        $confirmOrderReferenceRequest 
            = new OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest();
        $confirmOrderReferenceRequest
            ->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $confirmOrderReferenceRequest->setSellerId($this->_sellerId);
        
        return $this->_service->confirmOrderReference($confirmOrderReferenceRequest);
    }
    
    /**
     * Perform the authorize call for the order
     * 
     * Cancel order reference can now be called at any point between a
     * ConfirmOrderReference call and a cancelOrderReference call
     * In this example we will call it following a single authorization for
     * half of the order total
     *
     * @return OffAmazonPaymentsService_Model_AuthorizeResponse service response
     */
    public function performAuthorization()
    {
        $authorizationAmountPrice = new OffAmazonPaymentsService_Model_Price();
        $authorizationAmountPrice->setCurrencyCode($this->_currencyCode);
        $authorizationAmountPrice->setAmount($this->_orderTotalAmount);
        
        $authorizeRequest = new OffAmazonPaymentsService_Model_AuthorizeRequest();
        $authorizeRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $authorizeRequest->setSellerId($this->_sellerId);
        $authorizeRequest->setAuthorizationReferenceId(
            $this->_authorizationReferenceId
        );
        $authorizeRequest->setAuthorizationAmount($authorizationAmountPrice);
        
        return $this->_service->authorize($authorizeRequest);
    }
    
    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated
     * WARNING: This is not the way to integrate for production systems,
     * instead merchants should use IPN to receive a callback once the
     * processing has been completed.
     * Note that Amazon reserves the right to throttle requests that
     * ignore this advice and poll for a response
     *
     * @param string $amazonAuthorizationId authorization transaction to query
     *
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse 
     *     response
     */
    public function waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId)
    {         
        $getAuthorizationDetailsResponse = null;
        do {
            sleep(5);
            $getAuthorizationDetailsResponse
                = $this->getAuthorizationDetails($amazonAuthorizationId);
        } while ((isset($getAuthorizationDetailsResponse)) and
                (strcasecmp(
                    $getAuthorizationDetailsResponse
                        ->getGetAuthorizationDetailsResult()
                            ->getAuthorizationDetails()->getAuthorizationStatus()
                                ->getState(),
                    "Pending"
                ) == 0));
         
        if (is_null($getAuthorizationDetailsResponse)) {
            throw new ErrorException(
                "No valid response from getAuthorizationDetails request"
            );
        }
         
        return $getAuthorizationDetailsResponse;
    }
    
    /**
     * Perform the getAuthroizationDetails request for the order
     *
     * @param string $amazonAuthorizationReferenceId authorization transaction
     *                                               to query
     *
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse 
     *     response
     */
    public function getAuthorizationDetails($amazonAuthorizationReferenceId)
    {
        $getAuthorizationDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
        $getAuthorizationDetailsRequest
            ->setSellerId($this->_sellerId);
        $getAuthorizationDetailsRequest
            ->setAmazonAuthorizationId($amazonAuthorizationReferenceId);
    
        return $this->_service->getAuthorizationDetails(
            $getAuthorizationDetailsRequest
        );
    }
    
    /**
     * Cancel the payment contract - this can be performed on any order reference
     * that does not have a completed transaction
     *
     * @return OffAmazonPaymentsService_Model_CancelOrderReferenceResponse response
     */
    public function cancelOrderReference()
    {
        $cancelOrderReferenceRequest 
            = new OffAmazonPaymentsService_Model_CancelOrderReferenceRequest();
        $cancelOrderReferenceRequest->setSellerId($this->_sellerId);
        $cancelOrderReferenceRequest
            ->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        
        return $this->_service->cancelOrderReference($cancelOrderReferenceRequest);
    }
    
    /**
     * Get the status of the order reference request object to show that it
     * now cancelled
     *
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse 
     *     response
     */
    public function getOrderReferenceDetails()
    {
        $getOrderReferenceDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
        $getOrderReferenceDetailsRequest
            ->setSellerId($this->_sellerId);
        $getOrderReferenceDetailsRequest
            ->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
    
        return $this->_service->getOrderReferenceDetails(
            $getOrderReferenceDetailsRequest
        );
    }
    
}
?>