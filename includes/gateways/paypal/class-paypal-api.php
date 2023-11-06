<?php
/**
 * PayPal REST API Wrapper
 *
 * @package    easy-digital-downloads
 * @subpackage Gateways\PayPal
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    GPL2+
 * @since      2.11
 */

namespace EDD\Gateways\PayPal;

use EDD\Gateways\PayPal\Exceptions\API_Exception;
use EDD\Gateways\PayPal\Exceptions\Authentication_Exception;

/**
 * Class API
 *
 * @property string $mode
 * @property string $api_url
 * @property string $client_id
 * @property string $client_secret
 * @property string $cache_key
 * @property string $token_cache_key
 * @property int    $last_response_code
 *
 * @package EDD\PayPal
 */
class API {

	const MODE_SANDBOX = 'sandbox';
	const MODE_LIVE = 'live';

	/**
	 * Mode to use for API requests
	 *
	 * @var string
	 */
	private $mode;

	/**
	 * Base API URL
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * Client ID
	 *
	 * @var string
	 */
	private $client_id;

	/**
	 * Client secret
	 *
	 * @var string
	 */
	private $client_secret;

	/**
	 * Cache key
	 *
	 * @var string
	 */
	private $cache_key;

	/**
	 * Cache key to use for the token.
	 *
	 * @var string
	 */
	private $token_cache_key;

	/**
	 * Response code from the last API request.
	 *
	 * @var int
	 */
	private $last_response_code;

	/**
	 * API constructor.
	 *
	 * @param string $mode        Mode to connect in. Either `sandbox` or `live`.
	 * @param array  $credentials Optional. Credentials to use for the connection. If omitted, saved store
	 *                            credentials are used.
	 *
	 * @throws Authentication_Exception
	 */
	public function __construct( $mode = '', $credentials = array() ) {
		// If mode is not provided, use the current store mode.
		if ( empty( $mode ) ) {
			$mode = edd_is_test_mode() ? self::MODE_SANDBOX : self::MODE_LIVE;
		}

		$this->mode = $mode;

		if ( self::MODE_SANDBOX === $mode ) {
			$this->api_url = 'https://api-m.sandbox.paypal.com';
		} else {
			$this->api_url = 'https://api-m.paypal.com';
		}

		if ( empty( $credentials ) ) {
			$credentials = array(
				'client_id'     => edd_get_option( 'paypal_' . $this->mode . '_client_id' ),
				'client_secret' => edd_get_option( 'paypal_' . $this->mode . '_client_secret' ),
			);
		}

		$this->set_credentials( $credentials );
	}

	/**
	 * Magic getter
	 *
	 * @param string $property
	 *
	 * @since 2.10
	 * @return mixed
	 */
	public function __get( $property ) {
		return isset( $this->{$property} ) ? $this->{$property} : null;
	}

	/**
	 * Sets the credentials to use for API requests.
	 *
	 * @param array $creds         {
	 *                             Credentials to set.
	 *
	 * @type string $client_id     PayPal client ID.
	 * @type string $client_secret PayPal client secret.
	 * @type string $cache_key     Cache key used for storing the access token until it expires. Should be unique to
	 *                             the set of credentials. The mode is automatically appended, so should not be
	 *                             included manually.
	 * }
	 *
	 * @since 2.11
	 * @throws Authentication_Exception
	 */
	public function set_credentials( $creds ) {
		$creds = wp_parse_args( $creds, array(
			'client_id'     => '',
			'client_secret' => '',
			'cache_key'     => 'edd_paypal_commerce_access_token'
		) );

		$required_creds = array( 'client_id', 'client_secret', 'cache_key' );

		foreach ( $required_creds as $cred_id ) {
			if ( empty( $creds[ $cred_id ] ) ) {
				throw new Authentication_Exception( sprintf(
				/* Translators: %s - The ID of the PayPal credential */
					__( 'Missing PayPal credential: %s', 'easy-digital-downloads' ),
					$cred_id
				) );
			}
		}

		foreach ( $creds as $cred_id => $cred_value ) {
			$this->{$cred_id} = $cred_value;
		}

		$this->token_cache_key = sanitize_key( $creds['cache_key'] . '_' . $this->mode );
	}

	/**
	 * Retrieves the access token. This checks cache first, and if the cached token isn't valid then
	 * a new one is generated from the API.
	 *
	 * @since 2.11
	 * @return Token
	 * @throws API_Exception
	 */
	public function get_access_token() {
		try {
			$token = Token::from_json( (string) get_option( $this->token_cache_key ) );

			return ! $token->is_expired() ? $token : $this->generate_access_token();
		} catch ( \RuntimeException $e ) {
			return $this->generate_access_token();
		}
	}

	/**
	 * Generates a new access token and caches it.
	 *
	 * @since 2.11
	 * @return Token
	 * @throws API_Exception
	 */
	private function generate_access_token() {
		$response = wp_remote_post( $this->api_url . '/v1/oauth2/token', array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Authorization' => sprintf( 'Basic %s', base64_encode( sprintf( '%s:%s', $this->client_id, $this->client_secret ) ) ),
				'timeout'       => 15
			),
			'body'    => array(
				'grant_type' => 'client_credentials'
			),
			'user-agent' => 'Easy Digital Downloads/' . EDD_VERSION . '; ' . get_bloginfo( 'name' ),
		) );

		$body = json_decode( wp_remote_retrieve_body( $response ) );
		$code = intval( wp_remote_retrieve_response_code( $response ) );

		if ( is_wp_error( $response ) ) {
			throw new API_Exception( $response->get_error_message(), $code );
		}

		if ( ! empty( $body->error_description ) ) {
			throw new API_Exception( $body->error_description, $code );
		}

		if ( 200 !== $code ) {
			throw new API_Exception( sprintf(
			/* Translators: %d - HTTP response code. */
				__( 'Unexpected response code: %d', 'easy-digital-downloads' ),
				$code
			), $code );
		}

		$token = new Token( $body );

		update_option( $this->token_cache_key, $token->to_json() );

		return $token;
	}

	/**
	 * Makes an API request.
	 *
	 * @param string $endpoint API endpoint.
	 * @param array  $body     Array of data to send in the request.
	 * @param array  $headers  Array of headers.
	 * @param string $method   HTTP method.
	 *
	 * @since 2.11
	 * @return mixed
	 * @throws API_Exception
	 */
	public function make_request( $endpoint, $body = array(), $headers = array(), $method = 'POST' ) {
		$headers = wp_parse_args( $headers, array(
			'Content-Type'                  => 'application/json',
			'Authorization'                 => sprintf( 'Bearer %s', $this->get_access_token()->token() ),
			'PayPal-Partner-Attribution-Id' => EDD_PAYPAL_PARTNER_ATTRIBUTION_ID
		) );

		$request_args = array(
			'method'     => $method,
			'timeout'    => 15,
			'headers'    => $headers,
			'user-agent' => 'Easy Digital Downloads/' . EDD_VERSION . '; ' . get_bloginfo( 'name' ),
		);

		if ( ! empty( $body ) ) {
			$request_args['body'] = json_encode( $body );
		}

		// In a few rare cases, we may be providing a full URL to `$endpoint` instead of just the path.
		$api_url = ( 'https://' === substr( $endpoint, 0, 8 ) ) ? $endpoint : $this->api_url . '/' . $endpoint;

		$response = wp_remote_request( $api_url, $request_args );

		if ( is_wp_error( $response ) ) {
			throw new API_Exception( $response->get_error_message() );
		}

		$this->last_response_code = intval( wp_remote_retrieve_response_code( $response ) );

		return json_decode( wp_remote_retrieve_body( $response ) );
	}

}
