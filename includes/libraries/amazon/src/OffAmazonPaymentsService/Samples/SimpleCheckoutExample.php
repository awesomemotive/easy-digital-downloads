<?php
/*
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
 * Straight checkout example
 *
 * This demonstrates a merchant use case where the item is in stock, and
 * the order reference creation is immediately followed by the order
 * confirmation and capture of funds for the merchant
 *
 * This use case makes the assumption that the merchant is using the
 * address capture widget to capture the destination address for the order, and
 * uses the address information to calculate a tax and shipping rate
 *
 * This example will show the following service calls:
 *    - GetOrderReferenceDetails
 *    - SetOrderReferenceDetails
 *    - ConfirmOrderReference
 *    - Authorize
 *    - GetAuthorizeDetails
 *    - Capture
 *    - GetCaptureDetails
 *    - CloseOrderReference
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';
require_once realpath(dirname(__FILE__)) . '/Utils/ShippingAndTaxCostHelper.php';


/**
 * SimpleCheckoutExample includes the logic
 * required to capture an order and perform
 * all actions to capture the order amount from
 * the buyer
 *
 */
class SimpleCheckoutExample
{
    private $_sellerId;
    private $_service;
    private $_amazonOrderReferenceId;
    private $_authorizationReferenceId;
    private $_captureReferenceId;
    private $_shippingAndTaxCostHelper;

    /**
     * Create a new instance of the simple checkout example
     * case
     * 
     * @param OffAmazonPaymentsService_Client $service                 instance of the service 
     *                                                                 client
     * @param string                           $amazonOrderReferenceId an order reference object in
     *                                                                 draft state to use in
     *                                                                 the example
     *                                                                 
     * @return new SimpleCheckoutExample
     */
    public function __construct($service, $amazonOrderReferenceId) 
    {
               
        $this->_service = $service;
        $this->_amazonOrderReferenceId = $amazonOrderReferenceId;
        $this->_sellerId = $this->_service->getMerchantValues()->getMerchantId();
        $this->_shippingAndTaxCostHelper = new ShippingAndTaxCostHelper();
         
        /*
         * Setup shared identifiers for the series of transaction requests
         */
        $this->_authorizationReferenceId 
            = str_replace("-", "", $this->_amazonOrderReferenceId) . "A01";
        $this->_captureReferenceId = $this->_authorizationReferenceId . "C01";

    }

    /**
     * Use the order reference object to query the order information, including 
     * the current physical delivery address as selected by the buyer
     * 
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse service response
     */
    public function getOrderReferenceDetails()
    {
        $getOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
        $getOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
        $getOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);

        return $this->_service->getOrderReferenceDetails($getOrderReferenceDetailsRequest);
    }

    /**
     * Calculate the total amount to charge the buyer for this order,
     * based on the buyer destination address
     * 
     * Note that until the order is confirmed, the name & address fields will
     * not be returned to the client
     *
     * @param OffAmazonPaymentsService_Model_OrderReferenceDetails $orderReferenceDetails        response
     * @param string                                               $orderAmountPreTaxAndShipping order amount
     * @param int                                                  $shippingType                 shipping type
     * 
     * @return float total amount for the order, with shipping and tax included
     */
    public function calculateOrderTotalBasedOnBuyerDetails($orderReferenceDetails, $orderAmountPreTaxAndShipping, $shippingType) 
    {
        return $this->_shippingAndTaxCostHelper->calculateTotalAmount($orderReferenceDetails,
                $orderAmountPreTaxAndShipping, $shippingType);
    }
     
    /**
     * Add order information by making the call to setOrderReferenceDetails with
     * the total order amount, as well as notes describing the order information
     * 
     * @param float  $orderTotal   total value of the order, incl shipping and tax
     * 
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse service response
     */
    public function addOrderTotalAndSellerInformationToOrder($orderTotal)
    {
        /*
         * Setup request parameters and uncomment invoke to try out
         * sample for Set Order Reference Details Action
         */
        $setOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $setOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
        $setOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $setOrderReferenceDetailsRequest->setOrderReferenceAttributes(new OffAmazonPaymentsService_Model_OrderReferenceAttributes());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setOrderTotal(new OffAmazonPaymentsService_Model_OrderTotal());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setAmount($orderTotal);
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setSellerNote("Red widgets");
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setSellerOrderAttributes(new OffAmazonPaymentsService_Model_SellerOrderAttributes());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setSellerOrderId("AD32333432212");
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setStoreName("Domestic site");
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setCustomInformation("Add blue stripe");
        
        return $this->_service->setOrderReferenceDetails($setOrderReferenceDetailsRequest);
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     * 
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse service response
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
     * @param float  $authorizationAmount amount to authorize from the buyer
     * 
     * @return OffAmazonPaymentsService_Model_AuthorizeResponse service response
     */
    public function authorizeOrderAmount($authorizationAmount, $authOption = 1)
    {
        $authorizeRequest = new OffAmazonPaymentsService_Model_AuthorizeRequest();
        $authorizeRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $authorizeRequest->setSellerId($this->_sellerId);
        $authorizeRequest->setAuthorizationReferenceId($this->_authorizationReferenceId);
        $authorizeRequest->setAuthorizationAmount(new OffAmazonPaymentsService_Model_Price());
        $authorizeRequest->getAuthorizationAmount()->setAmount($authorizationAmount);
        $authorizeRequest->getAuthorizationAmount()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        if($authOption == 2){
            //To use fast authorization, set the Transaction Timeout (optional parameteter) to 0.
            $authorizeRequest->setTransactionTimeout(0);
        }
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
     * @param string $amazonAuthorizationReferenceId authorization transaction to query
     * 
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse service response
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
                        ->getGetAuthorizationDetailsResult()->getAuthorizationDetails()
                            ->getAuthorizationStatus()->getState(),
                    "Pending"
                ) == 0));
        
        if (is_null($getAuthorizationDetailsResponse)) {
            throw new ErrorException("No valid response from getAuthorizationDetails request");
        }
        
        return $getAuthorizationDetailsResponse;
    }
    
    /**
     * Perform the getAuthroizationDetails request for the order
     * 
     * @param string $amazonAuthorizationReferenceId authorization transaction  
     *                                               to query
     * 
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse service response
     */
    public function getAuthorizationDetails($amazonAuthorizationReferenceId)
    {
        $getAuthorizationDetailsRequest = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
        $getAuthorizationDetailsRequest->setSellerId($this->_sellerId);
        $getAuthorizationDetailsRequest->setAmazonAuthorizationId($amazonAuthorizationReferenceId);
        
        return $this->_service->getAuthorizationDetails($getAuthorizationDetailsRequest);
    }
    
    /**
     * Perform the capture call for the order
     *
     * @param float  $captureAmount         amount to capture from the buyer
     * @param string $amazonAuthorizationId auth id to perform the capture on
     *
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function captureOrderAmount($captureAmount, $amazonAuthorizationId, $providerCreditInfo = null)
    {
        $captureRequest = new OffAmazonPaymentsService_Model_CaptureRequest();
        $captureRequest->setSellerId($this->_sellerId);
        $captureRequest->setAmazonAuthorizationId($amazonAuthorizationId);
        $captureRequest->setCaptureReferenceId($this->_captureReferenceId);
        $captureRequest->setCaptureAmount(new OffAmazonPaymentsService_Model_Price());
        $captureRequest->getCaptureAmount()->setAmount($captureAmount);
        $captureRequest->getCaptureAmount()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        
        if($providerCreditInfo != null){
        	$captureRequest->setProviderCreditList(new OffAmazonPaymentsService_Model_ProviderCreditList());
        	$values = array();
            $providerCredit = new OffAmazonPaymentsService_Model_ProviderCredit();
            $providerCredit->setProviderId($providerCreditInfo[0]);
            $creditAmount = new OffAmazonPaymentsService_Model_Price();
            $creditAmount->setAmount($providerCreditInfo[1]);
            $creditAmount->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
            $providerCredit->setCreditAmount($creditAmount);
            array_push($values, $providerCredit);
        	$captureRequest->getProviderCreditList()->setmember($values);
        }
         
        return $this->_service->capture($captureRequest);
    }

    /**
     * Perform the get capture details call for the order
     *
     * @param string $amazonCaptureId capture it to get details for
     * 
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function getCaptureDetails($amazonCaptureId)
    {
        $captureDetailsRequest 
            = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $captureDetailsRequest->setSellerId($this->_sellerId);
        $captureDetailsRequest->setAmazonCaptureId($amazonCaptureId);
        
        return $this->_service->getCaptureDetails($captureDetailsRequest);
    }
    
    public function getProviderCreditDetails($providerCreditId){
    	$providerCreditDetailsRequest = new OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest();
    	$providerCreditDetailsRequest->setSellerId($this->_sellerId);
    	$providerCreditDetailsRequest->setAmazonProviderCreditId($providerCreditId);
    	return $this->_service->getProviderCreditDetails($providerCreditDetailsRequest);
    }
    
    
    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     * 
     * @return OffAmazonPaymentsService_Model_CloseOrderReferenceResponse service response
     */
    public function closeOrderReference()
    {
        $closeOrderReferenceRequest = new OffAmazonPaymentsService_Model_CloseOrderReferenceRequest();
        $closeOrderReferenceRequest->setSellerId($this->_sellerId);
        $closeOrderReferenceRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $closeOrderReferenceRequest->setClosureReason("Order complete");

        return $this->_service->closeOrderReference($closeOrderReferenceRequest);
    }
};
?>
