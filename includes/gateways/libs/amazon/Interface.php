<?php
namespace PayWithAmazon;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Interface class to showcase the public API methods for Pay With Amazon */

interface ClientInterface
{
    /* Takes user configuration array from the user as input
     * Takes JSON file path with configuration information as input
     * Validates the user configuation array against existing config array
     */

    public function __construct($config = null);

    /* Setter for sandbox
     * Sets the boolean value for config['sandbox'] variable
     */

    public function setSandbox($value);

    /* Setter for config['client_id']
     * Sets the  value for config['client_id'] variable
     */

    public function setClientId($value);

    /* Setter for Proxy
     * input $proxy [array]
     * @param $proxy['proxy_user_host'] - hostname for the proxy
     * @param $proxy['proxy_user_port'] - hostname for the proxy
     * @param $proxy['proxy_user_name'] - if your proxy required a username
     * @param $proxy['proxy_user_password'] - if your proxy required a passowrd
     */

    public function setProxy($proxy);

    /* Setter for $_mwsServiceUrl
     * Set the URL to which the post request has to be made for unit testing
     */

    public function setMwsServiceUrl($url);

    /* Getter
     * Gets the value for the key if the key exists in config
     */

    public function __get($name);

    /* Getter for parameters string
     * Gets the value for the parameters string for unit testing
     */

    public function getParameters();

    /* GetUserInfo convenience funtion - Returns user's profile information from Amazon using the access token returned by the Button widget.
     *
     * @see http://docs.developer.amazonservices.com/en_US/apa_guide/APAGuide_ObtainProfile.html
     * @param $access_token [String]
     */

    public function getUserInfo($access_token);

    /* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetOrderReferenceDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['address_consent_token'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getOrderReferenceDetails($requestParameters = array());

    /* SetOrderReferenceDetails API call - Sets order reference details such as the order total and a description for the order.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetOrderReferenceDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function setOrderReferenceDetails($requestParameters = array());

    /* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmOrderReference.html

     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function confirmOrderReference($requestParameters = array());

    /* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CancelOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['cancelation_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function cancelOrderReference($requestParameters = array());

    /* CloseOrderReferenceDetails API call - Confirms that an order reference has been fulfilled (fully or partially)
     * and that you do not expect to create any new authorizations on this order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function closeOrderReference($requestParameters = array());

    /* CloseAuthorization API call - Closes an authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function closeAuthorization($requestParameters = array());

    /* Authorize API call - Reserves a specified amount against the payment method(s) stored in the order reference.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Authorize.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_order_reference_id'] - [String]
     * @param requestParameters['authorization_amount'] [String]
     * @param requestParameters['currency_code'] - [String]
     * @param requestParameters['authorization_reference_id'] [String]
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['provider_credit_details'] - [array (array())]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] [String] - Defaults to 1440 minutes
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function authorize($requestParameters = array());

    /* GetAuthorizationDetails API call - Returns the status of a particular authorization and the total amount captured on the authorization.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetAuthorizationDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getAuthorizationDetails($requestParameters = array());

    /* Capture API call - Captures funds from an authorized payment instrument.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Capture.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_authorization_id'] - [String]
     * @param requestParameters['capture_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @param requestParameters['capture_reference_id'] - [String]
     * @optional requestParameters['provider_credit_details'] - [array (array())]
     * @optional requestParameters['seller_capture_note'] - [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function capture($requestParameters = array());

    /* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetCaptureDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_capture_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getCaptureDetails($requestParameters = array());

    /* Refund API call - Refunds a previously captured amount.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_Refund.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_capture_id'] - [String]
     * @param requestParameters['refund_reference_id'] - [String]
     * @param requestParameters['refund_amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['provider_credit_reversal_details'] - [array(array())]
     * @optional requestParameters['seller_refund_note'] [String]
     * @optional requestParameters['soft_descriptor'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function refund($requestParameters = array());

    /* GetRefundDetails API call - Returns the status of a particular refund.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetRefundDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_refund_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getRefundDetails($requestParameters = array());

    /* GetServiceStatus API Call - Returns the operational status of the Off-Amazon Payments API section
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetServiceStatus.html
     *
     * The GetServiceStatus operation returns the operational status of the Off-Amazon Payments API
     * section of Amazon Marketplace Web Service (Amazon MWS).
     * Status values are GREEN, GREEN_I, YELLOW, and RED.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getServiceStatus($requestParameters = array());

    /* CreateOrderReferenceForId API Call - Creates an order reference for the given object
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CreateOrderReferenceForId.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['Id'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean]
     * @optional requestParameters['ConfirmNow'] - [Boolean]
     * @optional Amount (required when confirm_now is set to true) [String]
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function createOrderReferenceForId($requestParameters = array());

    /* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetBillingAgreementDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getBillingAgreementDetails($requestParameters = array());

    /* SetBillingAgreementDetails API call - Sets Billing Agreement details such as a description of the agreement and other information about the seller.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_SetBillingAgreementDetails.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param requestParameters['amount'] - [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['seller_billing_agreement_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function setBillingAgreementDetails($requestParameters = array());

    /* ConfirmBillingAgreement API Call - Confirms that the Billing Agreement is free of constraints and all required information has been set on the Billing Agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function confirmBillingAgreement($requestParameters = array());

    /* ValidateBillingAgreement API Call - Validates the status of the Billing Agreement object and the payment method associated with it.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ValidateBillignAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function validateBillingAgreement($requestParameters = array());

    /* AuthorizeOnBillingAgreement API call - Reserves a specified amount against the payment method(s) stored in the Billing Agreement.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_AuthorizeOnBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @param requestParameters['authorization_reference_id'] [String]
     * @param requestParameters['authorization_amount'] [String]
     * @param requestParameters['currency_code'] - [String]
     * @optional requestParameters['seller_authorization_note'] [String]
     * @optional requestParameters['transaction_timeout'] - Defaults to 1440 minutes
     * @optional requestParameters['capture_now'] [String]
     * @optional requestParameters['soft_descriptor'] - - [String]
     * @optional requestParameters['seller_note'] - [String]
     * @optional requestParameters['platform_id'] - [String]
     * @optional requestParameters['custom_information'] - [String]
     * @optional requestParameters['seller_order_id'] - [String]
     * @optional requestParameters['store_name'] - [String]
     * @optional requestParameters['inherit_shipping_address'] [Boolean] - Defaults to true
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function authorizeOnBillingAgreement($requestParameters = array());

    /* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
     * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseBillingAgreement.html
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_billing_agreement_id'] - [String]
     * @optional requestParameters['closure_reason'] [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function closeBillingAgreement($requestParameters = array());

    /* charge convenience method
     * Performs the API calls
     * 1. SetOrderReferenceDetails / SetBillingAgreementDetails
     * 2. ConfirmOrderReference / ConfirmBillingAgreement
     * 3. Authorize (with Capture) / AuthorizeOnBillingAgreeemnt (with Capture)
     *
     * @param requestParameters['merchant_id'] - [String]
     *
     * @param requestParameters['amazon_reference_id'] - [String] : Order Reference ID /Billing Agreement ID
     * If requestParameters['amazon_reference_id'] is empty then the following is required,
     * @param requestParameters['amazon_order_reference_id'] - [String] : Order Reference ID
     * or,
     * @param requestParameters['amazon_billing_agreement_id'] - [String] : Billing Agreement ID
     *
     * @param $requestParameters['charge_amount'] - [String] : Amount value to be captured
     * @param requestParameters['currency_code'] - [String] : Currency Code for the Amount
     * @param requestParameters['authorization_reference_id'] - [String]- Any unique string that needs to be passed
     * @optional requestParameters['charge_note'] - [String] : Seller Note sent to the buyer
     * @optional requestParameters['transaction_timeout'] - [String] : Defaults to 1440 minutes
     * @optional requestParameters['charge_order_id'] - [String] : Custom Order ID provided
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function charge($requestParameters = array());

    /* GetProviderCreditDetails API Call - Get the details of the Provider Credit.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getProviderCreditDetails($requestParameters = array());

    /* GetProviderCreditReversalDetails API Call - Get details of the Provider Credit Reversal.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_reversal_id'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function getProviderCreditReversalDetails($requestParameters = array());

    /* ReverseProviderCredit API Call - Reverse the Provider Credit.
     *
     * @param requestParameters['merchant_id'] - [String]
     * @param requestParameters['amazon_provider_credit_id'] - [String]
     * @optional requestParameters['credit_reversal_reference_id'] - [String]
     * @param requestParameters['credit_reversal_amount'] - [String]
     * @optional requestParameters['currency_code'] - [String]
     * @optional requestParameters['credit_reversal_note'] - [String]
     * @optional requestParameters['mws_auth_token'] - [String]
     */

    public function reverseProviderCredit($requestParameters = array());
}

/* Interface for IpnHandler.php */

interface IpnHandlerInterface
{
   /* Takes headers and body of the IPN message as input in the constructor
    * verifies that the IPN is from the right resource and has the valid data
    */

    public function __construct($headers, $body, $ipnConfig = null);

    /* returnMessage() - JSON decode the raw [Message] portion of the IPN */

    public function returnMessage();

    /* toJson() - Converts IPN [Message] field to JSON
     *
     * Has child elements
     * ['NotificationData'] [XML] - API call XML notification data
     * @param remainingFields - consists of remaining IPN array fields that are merged
     * Type - Notification
     * MessageId -  ID of the Notification
     * Topic ARN - Topic of the IPN
     * @return response in JSON format
     */

    public function toJson();

    /* toArray() - Converts IPN [Message] field to associative array
     * @return response in array format
     */

    public function toArray();
}

/* Interface for HttpCurl.php */

interface HttpCurlInterface
{
    /* Takes user configuration array as input
     * Takes configuration for API call or IPN config
     */

    public function __construct($config = null);

    /* Set Http header for Access token for the GetUserInfo call */

    public function setHttpHeader();

    /* Setter for  Access token to get the user info */

    public function setAccessToken($accesstoken);

    /* POST using curl for the following situations
     * 1. API calls
     * 2. IPN certificate retrieval
     * 3. Get User Info
     */

    public function httpPost($url, $userAgent = null, $parameters = null);

    /* GET using curl for the following situations
     * 1. IPN certificate retrieval
     * 3. Get User Info
     */

    public function httpGet($url, $userAgent = null);
}

/* Interface for ResponseParser.php */

interface ResponseInterface
{
    /* Takes response from the API call */

    public function __construct($response = null);

    /* Returns the XML portion of the response */

    public function toXml();

    /* toJson  - converts XML into Json
     * @param $response [XML]
     */

    public function toJson();

    /* toArray  - converts XML into associative array
     * @param $this->_response [XML]
     */

    public function toArray();

    /* Get the status of the BillingAgreement */

    public function getBillingAgreementDetailsStatus($response);
}
