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


/**
 *  @see OffAmazonPaymentsService_Interface
 */
require_once 'OffAmazonPaymentsService/Interface.php';
require_once 'OffAmazonPaymentsService/MerchantValuesBuilder.php';
require_once 'OffAmazonPaymentsService/Model/CaptureRequest.php';
require_once 'OffAmazonPaymentsService/Model/CaptureResponse.php';
require_once 'OffAmazonPaymentsService/Model/RefundRequest.php';
require_once 'OffAmazonPaymentsService/Model/RefundResponse.php';
require_once 'OffAmazonPaymentsService/Model/CloseAuthorizationRequest.php';
require_once 'OffAmazonPaymentsService/Model/CloseAuthorizationResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetRefundDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetRefundDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetCaptureDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetCaptureDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/CloseOrderReferenceRequest.php';
require_once 'OffAmazonPaymentsService/Model/CloseOrderReferenceResponse.php';
require_once 'OffAmazonPaymentsService/Model/ConfirmOrderReferenceRequest.php';
require_once 'OffAmazonPaymentsService/Model/ConfirmOrderReferenceResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetOrderReferenceDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetOrderReferenceDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/AuthorizeRequest.php';
require_once 'OffAmazonPaymentsService/Model/AuthorizeResponse.php';
require_once 'OffAmazonPaymentsService/Model/SetOrderReferenceDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/SetOrderReferenceDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetAuthorizationDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetAuthorizationDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/CancelOrderReferenceRequest.php';
require_once 'OffAmazonPaymentsService/Model/CancelOrderReferenceResponse.php';
require_once 'OffAmazonPaymentsService/Model/AuthorizeOnBillingAgreementRequest.php';
require_once 'OffAmazonPaymentsService/Model/AuthorizeOnBillingAgreementResponse.php';
require_once 'OffAmazonPaymentsService/Model/CloseBillingAgreementRequest.php';
require_once 'OffAmazonPaymentsService/Model/CloseBillingAgreementResponse.php';
require_once 'OffAmazonPaymentsService/Model/ConfirmBillingAgreementRequest.php';
require_once 'OffAmazonPaymentsService/Model/ConfirmBillingAgreementResponse.php';
require_once 'OffAmazonPaymentsService/Model/CreateOrderReferenceForIdRequest.php';
require_once 'OffAmazonPaymentsService/Model/CreateOrderReferenceForIdResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetBillingAgreementDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetBillingAgreementDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/SetBillingAgreementDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/SetBillingAgreementDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/ValidateBillingAgreementRequest.php';
require_once 'OffAmazonPaymentsService/Model/ValidateBillingAgreementResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetProviderCreditReversalDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetProviderCreditReversalDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/GetProviderCreditDetailsRequest.php';
require_once 'OffAmazonPaymentsService/Model/GetProviderCreditDetailsResponse.php';
require_once 'OffAmazonPaymentsService/Model/ReverseProviderCreditRequest.php';
require_once 'OffAmazonPaymentsService/Model/ReverseProviderCreditResponse.php';
require_once 'OffAmazonPaymentsService/Model/ErrorResponse.php';
require_once 'OffAmazonPaymentsService/Model/ResponseHeaderMetadata.php';
require_once 'OffAmazonPaymentsService/Exception.php';
require_once 'OffAmazonPayments/HttpRequest/Impl/HttpRequestFactoryCurlImpl.php';
require_once 'OffAmazonPayments/HttpRequest/HttpException.php';

/**
 * Implementation of the OffAmazonPaymentsService interface
 * that implements client calls to the web service API
 *
 * OffAmazonPaymentsService_Client is an implementation of OffAmazonPaymentsService
 *
 */
class OffAmazonPaymentsService_Client implements OffAmazonPaymentsService_Interface
{
    const SERVICE_VERSION = '2013-01-01';

    private $_merchantValues = null;

    private $_httpRequestFactory = null;


    /** @var array */
    private  $_config = array (
        'SignatureVersion' => 2,
        'SignatureMethod' => 'HmacSHA256',
        'MaxErrorRetry' => 3
    );

    /**
     * Construct new Client
     *
     * @param string $awsAccessKeyId AWS Access Key ID
     * @param string $awsSecretAccessKey AWS Secret Access Key
     * @param array $config configuration options.
     * Valid configuration options are:
     * <ul>
     * <li>merchantId</li>
     * <li>accessKey</li>
     * <li>secretKey</li>
     * <li>applicationName</li>
     * <li>applicationVersion</li>
     * <li>region</li>
     * <li>environment</li>
     * <li>serviceURL</li>
     * <li>widgetURL</li>
     * <li>caBundleFile</li>
     * <li>clientId</li>
     * </ul>
     */
    public function __construct($config = null)
    {
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->_merchantValues = OffAmazonPaymentsService_MerchantValuesBuilder::create($config)->build();
        $this->_httpRequestFactory = new HttpRequestFactoryCurlImpl($this->_merchantValues);
    }
    
    public function getMerchantValues()
    {
        return $this->_merchantValues;
    }

    // Public API ------------------------------------------------------------//


        
    /**
     * Capture 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CaptureRequest request
     * or OffAmazonPaymentsService_Model_CaptureRequest object itself
     * @see OffAmazonPaymentsService_Model_Capture
     * @return OffAmazonPaymentsService_Model_CaptureResponse OffAmazonPaymentsService_Model_CaptureResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function capture($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_CaptureRequest) {
            $request = new OffAmazonPaymentsService_Model_CaptureRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertCapture($request));
        $response = OffAmazonPaymentsService_Model_CaptureResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Refund 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_RefundRequest request
     * or OffAmazonPaymentsService_Model_RefundRequest object itself
     * @see OffAmazonPaymentsService_Model_Refund
     * @return OffAmazonPaymentsService_Model_RefundResponse OffAmazonPaymentsService_Model_RefundResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function refund($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_RefundRequest) {
            
            $request = new OffAmazonPaymentsService_Model_RefundRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertRefund($request));
        $response = OffAmazonPaymentsService_Model_RefundResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Close Authorization 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseAuthorizationRequest request
     * or OffAmazonPaymentsService_Model_CloseAuthorizationRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseAuthorization
     * @return OffAmazonPaymentsService_Model_CloseAuthorizationResponse OffAmazonPaymentsService_Model_CloseAuthorizationResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeAuthorization($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_CloseAuthorizationRequest) {
            $request = new OffAmazonPaymentsService_Model_CloseAuthorizationRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertCloseAuthorization($request));
        $response = OffAmazonPaymentsService_Model_CloseAuthorizationResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Get Refund Details 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetRefundDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetRefundDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetRefundDetails
     * @return OffAmazonPaymentsService_Model_GetRefundDetailsResponse OffAmazonPaymentsService_Model_GetRefundDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getRefundDetails($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_GetRefundDetailsRequest) {
            $request = new OffAmazonPaymentsService_Model_GetRefundDetailsRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertGetRefundDetails($request));
        $response = OffAmazonPaymentsService_Model_GetRefundDetailsResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Get Capture Details 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetCaptureDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetCaptureDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetCaptureDetails
     * @return OffAmazonPaymentsService_Model_GetCaptureDetailsResponse OffAmazonPaymentsService_Model_GetCaptureDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getCaptureDetails($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_GetCaptureDetailsRequest) {
            $request = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertGetCaptureDetails($request));
        $response = OffAmazonPaymentsService_Model_GetCaptureDetailsResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Close Order Reference 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_CloseOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseOrderReference
     * @return OffAmazonPaymentsService_Model_CloseOrderReferenceResponse OffAmazonPaymentsService_Model_CloseOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeOrderReference($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_CloseOrderReferenceRequest) {            
            $request = new OffAmazonPaymentsService_Model_CloseOrderReferenceRequest($request);
        };
        $httpResponse = $this->_invoke($this->_convertCloseOrderReference($request));
        $response = OffAmazonPaymentsService_Model_CloseOrderReferenceResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Confirm Order Reference 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_ConfirmOrderReference
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function confirmOrderReference($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest) {
            $request = new OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertConfirmOrderReference($request));
        $response = OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Get Order Reference Details 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetOrderReferenceDetails
     * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getOrderReferenceDetails($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest) {
            $request = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertGetOrderReferenceDetails($request));
        $response = OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Authorize 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_AuthorizeRequest request
     * or OffAmazonPaymentsService_Model_AuthorizeRequest object itself
     * @see OffAmazonPaymentsService_Model_Authorize
     * @return OffAmazonPaymentsService_Model_AuthorizeResponse OffAmazonPaymentsService_Model_AuthorizeResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function authorize($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_AuthorizeRequest) {
            $request = new OffAmazonPaymentsService_Model_AuthorizeRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertAuthorize($request));
        $response = OffAmazonPaymentsService_Model_AuthorizeResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Set Order Reference Details 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest request
     * or OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_SetOrderReferenceDetails
     * @return OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function setOrderReferenceDetails($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest) {
            $request = new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertSetOrderReferenceDetails($request));
        $response = OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Get Authorization Details 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetAuthorizationDetails
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getAuthorizationDetails($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest) {
            $request = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertGetAuthorizationDetails($request));
        $response = OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }


        
    /**
     * Cancel Order Reference 
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CancelOrderReferenceRequest request
     * or OffAmazonPaymentsService_Model_CancelOrderReferenceRequest object itself
     * @see OffAmazonPaymentsService_Model_CancelOrderReference
     * @return OffAmazonPaymentsService_Model_CancelOrderReferenceResponse OffAmazonPaymentsService_Model_CancelOrderReferenceResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function cancelOrderReference($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_CancelOrderReferenceRequest) {
            $request = new OffAmazonPaymentsService_Model_CancelOrderReferenceRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertCancelOrderReference($request));
        $response = OffAmazonPaymentsService_Model_CancelOrderReferenceResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }
    
    
    
    /**
     * Create Order Reference For Id
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest request
     * or OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest object itself
     * @see OffAmazonPaymentsService_Model_CreateOrderReferenceForId
     * @return OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function createOrderReferenceForId($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest) {
            $request = new OffAmazonPaymentsService_Model_CreateOrderReferenceForIdRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertCreateOrderReferenceForId($request));
        $response = OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }
    
    
    
    /**
     * Get Billing Agreement Details
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetBillingAgreementDetails
     * @return OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getBillingAgreementDetails($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest) {
    		$request = new OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertGetBillingAgreementDetails($request));
    	$response = OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    
    
    /**
     * Set Billing Agreement Details
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest request
     * or OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_SetBillingAgreementDetails
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function setBillingAgreementDetails($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest) {
    		$request = new OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertSetBillingAgreementDetails($request));
    	$response = OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    
    
    /**
     * Confirm Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_ConfirmBillingAgreement
     * @return OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function confirmBillingAgreement($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest) {
    		$request = new OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertConfirmBillingAgreement($request));
    	$response = OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    
    
    /**
     * Validate Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_ValidateBillingAgreement
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function validateBillingAgreement($request)
    {
        if (!$request instanceof OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest) {
            $request = new OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest($request);
        }
        $httpResponse = $this->_invoke($this->_convertValidateBillingAgreement($request));
        $response = OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse::fromXML($httpResponse['ResponseBody']);
        $response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
        return $response;
    }
    
    
    
    /**
     * Authorize On Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreement
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function authorizeOnBillingAgreement($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest) {
    		$request = new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertAuthorizeOnBillingAgreement($request));
    	$response = OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    
    
    /**
     * Close Billing Agreement
     *
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_CloseBillingAgreementRequest request
     * or OffAmazonPaymentsService_Model_CloseBillingAgreementRequest object itself
     * @see OffAmazonPaymentsService_Model_CloseBillingAgreement
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementResponse OffAmazonPaymentsService_Model_CloseBillingAgreementResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function closeBillingAgreement($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_CloseBillingAgreementRequest) {
    		$request = new OffAmazonPaymentsService_Model_CloseBillingAgreementRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertCloseBillingAgreement($request));
    	$response = OffAmazonPaymentsService_Model_CloseBillingAgreementResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    /**
     * Get Provider Credit Details
     * A query API for ProviderCredits.  Both Provider and Seller sellerIds are authorized to call this API.
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetProviderCreditDetails.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetProviderCreditDetails
     * @return OffAmazonPaymentsService_Model_GetProviderCreditDetailsResponse OffAmazonPaymentsService_Model_GetProviderCreditDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getProviderCreditDetails($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest) {
    		$request = new OffAmazonPaymentsService_Model_GetProviderCreditDetailsRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertGetProviderCreditDetails($request));
    	$response = OffAmazonPaymentsService_Model_GetProviderCreditDetailsResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    /**
     * Get Provider Credit Reversal Details
     * Activity to query the funds reversed against a given Provider Credit reversal.
     *
     * @see http://docs.amazonwebservices.com/${docPath}GetProviderCreditReversalDetails.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest request
     * or OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest object itself
     * @see OffAmazonPaymentsService_Model_GetProviderCreditReversalDetails
     * @return OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsResponse OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function getProviderCreditReversalDetails($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest) {
    		$request = new OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertGetProviderCreditReversalDetails($request));
    	$response = OffAmazonPaymentsService_Model_GetProviderCreditReversalDetailsResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    
    /**
     * Reverse Provider Credit
     * Activity to enable the Caller/Provider to reverse the funds credited to Provider.
     *
     * @see http://docs.amazonwebservices.com/${docPath}ReverseProviderCredit.html
     * @param mixed $request array of parameters for OffAmazonPaymentsService_Model_ReverseProviderCreditRequest request
     * or OffAmazonPaymentsService_Model_ReverseProviderCreditRequest object itself
     * @see OffAmazonPaymentsService_Model_ReverseProviderCredit
     * @return OffAmazonPaymentsService_Model_ReverseProviderCreditResponse OffAmazonPaymentsService_Model_ReverseProviderCreditResponse
     *
     * @throws OffAmazonPaymentsService_Exception
     */
    public function reverseProviderCredit($request)
    {
    	if (!$request instanceof OffAmazonPaymentsService_Model_ReverseProviderCreditRequest) {
    		$request = new OffAmazonPaymentsService_Model_ReverseProviderCreditRequest($request);
    	}
    	$httpResponse = $this->_invoke($this->_convertReverseProviderCredit($request));
    	$response = OffAmazonPaymentsService_Model_ReverseProviderCreditResponse::fromXML($httpResponse['ResponseBody']);
    	$response->setResponseHeaderMetadata($httpResponse['ResponseHeaderMetadata']);
    	return $response;
    }
    

    // Private API ------------------------------------------------------------//

    /**
     * Invoke request and return response
     */
    private function _invoke(array $parameters)
    {
        $actionName = $parameters["Action"];
        $response = array();
        $responseBody = null;
        $statusCode = 200;

        /* Submit the request and read response body */
        try {

            /* Add required request parameters */
            $parameters = $this->_addRequiredParameters($parameters);

            $shouldRetry = true;
            $retries = 0;
            do {
                try {
                        $response = $this->_httpPost($parameters);
                        if ($response['Status'] == 200) {
                            $shouldRetry = false;
                        } else {
                            if ($response['Status'] == 500 || $response['Status'] == 503) {
                            	
                            	$errorResponse = OffAmazonPaymentsService_Model_ErrorResponse::fromXML($response['ResponseBody']);
                            	
                            	$errors = $errorResponse->getError();
                            	$shouldRetry = ($errors[0]->getCode() === 'RequestThrottled') ? false : true;
                            	
                            	if ($shouldRetry) {
                            		$this->_pauseOnRetry(++$retries, $response['Status']);
                            	} else {
                            		throw $this->_reportAnyErrors($response['ResponseBody'], $response['Status'], $response['ResponseHeaderMetadata']);
                            	}
                            } else {
                                throw $this->_reportAnyErrors($response['ResponseBody'], $response['Status'], $response['ResponseHeaderMetadata']);
                            }
                       }
                /* Rethrow on deserializer error */
                } catch (Exception $e) {
                    if ($e instanceof OffAmazonPaymentsService_Exception) {
                        throw $e;
                    } else {
                        throw new OffAmazonPaymentsService_Exception(array('Exception' => $e, 'Message' => $e->getMessage()));
                    }
                }

            } while ($shouldRetry);

        } catch (OffAmazonPaymentsService_Exception $se) {
            throw $se;
        } catch (Exception $t) {
            throw new OffAmazonPaymentsService_Exception(array('Exception' => $t, 'Message' => $t->getMessage()));
        }

        return array ('ResponseBody' => $response['ResponseBody'], 'ResponseHeaderMetadata' => $response['ResponseHeaderMetadata']);
    }

    /**
     * Look for additional error strings in the response and return formatted exception
     */
    private function _reportAnyErrors($responseBody, $status, $responseHeaderMetadata, Exception $e =  null)
    {
        $ex = null;
        if (!is_null($responseBody) && strpos($responseBody, '<') === 0) {
            if (preg_match('@<RequestId>(.*)</RequestId>.*<Error>.*<Code>(.*)</Code>.*<Message>(.*)</Message>.*</Error>.*(<Error>)?@mis',
                $responseBody, $errorMatcherOne)) {

                $requestId = $errorMatcherOne[1];
                $code = $errorMatcherOne[2];
                $message = $errorMatcherOne[3];

                $ex = new OffAmazonPaymentsService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                           'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody,
                                                           'ResponseHeaderMetadata' => $responseHeaderMetadata));

            } elseif (preg_match('@<Error>.*<Type>(.*)</Type>.*<Code>(.*)</Code>.*<Message>(.*)</Message>.*</Error>.*(<Error>)?.*<RequestId>(.*)</RequestId>@mis',
                $responseBody, $errorMatcherThree)) {

                $type = $errorMatcherThree[1];
                $code = $errorMatcherThree[2];
                $message = $errorMatcherThree[3];
                $requestId = $errorMatcherThree[5];
                $ex = new OffAmazonPaymentsService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                              'ErrorType' => $type, 'RequestId' => $requestId, 'XML' => $responseBody,
                                                              'ResponseHeaderMetadata' => $responseHeaderMetadata));
                
            } elseif (preg_match('@<Error>.*<Code>(.*)</Code>.*<Message>(.*)</Message>.*</Error>.*(<Error>)?.*<RequestID>(.*)</RequestID>@mis',
                $responseBody, $errorMatcherTwo)) {

                $code = $errorMatcherTwo[1];
                $message = $errorMatcherTwo[2];
                $requestId = $errorMatcherTwo[4];
                $ex = new OffAmazonPaymentsService_Exception(array ('Message' => $message, 'StatusCode' => $status, 'ErrorCode' => $code,
                                                              'ErrorType' => 'Unknown', 'RequestId' => $requestId, 'XML' => $responseBody,
                                                              'ResponseHeaderMetadata' => $responseHeaderMetadata));

            } else {
                $ex = new OffAmazonPaymentsService_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status, 'ResponseHeaderMetadata' => $responseHeaderMetadata));
            }
        } else {
            $ex = new OffAmazonPaymentsService_Exception(array('Message' => 'Internal Error', 'StatusCode' => $status, 'ResponseHeaderMetadata' => $responseHeaderMetadata));
        }
        return $ex;
    }



    /**
     * Perform HTTP post with exponential retries on error 500 and 503
     *
     */
    private function _httpPost(array $parameters)
    {

        $query = $this->_getParametersAsString($parameters);
        $response = '';
        try { 
            $response = $this->_httpRequestFactory->createPostRequest($this->_merchantValues->getServiceUrl(),$query)->execute();
        } catch (OffAmazonPayments_HttpException $ex) {
            $error_msg = "Unable to post request, underlying exception of " . $ex->getMessage();
            throw new OffAmazonPaymentsService_Exception(array('Message' => $error_msg));
        }

        //First split by 2 'CRLF'
        $responseComponents = preg_split("/(?:\r?\n){2}/", $response);
        $body = null;
        for ($count = 0; 
                $count < count($responseComponents) && $body == null; 
                $count++) {
            
            $headers = $responseComponents[$count];
            $responseStatus = $this->_extractHttpStatusCode($headers);
            
            if($responseStatus != null && 
                    $this->_httpHeadersHaveContent($headers)){
                
                $responseHeaderMetadata = $this->_extractResponseHeaderMetadata($headers);
                //The body will be the next item in the responseComponents array
                $body = $responseComponents[++$count];
            }
        }
        
        //If the body is null here then we were unable to parse the response and will throw an exception
        if($body == null){
            $exProps["Message"] = "Failed to parse valid HTTP response (" . $response . ")";
            $exProps["ErrorType"] = "HTTP";
            throw new OffAmazonPaymentsService_Exception($exProps);
        }

        return array(
                'Status' => $responseStatus, 
                'ResponseBody' => $body, 
                'ResponseHeaderMetadata' => $responseHeaderMetadata);
    }

    /**
     * parse the status line of a header string for the proper format and
     * return the status code
     *
     * Example: HTTP/1.1 200 OK
     * ...
     * returns String statusCode or null if the status line can't be parsed
     */
    private function _extractHttpStatusCode($headers){
        $statusCode = null; 
        if (1 === preg_match("/(\\S+) +(\\d+) +([^\n\r]+)(?:\r?\n|\r)/", $headers, $matches)) {
            //The matches array [entireMatchString, protocol, statusCode, the rest]
            $statusCode = $matches[2]; 
        }
        return $statusCode;
    }
    
    /**
     * Tries to determine some valid headers indicating this response
     * has content.  In this case
     * return true if there is a valid "Content-Length" or "Transfer-Encoding" header
     */
    private function _httpHeadersHaveContent($headers){
        return (1 === preg_match("/[cC]ontent-[lL]ength: +(?:\\d+)(?:\\r?\\n|\\r|$)/", $headers) ||
                1 === preg_match("/Transfer-Encoding: +(?!identity[\r\n;= ])(?:[^\r\n]+)(?:\r?\n|\r|$)/i", $headers));
    }
    
    /**
    *  extract a ResponseHeaderMetadata object from the raw headers
    */
    private function _extractResponseHeaderMetadata($rawHeaders){
        $inputHeaders = preg_split("/\r\n|\n|\r/", $rawHeaders);
        $headers = array();
        $headers['x-mws-request-id'] = null;
        $headers['x-mws-response-context'] = null;
        $headers['x-mws-timestamp'] = null;

        foreach ($inputHeaders as $currentHeader) {
            $keyValue = explode (': ', $currentHeader);
            if (isset($keyValue[1])) {
                list ($key, $value) = $keyValue;
                if (isset($headers[$key]) && $headers[$key]!==null) {
                    $headers[$key] = $headers[$key] . "," . $value;
                } else {
                    $headers[$key] = $value;
                }
            }
        }

        return new OffAmazonPaymentsService_Model_ResponseHeaderMetadata(
          $headers['x-mws-request-id'],
          $headers['x-mws-response-context'],
          $headers['x-mws-timestamp']);
    }

    /**
     * Exponential sleep on failed request
     * @param retries current retry
     * @throws OffAmazonPaymentsService_Exception if maximum number of retries has been reached
     */
    private function _pauseOnRetry($retries, $status)
    {
        if ($retries <= $this->_config['MaxErrorRetry']) {
            $delay = (int) (pow(4, $retries) * 100000) ;
            usleep($delay);
        } else {
            throw new OffAmazonPaymentsService_Exception (array ('Message' => "Maximum number of retry attempts reached :  $retries", 'StatusCode' => $status));
        }
    }

    /**
     * Add authentication related and version parameters
     */
    private function _addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId'] = $this->_merchantValues->getAccessKey();
        $parameters['Timestamp'] = $this->_getFormattedTimestamp();
        $parameters['Version'] = self::SERVICE_VERSION;
        $parameters['SignatureVersion'] = $this->_config['SignatureVersion'];
        if ($parameters['SignatureVersion'] > 1) {
            $parameters['SignatureMethod'] = $this->_config['SignatureMethod'];
        }
        $parameters['Signature'] = $this->_signParameters($parameters, $this->_merchantValues->getSecretKey());

        return $parameters;
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }
        return implode('&', $queryParameters);
    }


    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * If Signature Version is 0, it signs concatenated Action and Timestamp
     *
     * If Signature Version is 1, it performs the following:
     *
     * Sorts all  parameters (including SignatureVersion and excluding Signature,
     * the value of which is being created), ignoring case.
     *
     * Iterate over the sorted list and append the parameter name (in original case)
     * and then its value. It will not URL-encode the parameter values before
     * constructing this string. There are no separators.
     *
     * If Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    private function _signParameters(array $parameters, $key) {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm = "HmacSHA1";
        $stringToSign = null;
        if (2 == $signatureVersion) {
            $algorithm = $this->_config['SignatureMethod'];
            $parameters['SignatureMethod'] = $algorithm;
            $stringToSign = $this->_calculateStringToSignV2($parameters);
        } else {
            throw new Exception("Invalid Signature Version specified");
        }
        return $this->_sign($stringToSign, $key, $algorithm);
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private function _calculateStringToSignV2(array $parameters) {
        $data = 'POST';
        $data .= "\n";
        $endpoint = parse_url ($this->_merchantValues->getServiceUrl());
        $data .= $endpoint['host'];
        $data .= "\n";
        $uri = array_key_exists('path', $endpoint) ? $endpoint['path'] : null;
        if (!isset ($uri)) {
        	$uri = "/";
        }
		$uriencoded = implode("/", array_map(array($this, "_urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->_getParametersAsString($parameters);
        return $data;
    }

    private function _urlencode($value) {
		return str_replace('%7E', '~', rawurlencode($value));
    }


    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $key, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception ("Non-supported signing method specified");
        }
        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }


    /**
     * Formats date as ISO 8601 timestamp
     */
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }
    
    /**
     * Formats date as ISO 8601 timestamp
     */
    private function getFormattedTimestamp($dateTime)
    {
	    return $dateTime->format(DATE_ISO8601);
    }



                                                
    /**
     * Convert CaptureRequest to name value pairs
     */
    private function _convertCapture($request) {
        
        $parameters = array();
        $parameters['Action'] = 'Capture';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonAuthorizationId()) {
            $parameters['AmazonAuthorizationId'] =  $request->getAmazonAuthorizationId();
        }
        if ($request->isSetCaptureReferenceId()) {
            $parameters['CaptureReferenceId'] =  $request->getCaptureReferenceId();
        }
        if ($request->isSetCaptureAmount()) {
            $captureAmountcaptureRequest = $request->getCaptureAmount();
            if ($captureAmountcaptureRequest->isSetAmount()) {
                $parameters['CaptureAmount' . '.' . 'Amount'] =  $captureAmountcaptureRequest->getAmount();
            }
            if ($captureAmountcaptureRequest->isSetCurrencyCode()) {
                $parameters['CaptureAmount' . '.' . 'CurrencyCode'] =  $captureAmountcaptureRequest->getCurrencyCode();
            }
        }
        if ($request->isSetSellerCaptureNote()) {
            $parameters['SellerCaptureNote'] =  $request->getSellerCaptureNote();
        }
        if ($request->isSetSoftDescriptor()) {
            $parameters['SoftDescriptor'] =  $request->getSoftDescriptor();
        }
        if ($request->isSetProviderCreditList()) {
        	$providerCreditListcaptureRequest = $request->getProviderCreditList();
        	foreach ($providerCreditListcaptureRequest->getmember() as $memberproviderCreditListIndex => $memberproviderCreditList) {
        		if ($memberproviderCreditList->isSetProviderId()) {
        			$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'ProviderId'] =  $memberproviderCreditList->getProviderId();
        		}
        		if ($memberproviderCreditList->isSetCreditAmount()) {
        			$creditAmountmember = $memberproviderCreditList->getCreditAmount();
        			if ($creditAmountmember->isSetAmount()) {
        				$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'CreditAmount' . '.' . 'Amount'] =  $creditAmountmember->getAmount();
        			}
        			if ($creditAmountmember->isSetCurrencyCode()) {
        				$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'CreditAmount' . '.' . 'CurrencyCode'] =  $creditAmountmember->getCurrencyCode();
        			}
        		}
        
        	}
        }
        
        return $parameters;
    }
        
                                                
    /**
     * Convert RefundRequest to name value pairs
     */
    private function _convertRefund($request) {
        
        $parameters = array();
        $parameters['Action'] = 'Refund';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonCaptureId()) {
            $parameters['AmazonCaptureId'] =  $request->getAmazonCaptureId();
        }
        if ($request->isSetRefundReferenceId()) {
            $parameters['RefundReferenceId'] =  $request->getRefundReferenceId();
        }
        if ($request->isSetRefundAmount()) {
            $refundAmount = $request->getRefundAmount();
            if ($refundAmount->isSetAmount()) {
                $parameters['RefundAmount' . '.' . 'Amount'] =  $refundAmount->getAmount();
            }
            if ($refundAmount->isSetCurrencyCode()) {
                $parameters['RefundAmount' . '.' . 'CurrencyCode'] =  $refundAmount->getCurrencyCode();
            }
        }
        if ($request->isSetSellerRefundNote()) {
            $parameters['SellerRefundNote'] =  $request->getSellerRefundNote();
        }
        if ($request->isSetSoftDescriptor()) {
            $parameters['SoftDescriptor'] =  $request->getSoftDescriptor();
        }
        if ($request->isSetProviderCreditReversalList()) {
        	$providerCreditReversalListrefundRequest = $request->getProviderCreditReversalList();
        	foreach ($providerCreditReversalListrefundRequest->getmember() as $memberproviderCreditReversalListIndex => $memberproviderCreditReversalList) {
        		if ($memberproviderCreditReversalList->isSetProviderId()) {
        			$parameters['ProviderCreditReversalList' . '.' . 'member' . '.'  . ($memberproviderCreditReversalListIndex + 1) . '.' . 'ProviderId'] =  $memberproviderCreditReversalList->getProviderId();
        		}
        		if ($memberproviderCreditReversalList->isSetCreditReversalAmount()) {
        			$creditReversalAmountmember = $memberproviderCreditReversalList->getCreditReversalAmount();
        			if ($creditReversalAmountmember->isSetAmount()) {
        				$parameters['ProviderCreditReversalList' . '.' . 'member' . '.'  . ($memberproviderCreditReversalListIndex + 1) . '.' . 'CreditReversalAmount' . '.' . 'Amount'] =  $creditReversalAmountmember->getAmount();
        			}
        			if ($creditReversalAmountmember->isSetCurrencyCode()) {
        				$parameters['ProviderCreditReversalList' . '.' . 'member' . '.'  . ($memberproviderCreditReversalListIndex + 1) . '.' . 'CreditReversalAmount' . '.' . 'CurrencyCode'] =  $creditReversalAmountmember->getCurrencyCode();
        			}
        		}
        
        	}
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert CloseAuthorizationRequest to name value pairs
     */
    private function _convertCloseAuthorization($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CloseAuthorization';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonAuthorizationId()) {
            $parameters['AmazonAuthorizationId'] =  $request->getAmazonAuthorizationId();
        }
        if ($request->isSetClosureReason()) {
            $parameters['ClosureReason'] =  $request->getClosureReason();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetRefundDetailsRequest to name value pairs
     */
    private function _convertGetRefundDetails($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetRefundDetails';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonRefundId()) {
            $parameters['AmazonRefundId'] =  $request->getAmazonRefundId();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetCaptureDetailsRequest to name value pairs
     */
    private function _convertGetCaptureDetails($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetCaptureDetails';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonCaptureId()) {
            $parameters['AmazonCaptureId'] =  $request->getAmazonCaptureId();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert CloseOrderReferenceRequest to name value pairs
     */
    private function _convertCloseOrderReference($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CloseOrderReference';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetClosureReason()) {
            $parameters['ClosureReason'] =  $request->getClosureReason();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert ConfirmOrderReferenceRequest to name value pairs
     */
    private function _convertConfirmOrderReference($request) {
        
        $parameters = array();
        $parameters['Action'] = 'ConfirmOrderReference';
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }

        return $parameters;
    }
        
                                        
    /**
     * Convert GetOrderReferenceDetailsRequest to name value pairs
     */
    private function _convertGetOrderReferenceDetails($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetOrderReferenceDetails';
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAddressConsentToken()) {
            $parameters['AddressConsentToken'] = $request->getAddressConsentToken();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert AuthorizeRequest to name value pairs
     */
    private function _convertAuthorize($request) {
        
        $parameters = array();
        $parameters['Action'] = 'Authorize';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetAuthorizationReferenceId()) {
            $parameters['AuthorizationReferenceId'] =  $request->getAuthorizationReferenceId();
        }
        if ($request->isSetAuthorizationAmount()) {
            $authorizationAmount = $request->getAuthorizationAmount();
            if ($authorizationAmount->isSetAmount()) {
                $parameters['AuthorizationAmount' . '.' . 'Amount'] =  $authorizationAmount->getAmount();
            }
            if ($authorizationAmount->isSetCurrencyCode()) {
                $parameters['AuthorizationAmount' . '.' . 'CurrencyCode'] =  $authorizationAmount->getCurrencyCode();
            }
        }
        if ($request->isSetSellerAuthorizationNote()) {
            $parameters['SellerAuthorizationNote'] =  $request->getSellerAuthorizationNote();
        }
        if ($request->isSetOrderItemCategories()) {
            $orderItemCategories = $request->getOrderItemCategories();
            foreach  ($orderItemCategories->getOrderItemCategory() as $orderItemCategoryIndex => $orderItemCategory) {
                $parameters['OrderItemCategories' . '.' . 'OrderItemCategory' . '.'  . ($orderItemCategoryIndex + 1)] =  $orderItemCategory;
            }
        }
        if ($request->isSetTransactionTimeout()) {
            $parameters['TransactionTimeout'] =  $request->getTransactionTimeout();
        }
        if ($request->isSetCaptureNow()) {
            $parameters['CaptureNow'] =  $request->getCaptureNow() ? "true" : "false";
        }
        if ($request->isSetSoftDescriptor()) {
            $parameters['SoftDescriptor'] =  $request->getSoftDescriptor();
        }
        if ($request->isSetProviderCreditList()) {
        	$providerCreditListauthorizeRequest = $request->getProviderCreditList();
        	foreach ($providerCreditListauthorizeRequest->getProviderCredit() as $memberproviderCreditListIndex => $memberproviderCreditList) {
        		if ($memberproviderCreditList->isSetProviderId()) {
        			$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'ProviderId'] =  $memberproviderCreditList->getProviderId();
        		}
        		if ($memberproviderCreditList->isSetCreditAmount()) {
        			$creditAmountmember = $memberproviderCreditList->getCreditAmount();
        			if ($creditAmountmember->isSetAmount()) {
        				$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'CreditAmount' . '.' . 'Amount'] =  $creditAmountmember->getAmount();
        			}
        			if ($creditAmountmember->isSetCurrencyCode()) {
        				$parameters['ProviderCreditList' . '.' . 'member' . '.'  . ($memberproviderCreditListIndex + 1) . '.' . 'CreditAmount' . '.' . 'CurrencyCode'] =  $creditAmountmember->getCurrencyCode();
        			}
        		}
        	}
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert SetOrderReferenceDetailsRequest to name value pairs
     */
    private function _convertSetOrderReferenceDetails($request) {
        
        $parameters = array();
        $parameters['Action'] = 'SetOrderReferenceDetails';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetOrderReferenceAttributes()) {
            $orderReferenceAttributes = $request->getOrderReferenceAttributes();
            if ($orderReferenceAttributes->isSetOrderTotal()) {
                $orderTotal = $orderReferenceAttributes->getOrderTotal();
                if ($orderTotal->isSetCurrencyCode()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'OrderTotal' . '.' . 'CurrencyCode'] =  $orderTotal->getCurrencyCode();
                }
                if ($orderTotal->isSetAmount()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'OrderTotal' . '.' . 'Amount'] =  $orderTotal->getAmount();
                }
            }
            if ($orderReferenceAttributes->isSetPlatformId()) {
                $parameters['OrderReferenceAttributes' . '.' . 'PlatformId'] =  $orderReferenceAttributes->getPlatformId();
            }
            if ($orderReferenceAttributes->isSetSellerNote()) {
                $parameters['OrderReferenceAttributes' . '.' . 'SellerNote'] =  $orderReferenceAttributes->getSellerNote();
            }
            if ($orderReferenceAttributes->isSetSellerOrderAttributes()) {
                $sellerOrderAttributes = $orderReferenceAttributes->getSellerOrderAttributes();
                if ($sellerOrderAttributes->isSetSellerOrderId()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'SellerOrderId'] =  $sellerOrderAttributes->getSellerOrderId();
                }
                if ($sellerOrderAttributes->isSetStoreName()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'StoreName'] =  $sellerOrderAttributes->getStoreName();
                }
                if ($sellerOrderAttributes->isSetOrderItemCategories()) {
                    $orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
                    foreach  ($orderItemCategories->getOrderItemCategory() as $orderItemCategoryIndex => $orderItemCategory) {
                        $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'OrderItemCategories' . '.' . 'OrderItemCategory' . '.'  . ($orderItemCategoryIndex + 1)] =  $orderItemCategory;
                    }
                }
                if ($sellerOrderAttributes->isSetCustomInformation()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'CustomInformation'] =  $sellerOrderAttributes->getCustomInformation();
                }
            }
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetAuthorizationDetailsRequest to name value pairs
     */
    private function _convertGetAuthorizationDetails($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetAuthorizationDetails';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonAuthorizationId()) {
            $parameters['AmazonAuthorizationId'] =  $request->getAmazonAuthorizationId();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert CancelOrderReferenceRequest to name value pairs
     */
    private function _convertCancelOrderReference($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CancelOrderReference';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonOrderReferenceId()) {
            $parameters['AmazonOrderReferenceId'] =  $request->getAmazonOrderReferenceId();
        }
        if ($request->isSetCancelationReason()) {
            $parameters['CancelationReason'] =  $request->getCancelationReason();
        }

        return $parameters;
    }
    
    
    /**
     * Convert CreateOrderReferenceForIdRequest to name value pairs
     */
    private function _convertCreateOrderReferenceForId($request) {
    
        $parameters = array();
        $parameters['Action'] = 'CreateOrderReferenceForId';
        if ($request->isSetId()) {
            $parameters['Id'] =  $request->getId();
        }
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetIdType()) {
            $parameters['IdType'] =  $request->getIdType();
        }
        if ($request->isSetInheritShippingAddress()) {
            $parameters['InheritShippingAddress'] =  $request->getInheritShippingAddress() ? "true" : "false";
        }
        if ($request->isSetConfirmNow()) {
            $parameters['ConfirmNow'] =  $request->getConfirmNow() ? "true" : "false";
        }
        if ($request->isSetOrderReferenceAttributes()) {
            $orderReferenceAttributescreateOrderReferenceForIdRequest = $request->getOrderReferenceAttributes();
            if ($orderReferenceAttributescreateOrderReferenceForIdRequest->isSetOrderTotal()) {
                $orderTotalorderReferenceAttributes = $orderReferenceAttributescreateOrderReferenceForIdRequest->getOrderTotal();
                if ($orderTotalorderReferenceAttributes->isSetCurrencyCode()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'OrderTotal' . '.' . 'CurrencyCode'] =  $orderTotalorderReferenceAttributes->getCurrencyCode();
                }
                if ($orderTotalorderReferenceAttributes->isSetAmount()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'OrderTotal' . '.' . 'Amount'] =  $orderTotalorderReferenceAttributes->getAmount();
                }
            }
            if ($orderReferenceAttributescreateOrderReferenceForIdRequest->isSetPlatformId()) {
                $parameters['OrderReferenceAttributes' . '.' . 'PlatformId'] =  $orderReferenceAttributescreateOrderReferenceForIdRequest->getPlatformId();
            }
            if ($orderReferenceAttributescreateOrderReferenceForIdRequest->isSetSellerNote()) {
                $parameters['OrderReferenceAttributes' . '.' . 'SellerNote'] =  $orderReferenceAttributescreateOrderReferenceForIdRequest->getSellerNote();
            }
            if ($orderReferenceAttributescreateOrderReferenceForIdRequest->isSetSellerOrderAttributes()) {
                $sellerOrderAttributesorderReferenceAttributes = $orderReferenceAttributescreateOrderReferenceForIdRequest->getSellerOrderAttributes();
                if ($sellerOrderAttributesorderReferenceAttributes->isSetSellerOrderId()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'SellerOrderId'] =  $sellerOrderAttributesorderReferenceAttributes->getSellerOrderId();
                }
                if ($sellerOrderAttributesorderReferenceAttributes->isSetStoreName()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'StoreName'] =  $sellerOrderAttributesorderReferenceAttributes->getStoreName();
                }
                if ($sellerOrderAttributesorderReferenceAttributes->isSetOrderItemCategories()) {
                    $orderItemCategoriessellerOrderAttributes = $sellerOrderAttributesorderReferenceAttributes->getOrderItemCategories();
                    foreach  ($orderItemCategoriessellerOrderAttributes->getOrderItemCategory() as $orderItemCategoryorderItemCategoriesIndex => $orderItemCategoryorderItemCategories) {
                        $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'OrderItemCategories' . '.' . 'OrderItemCategory' . '.'  . ($orderItemCategoryorderItemCategoriesIndex + 1)] =  $orderItemCategoryorderItemCategories;
                    }
                }
                if ($sellerOrderAttributesorderReferenceAttributes->isSetCustomInformation()) {
                    $parameters['OrderReferenceAttributes' . '.' . 'SellerOrderAttributes' . '.' . 'CustomInformation'] =  $sellerOrderAttributesorderReferenceAttributes->getCustomInformation();
                }
            }
        }
    
        return $parameters;
    }
    
    
    /**
     * Convert GetBillingAgreementDetailsRequest to name value pairs
     */
    private function _convertGetBillingAgreementDetails($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'GetBillingAgreementDetails';
    	if ($request->isSetAmazonBillingAgreementId()) {
    		$parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
    	}
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAddressConsentToken()) {
    		$parameters['AddressConsentToken'] =  $request->getAddressConsentToken();
    	}
    
    	return $parameters;
    }
    
    
    /**
     * Convert SetBillingAgreementDetailsRequest to name value pairs
     */
    private function _convertSetBillingAgreementDetails($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'SetBillingAgreementDetails';
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAmazonBillingAgreementId()) {
    		$parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
    	}
    	if ($request->isSetBillingAgreementAttributes()) {
    		$billingAgreementAttributessetBillingAgreementDetailsRequest = $request->getBillingAgreementAttributes();
    		if ($billingAgreementAttributessetBillingAgreementDetailsRequest->isSetPlatformId()) {
    			$parameters['BillingAgreementAttributes' . '.' . 'PlatformId'] =  $billingAgreementAttributessetBillingAgreementDetailsRequest->getPlatformId();
    		}
    		if ($billingAgreementAttributessetBillingAgreementDetailsRequest->isSetSellerNote()) {
    			$parameters['BillingAgreementAttributes' . '.' . 'SellerNote'] =  $billingAgreementAttributessetBillingAgreementDetailsRequest->getSellerNote();
    		}
    		if ($billingAgreementAttributessetBillingAgreementDetailsRequest->isSetSellerBillingAgreementAttributes()) {
    			$sellerBillingAgreementAttributesbillingAgreementAttributes = $billingAgreementAttributessetBillingAgreementDetailsRequest->getSellerBillingAgreementAttributes();
    			if ($sellerBillingAgreementAttributesbillingAgreementAttributes->isSetSellerBillingAgreementId()) {
    				$parameters['BillingAgreementAttributes' . '.' . 'SellerBillingAgreementAttributes' . '.' . 'SellerBillingAgreementId'] =  $sellerBillingAgreementAttributesbillingAgreementAttributes->getSellerBillingAgreementId();
    			}
    			if ($sellerBillingAgreementAttributesbillingAgreementAttributes->isSetStoreName()) {
    				$parameters['BillingAgreementAttributes' . '.' . 'SellerBillingAgreementAttributes' . '.' . 'StoreName'] =  $sellerBillingAgreementAttributesbillingAgreementAttributes->getStoreName();
    			}
    			if ($sellerBillingAgreementAttributesbillingAgreementAttributes->isSetCustomInformation()) {
    				$parameters['BillingAgreementAttributes' . '.' . 'SellerBillingAgreementAttributes' . '.' . 'CustomInformation'] =  $sellerBillingAgreementAttributesbillingAgreementAttributes->getCustomInformation();
    			}
    		}
    	}
    
    	return $parameters;
    }
    
    
    /**
     * Convert ConfirmBillingAgreementRequest to name value pairs
     */
    private function _convertConfirmBillingAgreement($request) {
        
        $parameters = array();
        $parameters['Action'] = 'ConfirmBillingAgreement';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetAmazonBillingAgreementId()) {
            $parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
        }

        return $parameters;
    }
    
    
    /**
     * Convert ValidateBillingAgreementRequest to name value pairs
     */
    private function _convertValidateBillingAgreement($request) {
    
        $parameters = array();
        $parameters['Action'] = 'ValidateBillingAgreement';
        if ($request->isSetAmazonBillingAgreementId()) {
            $parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
        }
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
    
        return $parameters;
    }
    
    
    /**
     * Convert AuthorizeOnBillingAgreementRequest to name value pairs
     */
    private function _convertAuthorizeOnBillingAgreement($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'AuthorizeOnBillingAgreement';
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAmazonBillingAgreementId()) {
    		$parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
    	}
    	if ($request->isSetAuthorizationReferenceId()) {
    		$parameters['AuthorizationReferenceId'] =  $request->getAuthorizationReferenceId();
    	}
    	if ($request->isSetAuthorizationAmount()) {
    		$authorizationAmountauthorizeOnBillingAgreementRequest = $request->getAuthorizationAmount();
    		if ($authorizationAmountauthorizeOnBillingAgreementRequest->isSetAmount()) {
    			$parameters['AuthorizationAmount' . '.' . 'Amount'] =  $authorizationAmountauthorizeOnBillingAgreementRequest->getAmount();
    		}
    		if ($authorizationAmountauthorizeOnBillingAgreementRequest->isSetCurrencyCode()) {
    			$parameters['AuthorizationAmount' . '.' . 'CurrencyCode'] =  $authorizationAmountauthorizeOnBillingAgreementRequest->getCurrencyCode();
    		}
    	}
    	if ($request->isSetSellerAuthorizationNote()) {
    		$parameters['SellerAuthorizationNote'] =  $request->getSellerAuthorizationNote();
    	}
    	if ($request->isSetTransactionTimeout()) {
    		$parameters['TransactionTimeout'] =  $request->getTransactionTimeout();
    	}
    	if ($request->isSetCaptureNow()) {
    		$parameters['CaptureNow'] =  $request->getCaptureNow() ? "true" : "false";
    	}
    	if ($request->isSetSoftDescriptor()) {
    		$parameters['SoftDescriptor'] =  $request->getSoftDescriptor();
    	}
    	if ($request->isSetSellerNote()) {
    		$parameters['SellerNote'] =  $request->getSellerNote();
    	}
    	if ($request->isSetPlatformId()) {
    		$parameters['PlatformId'] =  $request->getPlatformId();
    	}
    	if ($request->isSetSellerOrderAttributes()) {
    		$sellerOrderAttributesauthorizeOnBillingAgreementRequest = $request->getSellerOrderAttributes();
    		if ($sellerOrderAttributesauthorizeOnBillingAgreementRequest->isSetSellerOrderId()) {
    			$parameters['SellerOrderAttributes' . '.' . 'SellerOrderId'] =  $sellerOrderAttributesauthorizeOnBillingAgreementRequest->getSellerOrderId();
    		}
    		if ($sellerOrderAttributesauthorizeOnBillingAgreementRequest->isSetStoreName()) {
    			$parameters['SellerOrderAttributes' . '.' . 'StoreName'] =  $sellerOrderAttributesauthorizeOnBillingAgreementRequest->getStoreName();
    		}
    		if ($sellerOrderAttributesauthorizeOnBillingAgreementRequest->isSetOrderItemCategories()) {
    			$orderItemCategoriessellerOrderAttributes = $sellerOrderAttributesauthorizeOnBillingAgreementRequest->getOrderItemCategories();
    			foreach  ($orderItemCategoriessellerOrderAttributes->getOrderItemCategory() as $orderItemCategoryorderItemCategoriesIndex => $orderItemCategoryorderItemCategories) {
    				$parameters['SellerOrderAttributes' . '.' . 'OrderItemCategories' . '.' . 'OrderItemCategory' . '.'  . ($orderItemCategoryorderItemCategoriesIndex + 1)] =  $orderItemCategoryorderItemCategories;
    			}
    		}
    		if ($sellerOrderAttributesauthorizeOnBillingAgreementRequest->isSetCustomInformation()) {
    			$parameters['SellerOrderAttributes' . '.' . 'CustomInformation'] =  $sellerOrderAttributesauthorizeOnBillingAgreementRequest->getCustomInformation();
    		}
    	}
    	if ($request->isSetInheritShippingAddress()) {
    		$parameters['InheritShippingAddress'] =  $request->getInheritShippingAddress() ? "true" : "false";
    	}
    
    	return $parameters;
    }
    
    
    /**
     * Convert CloseBillingAgreementRequest to name value pairs
     */
    private function _convertCloseBillingAgreement($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'CloseBillingAgreement';
    	if ($request->isSetAmazonBillingAgreementId()) {
    		$parameters['AmazonBillingAgreementId'] =  $request->getAmazonBillingAgreementId();
    	}
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetClosureReason()) {
    		$parameters['ClosureReason'] =  $request->getClosureReason();
    	}
    	if ($request->isSetReasonCode()) {
    		$parameters['ReasonCode'] =  $request->getReasonCode();
    	}
    
    	return $parameters;
    }
    
    /**
     * Convert GetProviderCreditDetailsRequest to name value pairs
     */
    private function _convertGetProviderCreditDetails($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'GetProviderCreditDetails';
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAmazonProviderCreditId()) {
    		$parameters['AmazonProviderCreditId'] =  $request->getAmazonProviderCreditId();
    	}
    
    	return $parameters;
    }
    
    /**
     * Convert GetProviderCreditReversalDetailsRequest to name value pairs
     */
    private function _convertGetProviderCreditReversalDetails($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'GetProviderCreditReversalDetails';
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAmazonProviderCreditReversalId()) {
    		$parameters['AmazonProviderCreditReversalId'] =  $request->getAmazonProviderCreditReversalId();
    	}
    
    	return $parameters;
    }

    /**
     * Convert ReverseProviderCreditRequest to name value pairs
     */
    private function _convertReverseProviderCredit($request) {
    
    	$parameters = array();
    	$parameters['Action'] = 'ReverseProviderCredit';
    	if ($request->isSetSellerId()) {
    		$parameters['SellerId'] =  $request->getSellerId();
    	}
    	if ($request->isSetAmazonProviderCreditId()) {
    		$parameters['AmazonProviderCreditId'] =  $request->getAmazonProviderCreditId();
    	}
    	if ($request->isSetCreditReversalReferenceId()) {
    		$parameters['CreditReversalReferenceId'] =  $request->getCreditReversalReferenceId();
    	}
    	if ($request->isSetCreditReversalAmount()) {
    		$creditReversalAmountreverseProviderCreditRequest = $request->getCreditReversalAmount();
    		if ($creditReversalAmountreverseProviderCreditRequest->isSetAmount()) {
    			$parameters['CreditReversalAmount' . '.' . 'Amount'] =  $creditReversalAmountreverseProviderCreditRequest->getAmount();
    		}
    		if ($creditReversalAmountreverseProviderCreditRequest->isSetCurrencyCode()) {
    			$parameters['CreditReversalAmount' . '.' . 'CurrencyCode'] =  $creditReversalAmountreverseProviderCreditRequest->getCurrencyCode();
    		}
    	}
    	if ($request->isSetCreditReversalNote()) {
    		$parameters['CreditReversalNote'] =  $request->getCreditReversalNote();
    	}
    
    	return $parameters;
    }
                                                                                                                                                                                                                                        
}
?>
