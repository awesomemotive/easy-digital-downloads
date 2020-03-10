<?php
namespace PayWithAmazon;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/* Class Client
 * Takes configuration information
 * Makes API calls to MWS for Pay With Amazon
 * returns Response Object
 */

require_once 'ResponseParser.php';
require_once 'HttpCurl.php';
require_once 'Interface.php';

class Client implements ClientInterface
{
	const MWS_CLIENT_VERSION = '1.0.0';
	const SERVICE_VERSION = '2013-01-01';
	const MAX_ERROR_RETRY = 3;

	// Construct User agent string based off of the application_name, application_version, PHP platform
	private $userAgent = null;
	private $parameters = null;
	private $mwsEndpointPath = null;
	private $mwsEndpointUrl = null;
	private $profileEndpoint = null;
	private $config = array('merchant_id' 	   => null,
				'secret_key' 	   => null,
				'access_key' 	   => null,
				'region' 		   => null,
				'currency_code' 	   => null,
				'sandbox' 		   => false,
				'platform_id' 	   => null,
				'cabundle_file' 	   => null,
				'application_name'     => null,
				'application_version'  => null,
				'proxy_host' 	   => null,
				'proxy_port' 	   => -1,
				'proxy_username' 	   => null,
				'proxy_password' 	   => null,
				'client_id' 	   => null,
				'handle_throttle' 	   => true
				);

	private $modePath = null;

	// Final URL to where the API parameters POST done, based off the config['region'] and respective $mwsServiceUrls
	private $mwsServiceUrl = null;

	private $mwsServiceUrls = array('eu' => 'mws-eu.amazonservices.com',
					'na' => 'mws.amazonservices.com',
					'jp' => 'mws.amazonservices.jp');

	// Production profile end points to get the user information
	private $liveProfileEndpoint = array('uk' => 'https://api.amazon.co.uk',
					 'us' => 'https://api.amazon.com',
					 'de' => 'https://api.amazon.de',
					 'jp' => 'https://api.amazon.co.jp');

	// Sandbox profile end points to get the user information
	private $sandboxProfileEndpoint = array('uk' => 'https://api.sandbox.amazon.co.uk',
						'us' => 'https://api.sandbox.amazon.com',
						'de' => 'https://api.sandbox.amazon.de',
						'jp' => 'https://api.sandbox.amazon.co.jp');

	private $regionMappings = array('de' => 'eu',
					'uk' => 'eu',
					'us' => 'na',
					'jp' => 'jp');

	// Boolean variable to check if the API call was a success
	public $success = false;

	/* Takes user configuration array from the user as input
	 * Takes JSON file path with configuration information as input
	 * Validates the user configuration array against existing config array
	 */

	public function __construct($config = null)
	{
		if (!is_null($config)) {

			if (is_array($config)) {
				$configArray = $config;
			} elseif (!is_array($config)) {
		$configArray = $this->checkIfFileExists($config);
		}

		if (is_array($configArray)) {
				$this->checkConfigKeys($configArray);
			} else {
				throw new \Exception('$config is of the incorrect type ' . gettype($configArray) . ' and should be of the type array');
			}
		} else {
		throw new \Exception('$config cannot be null.');
	}
	}

	/* checkIfFileExists -  check if the JSON file exists in the path provided */

	private function checkIfFileExists($config)
	{
	if(file_exists($config))
	{
		$jsonString  = file_get_contents($config);
		$configArray = json_decode($jsonString, true);

		$jsonError = json_last_error();

		if ($jsonError != 0) {
		$errorMsg = "Error with message - content is not in json format" . $this->getErrorMessageForJsonError($jsonError) . " " . $configArray;
		throw new \Exception($errorMsg);
		}
	} else {
		$errorMsg ='$config is not a Json File path or the Json File was not found in the path provided';
		throw new \Exception($errorMsg);
	}
	return $configArray;
	}

	/* Checks if the keys of the input configuration matches the keys in the config array
	 * if they match the values are taken else throws exception
	 * strict case match is not performed
	 */

	private function checkConfigKeys($config)
	{
		$config = array_change_key_case($config, CASE_LOWER);
	$config = $this->trimArray($config);

		foreach ($config as $key => $value) {
			if (array_key_exists($key, $this->config)) {
				$this->config[$key] = $value;
			} else {
				throw new \Exception('Key ' . $key . ' is either not part of the configuration or has incorrect Key name.
				check the config array key names to match your key names of your config array', 1);
			}
		}
	}

	/* Convert a json error code to a descriptive error message
	 *
	 * @param int $jsonError message code
	 *
	 * @return string error message
	 */

	private function getErrorMessageForJsonError($jsonError)
	{
		switch ($jsonError) {
			case JSON_ERROR_DEPTH:
				return " - maximum stack depth exceeded.";
				break;
			case JSON_ERROR_STATE_MISMATCH:
				return " - invalid or malformed JSON.";
				break;
			case JSON_ERROR_CTRL_CHAR:
				return " - control character error.";
				break;
			case JSON_ERROR_SYNTAX:
				return " - syntax error.";
				break;
			default:
				return ".";
				break;
		}
	}

	/* Setter for sandbox
	 * Sets the Boolean value for config['sandbox'] variable
	 */

	public function setSandbox($value)
	{
		if (is_bool($value)) {
			$this->config['sandbox'] = $value;
		} else {
			throw new \Exception($value . ' is of type ' . gettype($value) . ' and should be a boolean value');
		}
	}

	/* Setter for config['client_id']
	 * Sets the value for config['client_id'] variable
	 */

	public function setClientId($value)
	{
		if (!empty($value)) {
			$this->config['client_id'] = $value;
		} else {
			throw new \Exception('setter value for client ID provided is empty');
		}
	}

	/* Setter for Proxy
	 * input $proxy [array]
	 * @param $proxy['proxy_user_host'] - hostname for the proxy
	 * @param $proxy['proxy_user_port'] - hostname for the proxy
	 * @param $proxy['proxy_user_name'] - if your proxy required a username
	 * @param $proxy['proxy_user_password'] - if your proxy required a password
	 */

	public function setProxy($proxy)
	{
	$proxy = $this->trimArray($proxy);

		if (!empty($proxy['proxy_user_host']))
		$this->config['proxy_user_host'] = $proxy['proxy_user_host'];

		if (!empty($proxy['proxy_user_port']))
			$this->config['proxy_user_port'] = $proxy['proxy_user_port'];

		if (!empty($proxy['proxy_user_name']))
			$this->config['proxy_user_name'] = $proxy['proxy_user_name'];

		if (!empty($proxy['proxy_user_password']))
			$this->config['proxy_user_password'] = $proxy['proxy_user_password'];
	}

	/* Setter for $mwsServiceUrl
	 * Set the URL to which the post request has to be made for unit testing
	 */

	public function setMwsServiceUrl($url)
	{
	$this->mwsServiceUrl = $url;
	}

	/* Getter
	 * Gets the value for the key if the key exists in config
	 */

	public function __get($name)
	{
		if (array_key_exists(strtolower($name), $this->config)) {
			return $this->config[strtolower($name)];
		} else {
			throw new \Exception('Key ' . $name . ' is either not a part of the configuration array config or the' . $name . 'does not match the key name in the config array', 1);
		}
	}

	/* Getter for parameters string
	 * Gets the value for the parameters string for unit testing
	 */

	public function getParameters()
	{
	return trim($this->parameters);
	}

	/* Trim the input Array key values */

	private function trimArray($array)
	{
	foreach ($array as $key => $value)
	{
		$array[$key] = trim($value);
	}
	return $array;
	}

	/* GetUserInfo convenience function - Returns user's profile information from Amazon using the access token returned by the Button widget.
	 *
	 * @see http://login.amazon.com/website Step 4
	 * @param $accessToken [String]
	 */

	public function getUserInfo($accessToken)
	{
		// Get the correct Profile Endpoint URL based off the country/region provided in the config['region']
		$this->profileEndpointUrl();

		if (empty($accessToken)) {
			throw new \InvalidArgumentException('Access Token is a required parameter and is not set');
		}

		// To make sure double encoding doesn't occur decode first and encode again.
		$accessToken = urldecode($accessToken);
		$url 	     = $this->profileEndpoint . '/auth/o2/tokeninfo?access_token=' . urlEncode($accessToken);

		$httpCurlRequest = new HttpCurl();

		$response = $httpCurlRequest->httpGet($url);
		$data 	  = json_decode($response);

		if ($data->aud != $this->config['client_id']) {
			// The access token does not belong to us
			throw new \Exception('The Access token entered is incorrect');
		}

		// Exchange the access token for user profile
		$url             = $this->profileEndpoint . '/user/profile';
		$httpCurlRequest = new HttpCurl();

		$httpCurlRequest->setAccessToken($accessToken);
		$httpCurlRequest->setHttpHeader(true);
		$response = $httpCurlRequest->httpGet($url);

		$userInfo = json_decode($response, true);
		return $userInfo;
	}

	/* setParametersAndPost - sets the parameters array with non empty values from the requestParameters array sent to API calls.
	 * If Provider Credit Details is present, values are set by setProviderCreditDetails
	 * If Provider Credit Reversal Details is present, values are set by setProviderCreditDetails
	 */

	private function setParametersAndPost($parameters, $fieldMappings, $requestParameters)
	{
	/* For loop to take all the non empty parameters in the $requestParameters and add it into the $parameters array,
	 * if the keys are matched from $requestParameters array with the $fieldMappings array
	 */
		foreach ($requestParameters as $param => $value) {

		if(!is_array($value)) {
		$value = trim($value);
		}

			if (array_key_exists($param, $fieldMappings) && $value!='') {

		if(is_array($value)) {
			// If the parameter is a provider_credit_details or provider_credit_reversal_details, call the respective functions to set the values
			if($param === 'provider_credit_details') {
			$parameters = $this->setProviderCreditDetails($parameters,$value);
			} elseif ($param === 'provider_credit_reversal_details') {
			$parameters = $this->setProviderCreditReversalDetails($parameters,$value);
			}

		} else{
			// For variables that are boolean values, strtolower them
			if($this->checkIfBool($value))
			{
			$value = strtolower($value);
			}

			$parameters[$fieldMappings[$param]] = $value;
		}
			}
		}

		$parameters = $this->setDefaultValues($parameters, $fieldMappings, $requestParameters);
	$responseObject = $this->calculateSignatureAndPost($parameters);

	return $responseObject;
	}

	/* checkIfBool - checks if the input is a boolean */

	private function checkIfBool($string)
	{
	$string = strtolower($string);
	return in_array($string, array('true', 'false'));
	}

	/* calculateSignatureAndPost - convert the Parameters array to string and curl POST the parameters to MWS */

	private function calculateSignatureAndPost($parameters)
	{
	// Call the signature and Post function to perform the actions. Returns XML in array format
		$parametersString = $this->calculateSignatureAndParametersToString($parameters);

	// POST using curl the String converted Parameters
	$response = $this->invokePost($parametersString);

	// Send this response as args to ResponseParser class which will return the object of the class.
		$responseObject = new ResponseParser($response);
		return $responseObject;
	}

	/* If merchant_id is not set via the requestParameters array then it's taken from the config array
	 *
	 * Set the platform_id if set in the config['platform_id'] array
	 *
	 * If currency_code is set in the $requestParameters and it exists in the $fieldMappings array, strtoupper it
	 * else take the value from config array if set
	 */

	private function setDefaultValues($parameters, $fieldMappings, $requestParameters)
	{
		if (empty($requestParameters['merchant_id']))
			$parameters['SellerId'] = $this->config['merchant_id'];

		if (array_key_exists('platform_id', $fieldMappings)) {
		if (empty($requestParameters['platform_id']) && !empty($this->config['platform_id']))
			$parameters[$fieldMappings['platform_id']] = $this->config['platform_id'];
	}

		if (array_key_exists('currency_code', $fieldMappings)) {
			if (!empty($requestParameters['currency_code'])) {
		$parameters[$fieldMappings['currency_code']] = strtoupper($requestParameters['currency_code']);
			} else {
				$parameters[$fieldMappings['currency_code']] = strtoupper($this->config['currency_code']);
			}
		}

		return $parameters;
	}

	/* setProviderCreditDetails - sets the provider credit details sent via the Capture or Authorize API calls
	 * @param provider_id - [String]
	 * @param credit_amount - [String]
	 * @optional currency_code - [String]
	 */

	private function setProviderCreditDetails($parameters, $providerCreditInfo)
	{
	$providerIndex = 0;
	$providerString = 'ProviderCreditList.member.';

		$fieldMappings = array(
			'provider_id'   => 'ProviderId',
			'credit_amount' => 'CreditAmount.Amount',
			'currency_code' => 'CreditAmount.CurrencyCode'
		);

	foreach ($providerCreditInfo as $key => $value)
	 {
		$value = array_change_key_case($value, CASE_LOWER);
		$providerIndex = $providerIndex + 1;

		foreach ($value as $param => $val)
		{
		if (array_key_exists($param, $fieldMappings) && trim($val)!='') {
			$parameters[$providerString.$providerIndex. '.' .$fieldMappings[$param]] = $val;
		}
		}

		// If currency code is not entered take it from the config array
		if(empty($parameters[$providerString.$providerIndex. '.' .$fieldMappings['currency_code']]))
		{
		$parameters[$providerString.$providerIndex. '.' .$fieldMappings['currency_code']] = strtoupper($this->config['currency_code']);
		}
	}

	return $parameters;
	}

	/* setProviderCreditReversalDetails - sets the reverse provider credit details sent via the Refund API call.
	 * @param provider_id - [String]
	 * @param credit_amount - [String]
	 * @optional currency_code - [String]
	 */

	private function setProviderCreditReversalDetails($parameters, $providerCreditInfo)
	{
	$providerIndex = 0;
	$providerString = 'ProviderCreditReversalList.member.';

		$fieldMappings = array(
			'provider_id' 	   	=> 'ProviderId',
			'credit_reversal_amount' 	=> 'CreditReversalAmount.Amount',
			'currency_code' 		=> 'CreditReversalAmount.CurrencyCode'
		);

	foreach ($providerCreditInfo as $key => $value)
	{
		$value = array_change_key_case($value, CASE_LOWER);
		$providerIndex = $providerIndex + 1;

		foreach ($value as $param => $val)
		{
		if (array_key_exists($param, $fieldMappings) && trim($val)!='') {
			$parameters[$providerString.$providerIndex. '.' .$fieldMappings[$param]] = $val;
		}
		}

		// If currency code is not entered take it from the config array
		if(empty($parameters[$providerString.$providerIndex. '.' .$fieldMappings['currency_code']]))
		{
		$parameters[$providerString.$providerIndex. '.' .$fieldMappings['currency_code']] = strtoupper($this->config['currency_code']);
		}
	}

	return $parameters;
	}

	/* GetOrderReferenceDetails API call - Returns details about the Order Reference object and its current state.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetOrderReferenceDetails.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_order_reference_id'] - [String]
	 * @optional requestParameters['address_consent_token'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getOrderReferenceDetails($requestParameters = array())
	{

		$parameters['Action'] = 'GetOrderReferenceDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_order_reference_id' => 'AmazonOrderReferenceId',
			'address_consent_token' 	=> 'AddressConsentToken',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);
		return ($responseObject);
	}

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

	public function setOrderReferenceDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'SetOrderReferenceDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_order_reference_id' => 'AmazonOrderReferenceId',
			'amount' 			=> 'OrderReferenceAttributes.OrderTotal.Amount',
			'currency_code' 		=> 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
			'platform_id' 		=> 'OrderReferenceAttributes.PlatformId',
			'seller_note' 		=> 'OrderReferenceAttributes.SellerNote',
			'seller_order_id' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
			'store_name' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
			'custom_information'	=> 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

	/* ConfirmOrderReferenceDetails API call - Confirms that the order reference is free of constraints and all required information has been set on the order reference.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmOrderReference.html

	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_order_reference_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function confirmOrderReference($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'ConfirmOrderReference';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_order_reference_id' => 'AmazonOrderReferenceId',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* CancelOrderReferenceDetails API call - Cancels a previously confirmed order reference.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CancelOrderReference.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_order_reference_id'] - [String]
	 * @optional requestParameters['cancelation_reason'] [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function cancelOrderReference($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'CancelOrderReference';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_order_reference_id' => 'AmazonOrderReferenceId',
			'cancelation_reason' 	=> 'CancelationReason',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

	/* CloseOrderReferenceDetails API call - Confirms that an order reference has been fulfilled (fully or partially)
	 * and that you do not expect to create any new authorizations on this order reference.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_order_reference_id'] - [String]
	 * @optional requestParameters['closure_reason'] [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function closeOrderReference($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'CloseOrderReference';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_order_reference_id' => 'AmazonOrderReferenceId',
			'closure_reason' 		=> 'ClosureReason',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* CloseAuthorization API call - Closes an authorization.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseOrderReference.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_authorization_id'] - [String]
	 * @optional requestParameters['closure_reason'] [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function closeAuthorization($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'CloseAuthorization';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_authorization_id' 	=> 'AmazonAuthorizationId',
			'closure_reason' 		=> 'ClosureReason',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function authorize($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'Authorize';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		 => 'SellerId',
			'amazon_order_reference_id'  => 'AmazonOrderReferenceId',
			'authorization_amount' 	 => 'AuthorizationAmount.Amount',
			'currency_code' 		 => 'AuthorizationAmount.CurrencyCode',
			'authorization_reference_id' => 'AuthorizationReferenceId',
			'capture_now' 		 => 'CaptureNow',
		'provider_credit_details'	 => array(),
			'seller_authorization_note'  => 'SellerAuthorizationNote',
			'transaction_timeout' 	 => 'TransactionTimeout',
			'soft_descriptor' 		 => 'SoftDescriptor',
			'mws_auth_token' 		 => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* GetAuthorizationDetails API call - Returns the status of a particular authorization and the total amount captured on the authorization.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetAuthorizationDetails.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_authorization_id'] [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getAuthorizationDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'GetAuthorizationDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_authorization_id' 	=> 'AmazonAuthorizationId',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function capture($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'Capture';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_authorization_id' 	=> 'AmazonAuthorizationId',
			'capture_amount' 		=> 'CaptureAmount.Amount',
			'currency_code' 		=> 'CaptureAmount.CurrencyCode',
			'capture_reference_id' 	=> 'CaptureReferenceId',
		'provider_credit_details'	=> array(),
			'seller_capture_note' 	=> 'SellerCaptureNote',
			'soft_descriptor' 		=> 'SoftDescriptor',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

	/* GetCaptureDetails API call - Returns the status of a particular capture and the total amount refunded on the capture.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetCaptureDetails.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_capture_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getCaptureDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'GetCaptureDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 	=> 'SellerId',
			'amazon_capture_id' => 'AmazonCaptureId',
			'mws_auth_token' 	=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function refund($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'Refund';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 	  		=> 'SellerId',
			'amazon_capture_id'   		=> 'AmazonCaptureId',
			'refund_reference_id' 		=> 'RefundReferenceId',
			'refund_amount' 	  		=> 'RefundAmount.Amount',
			'currency_code' 	  		=> 'RefundAmount.CurrencyCode',
		'provider_credit_reversal_details'	=> array(),
			'seller_refund_note'  		=> 'SellerRefundNote',
			'soft_descriptor' 	  		=> 'SoftDescriptor',
			'mws_auth_token' 	  		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* GetRefundDetails API call - Returns the status of a particular refund.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetRefundDetails.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_refund_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getRefundDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'GetRefundDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 	=> 'SellerId',
			'amazon_refund_id'  => 'AmazonRefundId',
			'mws_auth_token' 	=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function getServiceStatus($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'GetServiceStatus';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id'    => 'SellerId',
			'mws_auth_token' => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

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

	public function createOrderReferenceForId($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'CreateOrderReferenceForId';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'id' 			=> 'Id',
			'id_type' 			=> 'IdType',
			'inherit_shipping_address' 	=> 'InheritShippingAddress',
			'confirm_now' 		=> 'ConfirmNow',
			'amount' 			=> 'OrderReferenceAttributes.OrderTotal.Amount',
			'currency_code' 		=> 'OrderReferenceAttributes.OrderTotal.CurrencyCode',
			'platform_id' 		=> 'OrderReferenceAttributes.PlatformId',
			'seller_note' 		=> 'OrderReferenceAttributes.SellerNote',
			'seller_order_id' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.SellerOrderId',
			'store_name' 		=> 'OrderReferenceAttributes.SellerOrderAttributes.StoreName',
			'custom_information' 	=> 'OrderReferenceAttributes.SellerOrderAttributes.CustomInformation',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* GetBillingAgreementDetails API Call - Returns details about the Billing Agreement object and its current state.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_GetBillingAgreementDetails.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_billing_agreement_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getBillingAgreementDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'GetBillingAgreementDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  => 'SellerId',
			'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
			'address_consent_token' 	  => 'AddressConsentToken',
			'mws_auth_token' 		  => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

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

	public function setBillingAgreementDetails($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'SetBillingAgreementDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  => 'SellerId',
			'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
			'platform_id' 		  => 'BillingAgreementAttributes.PlatformId',
			'seller_note' 		  => 'BillingAgreementAttributes.SellerNote',
			'seller_billing_agreement_id' => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.SellerBillingAgreementId',
			'custom_information' 	  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.CustomInformation',
			'store_name' 		  => 'BillingAgreementAttributes.SellerBillingAgreementAttributes.StoreName',
			'mws_auth_token' 		  => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* ConfirmBillingAgreement API Call - Confirms that the Billing Agreement is free of constraints and all required information has been set on the Billing Agreement.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ConfirmBillingAgreement.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_billing_agreement_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function confirmBillingAgreement($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'ConfirmBillingAgreement';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  => 'SellerId',
			'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
			'mws_auth_token' 		  => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* ValidateBillignAgreement API Call - Validates the status of the Billing Agreement object and the payment method associated with it.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_ValidateBillingAgreement.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_billing_agreement_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function validateBillingAgreement($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'ValidateBillingAgreement';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  => 'SellerId',
			'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
			'mws_auth_token' 		  => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function authorizeOnBillingAgreement($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'AuthorizeOnBillingAgreement';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 			=> 'SellerId',
			'amazon_billing_agreement_id' 	=> 'AmazonBillingAgreementId',
			'authorization_reference_id' 	=> 'AuthorizationReferenceId',
			'authorization_amount' 		=> 'AuthorizationAmount.Amount',
			'currency_code' 			=> 'AuthorizationAmount.CurrencyCode',
			'seller_authorization_note' 	=> 'SellerAuthorizationNote',
			'transaction_timeout' 		=> 'TransactionTimeout',
			'capture_now' 			=> 'CaptureNow',
			'soft_descriptor' 			=> 'SoftDescriptor',
			'seller_note' 			=> 'SellerNote',
			'platform_id' 			=> 'PlatformId',
			'custom_information' 		=> 'SellerOrderAttributes.CustomInformation',
			'seller_order_id' 			=> 'SellerOrderAttributes.SellerOrderId',
			'store_name' 			=> 'SellerOrderAttributes.StoreName',
			'inherit_shipping_address' 		=> 'InheritShippingAddress',
			'mws_auth_token' 			=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

	return ($responseObject);
	}

	/* CloseBillingAgreement API Call - Returns details about the Billing Agreement object and its current state.
	 * @see http://docs.developer.amazonservices.com/en_US/off_amazon_payments/OffAmazonPayments_CloseBillingAgreement.html
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_billing_agreement_id'] - [String]
	 * @optional requestParameters['closure_reason'] [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function closeBillingAgreement($requestParameters = array())
	{
		$parameters           = array();
		$parameters['Action'] = 'CloseBillingAgreement';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  => 'SellerId',
			'amazon_billing_agreement_id' => 'AmazonBillingAgreementId',
			'closure_reason' 		  => 'ClosureReason',
			'mws_auth_token' 		  => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function charge($requestParameters = array()) {

	$requestParameters = array_change_key_case($requestParameters, CASE_LOWER);
	$requestParameters= $this->trimArray($requestParameters);

	$setParameters = $authorizeParameters = $confirmParameters = $requestParameters;

		$chargeType = '';

	if (!empty($requestParameters['amazon_order_reference_id']))
	{
		$chargeType = 'OrderReference';

	} elseif(!empty($requestParameters['amazon_billing_agreement_id'])) {
		$chargeType = 'BillingAgreement';

	} elseif (!empty($requestParameters['amazon_reference_id'])) {
			switch (substr(strtoupper($requestParameters['amazon_reference_id']), 0, 1)) {
				case 'P':
				case 'S':
					$chargeType = 'OrderReference';
					$setParameters['amazon_order_reference_id'] = $requestParameters['amazon_reference_id'];
					$authorizeParameters['amazon_order_reference_id'] = $requestParameters['amazon_reference_id'];
					$confirmParameters['amazon_order_reference_id'] = $requestParameters['amazon_reference_id'];
					break;
				case 'B':
				case 'C':
					$chargeType = 'BillingAgreement';
					$setParameters['amazon_billing_agreement_id'] = $requestParameters['amazon_reference_id'];
					$authorizeParameters['amazon_billing_agreement_id'] = $requestParameters['amazon_reference_id'];
					$confirmParameters['amazon_billing_agreement_id'] = $requestParameters['amazon_reference_id'];
					break;
				default:
					throw new \Exception('Invalid Amazon Reference ID');
			}
		} else {
			throw new \Exception('key amazon_order_reference_id or amazon_billing_agreement_id is null and is a required parameter');
		}

	// Set the other parameters if the values are present
		$setParameters['amount'] = !empty($requestParameters['charge_amount']) ? $requestParameters['charge_amount'] : '';
		$authorizeParameters['authorization_amount'] = !empty($requestParameters['charge_amount']) ? $requestParameters['charge_amount'] : '';

		$setParameters['seller_note'] = !empty($requestParameters['charge_note']) ? $requestParameters['charge_note'] : '';
		$authorizeParameters['seller_authorization_note'] = !empty($requestParameters['charge_note']) ? $requestParameters['charge_note'] : '';
		$authorizeParameters['seller_note'] = !empty($requestParameters['charge_note']) ? $requestParameters['charge_note'] : '';

		$setParameters['seller_order_id'] = !empty($requestParameters['charge_order_id']) ? $requestParameters['charge_order_id'] : '';
		$setParameters['seller_billing_agreement_id'] = !empty($requestParameters['charge_order_id']) ? $requestParameters['charge_order_id'] : '';
		$authorizeParameters['seller_order_id'] = !empty($requestParameters['charge_order_id']) ? $requestParameters['charge_order_id'] : '';

		$authorizeParameters['capture_now'] = 'true';

	$response = $this->makeChargeCalls($chargeType, $setParameters, $confirmParameters, $authorizeParameters);
	return $response;
	}

	/* makeChargeCalls - makes API calls based off the charge type (OrderReference or BillingAgreement) */

	private function makeChargeCalls($chargeType, $setParameters, $confirmParameters, $authorizeParameters)
	{
	switch ($chargeType) {
			case 'OrderReference':
				$response = $this->setOrderReferenceDetails($setParameters);
				if ($this->success) {
					$this->confirmOrderReference($confirmParameters);
				}
				if ($this->success) {
					$response = $this->Authorize($authorizeParameters);
				}
				return $response;
			case 'BillingAgreement':
				// Get the Billing Agreement details and feed the response object to the ResponseParser
				$responseObj = $this->getBillingAgreementDetails($setParameters);
				// Call the function GetBillingAgreementDetailsStatus in ResponseParser.php providing it the XML response
				// $baStatus is an aray containing the State of the Billing Agreement
				$baStatus = $responseObj->getBillingAgreementDetailsStatus($responseObj->toXml());
				if ($baStatus['State'] != 'Open') {
					$response = $this->SetBillingAgreementDetails($setParameters);
					if ($this->success) {
						$response = $this->ConfirmBillingAgreement($confirmParameters);
					}
				}
				// Check the Billing Agreement status again before making the Authorization.
				$responseObj = $this->getBillingAgreementDetails($setParameters);
				$baStatus = $responseObj->GetBillingAgreementDetailsStatus($responseObj->toXml());
				if ($this->success && $baStatus['State'] === 'Open') {
					$response = $this->AuthorizeOnBillingAgreement($authorizeParameters);
				}
			return $response;
		}
	}

	/* GetProviderCreditDetails API Call - Get the details of the Provider Credit.
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_provider_credit_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getProviderCreditDetails($requestParameters = array())
	{
	$parameters           = array();
		$parameters['Action'] = 'GetProviderCreditDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		=> 'SellerId',
			'amazon_provider_credit_id' => 'AmazonProviderCreditId',
			'mws_auth_token' 		=> 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* GetProviderCreditReversalDetails API Call - Get details of the Provider Credit Reversal.
	 *
	 * @param requestParameters['merchant_id'] - [String]
	 * @param requestParameters['amazon_provider_credit_reversal_id'] - [String]
	 * @optional requestParameters['mws_auth_token'] - [String]
	 */

	public function getProviderCreditReversalDetails($requestParameters = array())
	{
	$parameters           = array();
		$parameters['Action'] = 'GetProviderCreditReversalDetails';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		  	 => 'SellerId',
			'amazon_provider_credit_reversal_id' => 'AmazonProviderCreditReversalId',
			'mws_auth_token' 		  	 => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

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

	public function reverseProviderCredit($requestParameters = array())
	{
	$parameters           = array();
		$parameters['Action'] = 'ReverseProviderCredit';
		$requestParameters    = array_change_key_case($requestParameters, CASE_LOWER);

		$fieldMappings = array(
			'merchant_id' 		   => 'SellerId',
			'amazon_provider_credit_id'    => 'AmazonProviderCreditId',
		'credit_reversal_reference_id' => 'CreditReversalReferenceId',
		'credit_reversal_amount' 	   => 'CreditReversalAmount.Amount',
		'currency_code' 		   => 'CreditReversalAmount.CurrencyCode',
		'credit_reversal_note' 	   => 'CreditReversalNote',
			'mws_auth_token' 		   => 'MWSAuthToken'
		);

		$responseObject = $this->setParametersAndPost($parameters, $fieldMappings, $requestParameters);

		return ($responseObject);
	}

	/* Create an Array of required parameters, sort them
	 * Calculate signature and invoke the POST to the MWS Service URL
	 *
	 * @param AWSAccessKeyId [String]
	 * @param Version [String]
	 * @param SignatureMethod [String]
	 * @param Timestamp [String]
	 * @param Signature [String]
	 */

	private function calculateSignatureAndParametersToString($parameters = array())
	{
		$parameters['AWSAccessKeyId']   = $this->config['access_key'];
		$parameters['Version']          = self::SERVICE_VERSION;
		$parameters['SignatureMethod']  = 'HmacSHA256';
		$parameters['SignatureVersion'] = 2;
		$parameters['Timestamp']        = $this->getFormattedTimestamp();
		uksort($parameters, 'strcmp');

		$this->createServiceUrl();

		$parameters['Signature'] = $this->signParameters($parameters);
		$parameters              = $this->getParametersAsString($parameters);

	// Save these parameters in the parameters variable so that it can be returned for unit testing.
	$this->parameters 	 = $parameters;
		return $parameters;
	}

	/* Computes RFC 2104-compliant HMAC signature for request parameters
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

	private function signParameters(array $parameters)
	{
		$signatureVersion = $parameters['SignatureVersion'];
		$algorithm        = "HmacSHA1";
		$stringToSign     = null;
		if (2 === $signatureVersion) {
			$algorithm                     = "HmacSHA256";
			$parameters['SignatureMethod'] = $algorithm;
			$stringToSign                  = $this->calculateStringToSignV2($parameters);
		} else {
			throw new \Exception("Invalid Signature Version specified");
		}

		return $this->sign($stringToSign, $algorithm);
	}

	/* Calculate String to Sign for SignatureVersion 2
	 * @param array $parameters request parameters
	 * @return String to Sign
	 */

	private function calculateStringToSignV2(array $parameters)
	{
		$data = 'POST';
		$data .= "\n";
		$data .= $this->mwsEndpointUrl;
		$data .= "\n";
		$data .= $this->mwsEndpointPath;
		$data .= "\n";
		$data .= $this->getParametersAsString($parameters);
		return $data;
	}

	/* Convert paremeters to Url encoded query string */

	private function getParametersAsString(array $parameters)
	{
		$queryParameters = array();
		foreach ($parameters as $key => $value) {
			$queryParameters[] = $key . '=' . $this->urlEncode($value);
		}

		return implode('&', $queryParameters);
	}

	private function urlEncode($value)
	{
		return str_replace('%7E', '~', rawurlencode($value));
	}

	/* Computes RFC 2104-compliant HMAC signature */

	private function sign($data, $algorithm)
	{
		if ($algorithm === 'HmacSHA1') {
			$hash = 'sha1';
		} else if ($algorithm === 'HmacSHA256') {
			$hash = 'sha256';
		} else {
			throw new \Exception("Non-supported signing method specified");
		}

		return base64_encode(hash_hmac($hash, $data, $this->config['secret_key'], true));
	}

	/* Formats date as ISO 8601 timestamp */

	private function getFormattedTimestamp()
	{
		return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
	}

	/* invokePost takes the parameters and invokes the httpPost function to POST the parameters
	 * Exponential retries on error 500 and 503
	 * The response from the POST is an XML which is converted to Array
	 */

	private function invokePost($parameters)
	{
		$response       = array();
		$statusCode     = 200;
		$this->success = false;

	// Submit the request and read response body
	try {
			$shouldRetry = true;
			$retries     = 0;
			do {
				try {
					$this->constructUserAgentHeader();

					$httpCurlRequest = new HttpCurl($this->config);
			$response = $httpCurlRequest->httpPost($this->mwsServiceUrl, $this->userAgent, $parameters);

			// Split the API response into Response Body and the other parts of the response into other
					list($other, $responseBody) = explode("\r\n\r\n", $response, 2);
					$other = preg_split("/\r\n|\n|\r/", $other);

					list($protocol, $code, $text) = explode(' ', trim(array_shift($other)), 3);
					$response = array(
						'Status' => (int) $code,
						'ResponseBody' => $responseBody
					);

			$statusCode = $response['Status'];

			if ($statusCode == 200) {
						$shouldRetry    = false;
						$this->success = true;
					} elseif ($statusCode == 500 || $statusCode == 503) {

			$shouldRetry = true;
						if ($shouldRetry && strtolower($this->config['handle_throttle'])) {
							$this->pauseOnRetry(++$retries, $statusCode);
						}
					} else {
						$shouldRetry = false;
					}
				} catch (\Exception $e) {
					throw $e;
				}
			} while ($shouldRetry);
		} catch (\Exception $se) {
			throw $se;
		}

		return $response;
	}

	/* Exponential sleep on failed request
	 * @param retries current retry
	 * @throws Exception if maximum number of retries has been reached
	 */

	private function pauseOnRetry($retries, $status)
	{
		if ($retries <= self::MAX_ERROR_RETRY) {
			$delay = (int) (pow(4, $retries) * 100000);
			usleep($delay);
		} else {
			throw new \Exception('Error Code: '. $status.PHP_EOL.'Maximum number of retry attempts - '. $retries .' reached');
		}
	}

	/* Create MWS service URL and the Endpoint path */

	private function createServiceUrl()
	{
		$this->modePath = strtolower($this->config['sandbox']) ? 'OffAmazonPayments_Sandbox' : 'OffAmazonPayments';

		if (!empty($this->config['region'])) {
			$region = strtolower($this->config['region']);
			if (array_key_exists($region, $this->regionMappings)) {
				$this->mwsEndpointUrl  = $this->mwsServiceUrls[$this->regionMappings[$region]];
				$this->mwsServiceUrl   = 'https://' . $this->mwsEndpointUrl . '/' . $this->modePath . '/' . self::SERVICE_VERSION;
				$this->mwsEndpointPath = '/' . $this->modePath . '/' . self::SERVICE_VERSION;
			} else {
				throw new \Exception($region . ' is not a valid region');
			}
		} else {
			throw new \Exception("config['region'] is a required parameter and is not set");
		}
	}

	/* Based on the config['region'] and config['sandbox'] values get the user profile URL */

	private function profileEndpointUrl()
	{
		if (!empty($this->config['region'])) {
			$region = strtolower($this->config['region']);

		if (array_key_exists($region, $this->sandboxProfileEndpoint) && $this->config['sandbox'] ) {
				$this->profileEndpoint = $this->sandboxProfileEndpoint[$region];
		} elseif (array_key_exists($region, $this->liveProfileEndpoint)) {
		$this->profileEndpoint = $this->liveProfileEndpoint[$region];
		} else{
		throw new \Exception($region . ' is not a valid region');
		}
	} else {
			throw new \Exception("config['region'] is a required parameter and is not set");
		}
	}

	/* Create the User Agent Header sent with the POST request */

	private function constructUserAgentHeader()
	{
		$this->userAgent = $this->quoteApplicationName($this->config['application_name']) . '/' . $this->quoteApplicationVersion($this->config['application_version']);
		$this->userAgent .= ' (';
		$this->userAgent .= 'Language=PHP/' . phpversion();
		$this->userAgent .= '; ';
		$this->userAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
		$this->userAgent .= '; ';
		$this->userAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;
		$this->userAgent .= ')';
	}

	/* Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
	 * and '/' characters from a string.
	 * @param $s
	 * @return string
	 */

	private function quoteApplicationName($s)
	{
		$quotedString = preg_replace('/ {2,}|\s/', ' ', $s);
		$quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
		$quotedString = preg_replace('/\//', '\\/', $quotedString);
		return $quotedString;
	}

	/* Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
	 * and '(' characters from a string.
	 *
	 * @param $s
	 * @return string
	 */

	private function quoteApplicationVersion($s)
	{
		$quotedString = preg_replace('/ {2,}|\s/', ' ', $s);
		$quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
		$quotedString = preg_replace('/\\(/', '\\(', $quotedString);
		return $quotedString;
	}
}
