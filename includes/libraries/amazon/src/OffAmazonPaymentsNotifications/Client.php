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

require_once 'OffAmazonPaymentsNotifications/Interface.php';
require_once 'OffAmazonPaymentsNotifications/Impl/SnsMessageParser.php';
require_once 'OffAmazonPaymentsNotifications/Impl/SnsMessageValidator.php';
require_once 'OffAmazonPaymentsNotifications/Impl/OpenSslVerifySignature.php';
require_once 'OffAmazonPaymentsNotifications/Impl/IpnNotificationParser.php';
require_once 'OffAmazonPaymentsNotifications/Impl/XmlNotificationParser.php';
require_once 'OffAmazonPaymentsNotifications/InvalidMessageException.php';
require_once 'OffAmazonPaymentsNotifications/Notification.php';
require_once 'OffAmazonPaymentsNotifications/NotificationMetadata.php';
require_once 'OffAmazonPaymentsNotifications/Model/AuthorizationDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/AuthorizationNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreement.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreementLimits.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreementNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/BillingAgreementStatus.php';
require_once 'OffAmazonPaymentsNotifications/Model/CaptureDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/CaptureNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/IdList.php';
require_once 'OffAmazonPaymentsNotifications/Model/IpnNotificationMetadata.php';
require_once 'OffAmazonPaymentsNotifications/Model/NotificationImpl.php';
require_once 'OffAmazonPaymentsNotifications/Model/NotificationMetadataImpl.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderItemCategories.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderReference.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderReferenceNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderReferenceStatus.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderTotal.php';
require_once 'OffAmazonPaymentsNotifications/Model/Price.php';
require_once 'OffAmazonPaymentsNotifications/Model/RefundDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/RefundNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/SellerBillingAgreementAttributes.php';
require_once 'OffAmazonPaymentsNotifications/Model/SellerOrderAttributes.php';
require_once 'OffAmazonPaymentsNotifications/Model/SnsNotificationMetadata.php';
require_once 'OffAmazonPaymentsNotifications/Model/Status.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditSummary.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditSummaryList.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalSummary.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalSummaryList.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/ProviderCreditReversalDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/SolutionProviderMerchantNotification.php';
require_once 'OffAmazonPaymentsNotifications/Model/MerchantRegistrationDetails.php';
require_once 'OffAmazonPaymentsNotifications/Model/SolutionProviderOptions.php';
require_once 'OffAmazonPaymentsNotifications/Model/SolutionProviderOption.php';
require_once 'OffAmazonPaymentsService/MerchantValuesBuilder.php';
require_once 'OffAmazonPayments/HttpRequest/Impl/HttpRequestFactoryCurlImpl.php';

/**
 * Implementation of the OffAmazonPaymentsNotifications
 * library
 * 
 */
class OffAmazonPaymentsNotifications_Client 
    implements OffAmazonPaymentsNotifications_Interface
{
    /**
     * Store an instance of the sns message validator
     * object
     *
     * @var SnsMessageValidator
     */
    private $_snsMessageValidator = null;

    /**
     * Create an instance of the client class
     * 
     * @return void
     */
    public function __construct($config = null)
    {
        $merchantValues = OffAmazonPaymentsService_MerchantValuesBuilder::create($config)->build();
        $this->_snsMessageValidator 
            = new SnsMessageValidator(
                new OpenSslVerifySignature($merchantValues->getCnName(), 
                new HttpRequestFactoryCurlImpl($merchantValues)
                )
            );  
    }
    
    /**
     * Converts a http POST body and headers into
     * a notification object
     * 
     * @param array  $headers post request headers
     * @param string $body    post request body, should be json
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException
     * 
     * @return OffAmazonPaymentNotifications_Notification 
     */
    public function parseRawMessage($headers, $body)
    {
        // Is this json, is this
        // an sns message, do we have the fields we require
        $snsMessage = SnsMessageParser::parseNotification($headers, $body);
        // security validation - check that this message is
        // from amazon and that it has been signed correctly
        $this->_snsMessageValidator->validateMessage($snsMessage);
        
        // Convert to object - convert from basic class to object
        $ipnMessage = IpnNotificationParser::parseSnsMessage($snsMessage);

        return XmlNotificationParser::parseIpnMessage($ipnMessage);
    }
}
?>