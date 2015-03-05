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

/* 
 * Automatic payments simple checkout example
 *
 * This demonstrates a merchant use case where the item is in stock, and
 * the billing agreement creation is immediately followed by the order
 * confirmation and capture of funds for the merchant
 *
 * This use case makes the assumption that the merchant is using the billing 
 * agreement address capture widget to capture the destination address for the 
 * order, and uses the address information to calculate a tax and shipping rate
 *
 * This example will show the following service calls:
 *    - GetBillingAgreementDetails
 *    - SetBillingAgreementDetails
 *    - ConfirmBillingAgreement
 *    - AuthorizeOnBillingAgreement
 *    - GetAuthorizeDetails
 *    - Capture
 *    - GetCaptureDetails
 *    - CloseBillingAgreement
 */
require_once realpath(dirname(__FILE__)) . '/.config.inc.php';
require_once realpath(dirname(__FILE__)) . '/Utils/ShippingAndTaxCostHelper.php';

/**
 * AutomaticPaymentsSimpleCheckoutExample includes the logic required to capture
 * an order and perform all actions to capture the order amount fromthe buyer.
 */
class AutomaticPaymentsSimpleCheckoutExample
{

    private $_sellerId;

    private $_service;

    private $_amazonBillingAgreementId;

    private $_shippingAndTaxCostHelper;

    /**
     * Create a new instance of the automatic payment simple checkout example
     * case
     *
     * @param OffAmazonPaymentsService_Client $service
     *            instance of the service client
     * @param string $amazonBillingAgreementId
     *            an billing agreement object in draft state
     *            
     * @return new AutomaticPaymentsSimpleCheckoutExample
     */
    public function __construct ($service, $amazonBillingAgreementId)
    {
        $this->_service = $service;
        
        $this->_amazonBillingAgreementId = $amazonBillingAgreementId;
        
        $this->_sellerId = $this->_service->getMerchantValues()->getMerchantId();
        
        $this->_shippingAndTaxCostHelper = new ShippingAndTaxCostHelper();
    }

    /**
     * Use the billing agreement object to query the automatic payment
     * information, including the current physical delivery address as selected
     * by the buyer
     *
     * @return OffAmazonPaymentsService_Model_GetBillingAgreementDetailsResponse
     *         service response
     */
    public function getBillingAgreementDetails ()
    {
        $getBillingAgreementDetailsRequest = new OffAmazonPaymentsService_Model_GetBillingAgreementDetailsRequest();
        $getBillingAgreementDetailsRequest->setSellerId($this->_sellerId);
        $getBillingAgreementDetailsRequest->setAmazonBillingAgreementId(
                $this->_amazonBillingAgreementId);
        
        return $this->_service->getBillingAgreementDetails($getBillingAgreementDetailsRequest);
    }

    /**
     * Calculate the amount to charge the buyer for each payment, based on the
     * buyer destination
     *
     * Note that until the billing agreement is confirmed, the name & address
     * fields will not be returned to the client.
     *
     * @param OffAmazonPaymentsService_Model_BillingAgreementDetails $BillingAgreementDetails
     *            response
     * @param string $orderAmountPreTaxAndShipping
     *            order amount
     * @param int $shippingType
     *            shipping type
     *            
     * @return float total amount for the order, with shipping and tax included
     */
    public function calculatePaymentAmountBasedOnBuyerDetails ($BillingAgreementDetails, 
            $orderAmountPreTaxAndShipping, $shippingType)
    {
        return $this->_shippingAndTaxCostHelper->calculateTotalAmount($BillingAgreementDetails, 
                $orderAmountPreTaxAndShipping, $shippingType);
    }

    /**
     * Set seller specific information to the billing agreement details.
     *
     * @return OffAmazonPaymentsService_Model_SetBillingAgreementDetailsResponse
     *         service response
     */
    public function addSellerInformationToBillingAgreement ()
    {
        $sellerBillingAgreementAttributes = new OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes();
        $sellerBillingAgreementAttributes->setSellerBillingAgreementId(
                $this->_amazonBillingAgreementId);
        $sellerBillingAgreementAttributes->setStoreName("Your store name here");
        $sellerBillingAgreementAttributes->setCustomInformation(
                "Additional information you wish to include with this billing agreement.");
        
        $billingAgreementAttributes = new OffAmazonPaymentsService_Model_BillingAgreementAttributes();
        $billingAgreementAttributes->setSellerNote(
                "Description of the billing agreement that is displayed to the buyer in the emails.");
        $billingAgreementAttributes->setSellerBillingAgreementAttributes(
                $sellerBillingAgreementAttributes);
        
        $setBillingAgreementDetailsRequest = new OffAmazonPaymentsService_Model_SetBillingAgreementDetailsRequest();
        $setBillingAgreementDetailsRequest->setAmazonBillingAgreementId(
                $this->_amazonBillingAgreementId);
        $setBillingAgreementDetailsRequest->setSellerId($this->_sellerId);
        $setBillingAgreementDetailsRequest->setBillingAgreementAttributes(
                $billingAgreementAttributes);
        return $this->_service->setBillingAgreementDetails($setBillingAgreementDetailsRequest);
    }

    /**
     * Confirm the billing agreement information, allowing for authorizations
     * and captures to be created
     *
     * @return OffAmazonPaymentsService_Model_ConfirmBillingAgreementResponse
     *         service response
     */
    public function confirmBillingAgreement ()
    {
        $confirmBillingAgreementRequest = new OffAmazonPaymentsService_Model_ConfirmBillingAgreementRequest();
        $confirmBillingAgreementRequest->setAmazonBillingAgreementId(
                $this->_amazonBillingAgreementId);
        $confirmBillingAgreementRequest->setSellerId($this->_sellerId);
        
        return $this->_service->confirmBillingAgreement($confirmBillingAgreementRequest);
    }

    /**
     * Check that the billing agreement is in valid status and the selected payment
     * method is also valid. 
     * 
     * @return OffAmazonPaymentsService_Model_ValidateBillingAgreementResponse
     *         service response
     */
    public function validateBillingAgreement ()
    {
        $validateBillingAgreementRequest = new OffAmazonPaymentsService_Model_ValidateBillingAgreementRequest();
        $validateBillingAgreementRequest->setAmazonBillingAgreementId(
                $this->_amazonBillingAgreementId);
        $validateBillingAgreementRequest->setSellerId($this->_sellerId);
        
        return $this->_service->validateBillingAgreement($validateBillingAgreementRequest);
    }

    /**
     * Perform the authorize call on the billing agreement
     *
     * @param float $authorizationAmount
     *            amount to authorize from the buyer
     *            
     * @param string $authorizationReferenceId
     *            seller provided authorization reference id
     *            
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
     *         service response
     */
    public function authorizePaymentAmount ($authorizationAmount, $authorizationReferenceId)
    {
        $authorizeOnBillingAgreementRequest = $this->_createAuthorizeOnBillingAgreementRequest(
                $authorizationAmount, $authorizationReferenceId, false);
        return $this->_service->authorizeOnBillingAgreement($authorizeOnBillingAgreementRequest);
    }

    /**
     * Authorize on the billing agreement with auto capture
     *
     * @param float $authorizationAmount
     *            amount to authorize from the buyer
     *            
     * @param string $authorizationReferenceId
     *            seller provided authorization reference id
     *            
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementResponse
     *         service response
     */
    public function authorizePaymentAmountWithCaptureNow ($authorizationAmount, 
            $authorizationReferenceId)
    {
        $authorizeOnBillingAgreementRequest = $this->_createAuthorizeOnBillingAgreementRequest(
                $authorizationAmount, $authorizationReferenceId, true);
        return $this->_service->authorizeOnBillingAgreement($authorizeOnBillingAgreementRequest);
    }

    /**
     * Create AuthorizeOnBillingAgreement request
     *
     * @param float $authorizationAmount            
     * @param string $authorizationReferenceId            
     * @param bool $CaptureNow            
     *
     * @return OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest
     */
    private function _createAuthorizeOnBillingAgreementRequest ($authorizationAmount, 
            $authorizationReferenceId, $CaptureNow)
    {
        $authorizeOnBillingAgreementRequest = new OffAmazonPaymentsService_Model_AuthorizeOnBillingAgreementRequest();
        $authorizeOnBillingAgreementRequest->setAmazonBillingAgreementId(
                $this->_amazonBillingAgreementId);
        $authorizeOnBillingAgreementRequest->setSellerId($this->_sellerId);
        $authorizeOnBillingAgreementRequest->setAuthorizationReferenceId($authorizationReferenceId);
        $authorizeOnBillingAgreementRequest->setAuthorizationAmount(
                new OffAmazonPaymentsService_Model_Price());
        $authorizeOnBillingAgreementRequest->getAuthorizationAmount()->setAmount(
                $authorizationAmount);
        $authorizeOnBillingAgreementRequest->getAuthorizationAmount()->setCurrencyCode(
                $this->_service->getMerchantValues()
                    ->getCurrency());
        $authorizeOnBillingAgreementRequest->setCaptureNow($CaptureNow);
        return $authorizeOnBillingAgreementRequest;
    }

    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated.
     * WARNING: This is not the way to integrate for production systems,
     * instead merchants should use IPN to receive a callback once the
     * processing has been completed.
     * Note that Amazon reserves the right to throttle requests that
     * ignore this advice and poll for a response
     *
     * @param string $amazonAuthorizationReferenceId
     *            authorization transaction to query
     *            
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse
     *         service response
     */
    public function waitUntilAuthorizationProcessingIsCompleted ($amazonAuthorizationId)
    {
        $getAuthorizationDetailsResponse = null;
        do {
            sleep(5);
            $getAuthorizationDetailsResponse = $this->getAuthorizationDetails(
                    $amazonAuthorizationId);
        } while ((isset($getAuthorizationDetailsResponse)) and (strcasecmp(
                $getAuthorizationDetailsResponse->getGetAuthorizationDetailsResult()
                    ->getAuthorizationDetails()
                    ->getAuthorizationStatus()
                    ->getState(), "Pending") == 0));
        
        if (is_null($getAuthorizationDetailsResponse)) {
            throw new ErrorException("No valid response from getAuthorizationDetails request");
        }
        
        return $getAuthorizationDetailsResponse;
    }

    /**
     * Perform the getAuthroizationDetails request for the order
     *
     * @param string $amazonAuthorizationReferenceId
     *            authorization transaction
     *            to query
     *            
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse
     *         service response
     */
    public function getAuthorizationDetails ($amazonAuthorizationReferenceId)
    {
        $getAuthorizationDetailsRequest = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
        $getAuthorizationDetailsRequest->setSellerId($this->_sellerId);
        $getAuthorizationDetailsRequest->setAmazonAuthorizationId($amazonAuthorizationReferenceId);
        
        return $this->_service->getAuthorizationDetails($getAuthorizationDetailsRequest);
    }

    /**
     * Perform the capture call for the order
     *
     * @param float $captureAmount
     *            amount to capture from the buyer
     * @param string $amazonAuthorizationId
     *            auth id to perform the capture on
     *            
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function captureOrderAmount ($captureAmount, $amazonAuthorizationId)
    {
        $captureRequest = new OffAmazonPaymentsService_Model_CaptureRequest();
        $captureRequest->setSellerId($this->_sellerId);
        $captureRequest->setAmazonAuthorizationId($amazonAuthorizationId);
        $captureRequest->setCaptureReferenceId($amazonAuthorizationId . "-c01");
        $captureRequest->setCaptureAmount(new OffAmazonPaymentsService_Model_Price());
        $captureRequest->getCaptureAmount()->setAmount($captureAmount);
        $captureRequest->getCaptureAmount()->setCurrencyCode(
                $this->_service->getMerchantValues()
                    ->getCurrency());
        
        return $this->_service->capture($captureRequest);
    }

    /**
     * Perform the get capture details call for the order
     *
     * @param string $amazonCaptureId
     *            capture it to get details for
     *            
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function getCaptureDetails ($amazonCaptureId)
    {
        $captureDetailsRequest = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $captureDetailsRequest->setSellerId($this->_sellerId);
        $captureDetailsRequest->setAmazonCaptureId($amazonCaptureId);
        
        return $this->_service->getCaptureDetails($captureDetailsRequest);
    }

    /**
     * Close this billing agreement to indicate that the billing agreement is
     * complete, and
     * no further authorizations and captures will be performed on this billing
     * agreement.
     *
     * @return OffAmazonPaymentsService_Model_CloseBillingAgreementResponse
     *         service response
     */
    public function closeBillingAgreement ()
    {
        $closeBillingAgreementRequest = new OffAmazonPaymentsService_Model_CloseBillingAgreementRequest();
        $closeBillingAgreementRequest->setSellerId($this->_sellerId);
        $closeBillingAgreementRequest->setAmazonBillingAgreementId($this->_amazonBillingAgreementId);
        $closeBillingAgreementRequest->setClosureReason("Automatic payment complete");
        
        return $this->_service->closeBillingAgreement($closeBillingAgreementRequest);
    }
}
?>
