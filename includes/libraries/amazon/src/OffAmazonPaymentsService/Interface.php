<?php

/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

/**
 * Coral service for marketplace
 * payment API operations for external
 * merchants.
 * 
 */

interface  OffAmazonPaymentsService_Interface 
{
    

        
    /**
     * Capture 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CaptureRequest request
     * or OffAmazonPaymentsService_Model_CaptureRequest object itself
     * @see OffAmazonPaymentsService_Model_CaptureRequest
     * @return OffAmazonPaymentsService_Model_CaptureResponse OffAmazonPaymentsService_Model_CaptureResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function capture($request);


        
    /**
     * Refund 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_RefundRequest request
     * or OffAmazonPaymentsService_Model_RefundRequest object itself
     * @see OffAmazonPaymentsService_Model_RefundRequest
     * @return OffAmazonPaymentsService_Model_RefundResponse OffAmazonPaymentsService_Model_RefundResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function refund($request);


        
    /**
     * Close Authorization 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseAuthorizationRequest request
     * or OffAmazonPaymentsService_Model_CloseAuthorizationRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseAuthorizationRequest
     * @return OffAmazonPaymentsService_Model_CloseAuthorizationResponse OffAmazonPaymentsService_Model_CloseAuthorizationResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeAuthorization($request);


        
    /**
     * Get Refund Details 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetRefundDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetRefundDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetRefundDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsResponse OffAmazonPaymentsService_Model_GetRefundDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getRefundDetails($request);


        
    /**
     * Get Capture Details 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetCaptureDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetCaptureDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetCaptureDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetCaptureDetailsResponse OffAmazonPaymentsService_Model_GetCaptureDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getCaptureDetails($request);


        
    /**
     * Close Order Reference 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_CloseOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseOrderReferenceRequest
     * @return OffAmazonPaymentsService_Model_CloseOrderReferenceResponse OffAmazonPaymentsService_Model_CloseOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeOrderReference($request);


        
    /**
     * Confirm Order Reference 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function confirmOrderReference($request);


        
    /**
     * Get Order Reference Details 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getOrderReferenceDetails($request);


        
    /**
     * Authorize 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_AuthorizeRequest request
     * or OffAmazonPaymentsService_Model_AuthorizeRequest object itself
     * @see OffAmazonPaymentsService_Model_AuthorizeRequest
     * @return OffAmazonPaymentsService_Model_AuthorizeResponse OffAmazonPaymentsService_Model_AuthorizeResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function authorize($request);


        
    /**
     * Set Order Reference Details 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest request
     * or OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function setOrderReferenceDetails($request);


        
    /**
     * Get Authorization Details 
  
     * @see http://docs.amazonwebservices.com/${docPath}GetAuthorizationDetails.html      
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getAuthorizationDetails($request);


        
    /**
     * Cancel Order Reference 
  
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CancelOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_CancelOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_CancelOrderReferenceRequest
     * @return OffAmazonPaymentsService_Model_CancelOrderReferenceResponse OffAmazonPaymentsService_Model_CancelOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function cancelOrderReference($request);
    
    
    
    /**
     * Create Order Reference For Id 
     *   
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest request
     * or OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest object itself
     * @see OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function createOrderReferenceForId($request);
    
    
    
    /**
     * Get Billing Agreement Details
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getBillingAgreementDetails($request);
    
    
    
    /**
     * Set Billing Agreement Details
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest request
     * or OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function setBillingAgreementDetails($request);
    
    
    
    /**
     * Confirm Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest
     * @return OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function confirmBillingAgreement($request);
    
    
    
    /**
     * Validate Billing Agreement 
     *   
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function validateBillingAgreement($request);
    
    
    
    /**
     * Authorize On Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function authorizeOnBillingAgreement($request);
    
    
    
    /**
     * Close Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_CloseBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseBillingAgreementRequest
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementResponse OffAmazonPaymentsService_Model_CloseBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeBillingAgreement($request);
    
    /**
     * Get Provider Credit Details
     * A query API for ProviderCredits.  Both Provider and Seller sellerIds are authorized to call this API.
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetProviderCreditDetails.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetProviderCreditDetailsResponse OffAmazonPaymentsService_Model_GetProviderCreditDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getProviderCreditDetails($request);
    
    /**
     * Get Provider Credit Reversal Details
     * Activity to query the funds reversed against a given Provider Credit reversal.
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetProviderCreditReversalDetails.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest
     * @return OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsResponse OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getProviderCreditReversalDetails($request);
    
    /**
     * Reverse Provider Credit
     * Activity to enable the Caller/Provider to reverse the funds credited to Provider.
     *
     * @see http://docs.amazonwebservices.com/${docPath}ReverseProviderCredit.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ReverseProviderCreditRequest request
     * or OffAmazonPaymentsService_Model_ReverseProviderCreditRequest object itself
     * @see OffAmazonPaymentsService_Model_ReverseProviderCreditRequest
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditResponse OffAmazonPaymentsService_Model_ReverseProviderCreditResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function reverseProviderCredit($request);

}
?>