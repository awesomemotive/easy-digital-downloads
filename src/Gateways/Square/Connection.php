<?php
/**
 * Square Connection Handler
 *
 * Handles OAuth connection flow for Square integration
 *
 * @package     EDD\Gateways\Square
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square;

use EDD\Gateways\Square\Helpers\Api;
use EDD\Gateways\Square\Helpers\Mode;
use EDD\Gateways\Square\Helpers\Setting;

/**
 * Square Connection Class
 *
 * @since 3.4.0
 */
class Connection {
	/**
	 * Constructor
	 *
	 * @since 3.4.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_edd_square_disconnect', array( $this, 'handle_disconnect' ) );
	}

	/**
	 * Handle OAuth redirect with tokens in query string
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function handle_oauth_redirect() {
		// Check if we're on the EDD settings page with Square tokens.
		if ( ! is_admin() ) {
			return;
		}

		// Check if we have the square_tokens parameter.
		if ( ! isset( $_GET['square_tokens'] ) ) {
			return;
		}

		// Verify user has admin permissions.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_die( __( 'Insufficient permissions', 'easy-digital-downloads' ) );
		}

		try {
			// Process token data.
			$this->process_oauth_tokens( $_GET['square_tokens'] );

			// Clean redirect.
			$this->clean_redirect();

		} catch ( \Exception $e ) {
			$this->handle_oauth_error( $e );
		}
	}

	/**
	 * Process OAuth tokens from encoded payload
	 *
	 * @since 3.4.0
	 * @param string $encoded_tokens Base64 encoded token payload.
	 * @throws \Exception If token data is invalid.
	 */
	private function process_oauth_tokens( $encoded_tokens ) {
		// Decode and validate.
		$token_data = $this->decode_and_validate_tokens( $encoded_tokens );

		// Map proxy mode back to internal storage mode.
		// The proxy accepts 'test' or 'live', but we store as 'sandbox' or 'live'.

		// Store tokens.
		$this->store_tokens(
			$token_data['access_token'],
			$token_data['refresh_token'],
			$token_data['expires_at']
		);

		// Store client ID from proxy.
		if ( ! empty( $token_data['client_id'] ) ) {
			Setting::set( 'client_id', $token_data['client_id'] );
		}

		// Fetch merchant info.
		$merchant_info = $this->get_merchant_info();

		if ( $merchant_info ) {
			$this->store_merchant_info( $merchant_info );
		}

		// Add success notice.
		$this->add_success_notice( $merchant_info );
	}

	/**
	 * Decode and validate token payload
	 *
	 * @since 3.4.0
	 * @param string $encoded_tokens Base64 encoded tokens.
	 * @return array Validated token data
	 * @throws \Exception If validation fails.
	 */
	private function decode_and_validate_tokens( $encoded_tokens ) {
		// Sanitize input.
		$encoded_tokens = sanitize_text_field( $encoded_tokens );

		// Decode.
		$token_json = base64_decode( $encoded_tokens );
		$token_data = json_decode( $token_json, true );

		if ( ! $token_data || ! isset( $token_data['access_token'] ) ) {
			throw new \Exception( __( 'Invalid token data', 'easy-digital-downloads' ) );
		}

		// Validate required fields.
		$required_fields = array( 'access_token', 'refresh_token', 'expires_at', 'mode', 'client_id' );
		foreach ( $required_fields as $field ) {
			if ( empty( $token_data[ $field ] ) ) {
				/* translators: %s is the name of the required field. */
				throw new \Exception( sprintf( __( 'Missing required field: %s', 'easy-digital-downloads' ), $field ) );
			}
		}

		// Validate timestamp (5-minute expiry).
		$timestamp = isset( $token_data['timestamp'] ) ? $token_data['timestamp'] : 0;
		if ( time() - $timestamp > 300 ) {
			throw new \Exception( __( 'Token data is too old', 'easy-digital-downloads' ) );
		}

		return $token_data;
	}

	/**
	 * Store OAuth tokens securely.
	 *
	 * @since 3.4.0
	 * @param string $access_token Access token.
	 * @param string $refresh_token Refresh token.
	 * @param string $expires_at Expiration timestamp.
	 */
	private function store_tokens( $access_token, $refresh_token, $expires_at ) {
		Setting::set( 'access_token', $access_token );
		Setting::set( 'refresh_token', $refresh_token );
		Setting::set( 'expires_at', $expires_at );
	}

	/**
	 * Get merchant information from Square API.
	 *
	 * @since 3.4.0
	 *
	 * @return array|false Merchant info or false on failure.
	 */
	private function get_merchant_info() {
		try {
			$merchant_info = Api::client()->getMerchantsApi()->retrieveMerchant( 'me' );

			if ( $merchant_info->isSuccess() ) {
				$merchant = $merchant_info->getResult()->getMerchant();

				// Get the locations for the merchant.
				$locations      = array();
				$locations_list = Api::client()->getLocationsApi()->listLocations();
				if ( $locations_list->isSuccess() ) {
					foreach ( $locations_list->getResult()->getLocations() as $location ) {
						$locations[ $location->getId() ] = $location->getName();
					}
				}

				return array(
					'id'               => $merchant->getId(),
					'business_name'    => $merchant->getBusinessName(),
					'country'          => $merchant->getCountry(),
					'currency'         => $merchant->getCurrency(),
					'main_location_id' => $merchant->getMainLocationId(),
					'locations'        => $locations,
				);
			}
		} catch ( \Exception $e ) {
			error_log( 'EDD Square: Failed to fetch merchant info: ' . $e->getMessage() );
		}

		return false;
	}

	/**
	 * Store merchant information.
	 *
	 * @since 3.4.0
	 *
	 * @param array $merchant_info Merchant information.
	 */
	private function store_merchant_info( $merchant_info ) {
		$mode = Mode::get();
		Setting::set( 'merchant_id', $merchant_info['id'] );
		Setting::set( 'business_name', $merchant_info['business_name'] );
		Setting::set( 'country', $merchant_info['country'] );
		Setting::set( 'currency', $merchant_info['currency'] );

		// Store application ID if provided in merchant info.
		if ( ! empty( $merchant_info['application_id'] ) ) {
			Setting::set( 'application_id', $merchant_info['application_id'] );
		}

		Setting::set( 'locations', $merchant_info['locations'] );

		// Set the default location ID.
		edd_update_option( "square_{$mode}_location_id", $merchant_info['main_location_id'] );
	}

	/**
	 * Add success notice.
	 *
	 * @since 3.4.0
	 *
	 * @param array|null $merchant_info Merchant information.
	 */
	private function add_success_notice( $merchant_info ) {
		$business_name = $merchant_info ? $merchant_info['business_name'] : __( 'Square Account', 'easy-digital-downloads' );
		$message       = sprintf(
			/* translators: 1: Business name, 2: Connection mode (live/sandbox). */
			__( 'Square Connected! Successfully connected to %1$s in %2$s mode.', 'easy-digital-downloads' ),
			$business_name,
			Mode::get()
		);

		add_action(
			'admin_notices',
			function () use ( $message ) {
				echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		);
	}

	/**
	 * Clean redirect to remove sensitive parameters.
	 *
	 * @since 3.4.0
	 */
	private function clean_redirect() {
		// Redirect to the Square gateway settings page.
		wp_safe_redirect(
			edd_get_admin_url(
				array(
					'page'    => 'edd-settings',
					'tab'     => 'gateways',
					'section' => 'square',
				)
			)
		);
		exit;
	}

	/**
	 * Handle OAuth error.
	 *
	 * @since 3.4.0
	 * @param \Exception $e Exception object.
	 */
	private function handle_oauth_error( \Exception $e ) {
		$message = sprintf(
			/* translators: 1: Error message. */
			__( 'Square Connection Failed: %s', 'easy-digital-downloads' ),
			$e->getMessage()
		);

		add_action(
			'admin_notices',
			function () use ( $message ) {
				echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
			}
		);

		error_log( 'EDD Square OAuth Error: ' . $e->getMessage() );

		$this->clean_redirect();
	}

	/**
	 * Handle disconnect request.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public function handle_disconnect() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error( __( 'Insufficient permissions', 'easy-digital-downloads' ) );
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_square_admin_nonce' ) ) {
			wp_send_json_error( __( 'Invalid nonce', 'easy-digital-downloads' ) );
		}

		// Clear stored tokens and merchant info.
		Setting::delete( 'access_token' );
		Setting::delete( 'refresh_token' );
		Setting::delete( 'expires_at' );
		Setting::delete( 'merchant_id' );
		Setting::delete( 'business_name' );
		Setting::delete( 'country' );
		Setting::delete( 'currency' );
		Setting::delete( 'application_id' );
		Setting::delete( 'location_id' );
		Setting::delete( 'locations' );
		Setting::delete( 'client_id' );
		Setting::delete( 'webhook_subscription_id' );
		Setting::delete( 'webhook_signature_key' );

		// Remove Square from the enabled gateways.
		$gateways = edd_get_option( 'gateways', array() );
		unset( $gateways['square'] );
		edd_update_option( 'gateways', $gateways );

		wp_send_json_success(
			array(
				'message' => __( 'Successfully disconnected from Square', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Check if Square is connected for the current mode.
	 *
	 * @since 3.4.0
	 * @return bool True if connected, false otherwise.
	 */
	public static function is_connected() {
		return ! empty( Api::get_access_token() );
	}

	/**
	 * Initiate Square OAuth connection.
	 *
	 * @since 3.4.0
	 * @param string $mode Connection mode (test/sandbox or live).
	 * @return string|\WP_Error OAuth URL or error.
	 */
	public static function initiate_connection( $mode ) {
		// Validate mode.
		if ( ! in_array( $mode, array( 'test', 'sandbox', 'live' ), true ) ) {
			return new \WP_Error( 'invalid_mode', __( 'Invalid connection mode', 'easy-digital-downloads' ) );
		}

		// Get the authorization URL (using proxy, no credentials needed).
		$connection = new self();
		$oauth_url  = $connection->get_authorization_url();

		return $oauth_url;
	}

	/**
	 * Get OAuth authorization URL using EDD proxy
	 *
	 * @since 3.4.0
	 * @return string Authorization URL
	 */
	public function get_authorization_url() {
		// Map EDD test mode to proxy expected values.
		$mode = edd_is_test_mode() ? 'test' : 'live';

		// Use the EDD proxy for OAuth instead of direct Square OAuth.
		$proxy_url = 'https://connect.easydigitaldownloads.com/v2/square/connect';

		$params = array(
			'mode'         => $mode,
			'redirect_uri' => edd_get_admin_url(
				array(
					'page'    => 'edd-settings',
					'tab'     => 'gateways',
					'section' => 'square',
				)
			),
			'site_url'     => site_url(),
		);

		return add_query_arg( $params, $proxy_url );
	}

	/**
	 * Refresh token if it's expired
	 *
	 * @since 3.4.0
	 * @param string $refresh_token The refresh token.
	 * @return array|null Response data or null on failure.
	 */
	public static function refresh_token( $refresh_token ) {
		// Use the EDD proxy for OAuth instead of direct Square OAuth.
		$proxy_url = 'https://connect.easydigitaldownloads.com/v2/square/refresh';

		$mode = edd_is_test_mode() ? 'test' : 'live';

		$params = array(
			'mode'          => $mode,
			'refresh_token' => $refresh_token,
			'site_url'      => site_url(),
		);

		$response = wp_remote_post(
			$proxy_url,
			array(
				'timeout'     => 15,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array(
					'Content-Type' => 'application/json',
				),
				'body'        => wp_json_encode( $params ),
				'cookies'     => array(),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'EDD Square: Failed to refresh token: ' . $response->get_error_message() );
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			error_log( 'EDD Square: Failed to refresh token, HTTP ' . $code );
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data || ! isset( $data['access_token'] ) ) {
			error_log( 'EDD Square: Failed to refresh token, invalid response' );
			return null;
		}

		return $data;
	}

	/**
	 * Check if the access token is expired.
	 *
	 * @since 3.4.0
	 * @return bool True if token is expired, false otherwise.
	 */
	public static function is_token_expired() {
		$expires_at = Setting::get( 'expires_at' );

		if ( empty( $expires_at ) ) {
			return false;
		}

		// Add a 5-minute buffer to account for network latency.
		return time() + 300 > strtotime( $expires_at );
	}

	/**
	 * Refresh the access token if it's expired.
	 *
	 * @since 3.4.0
	 * @return bool True if token was refreshed, false otherwise.
	 */
	public static function refresh_access_token() {
		$refresh_token = Setting::get( 'refresh_token' );

		if ( empty( $refresh_token ) ) {
			return false;
		}

		$response = self::refresh_token( $refresh_token );

		if ( $response && isset( $response['access_token'] ) ) {
			// Update tokens.
			Setting::set( 'access_token', $response['access_token'] );
			Setting::set( 'refresh_token', $response['refresh_token'] );
			Setting::set( 'expires_at', $response['expires_at'] );
			// Store client ID if provided in refresh response.
			if ( ! empty( $response['client_id'] ) ) {
				Setting::set( 'client_id', $response['client_id'] );
			}
			return true;
		}

		return false;
	}

	/**
	 * Get connection status details.
	 *
	 * @since 3.4.0
	 * @return array Connection status data.
	 */
	public static function get_connection_status() {
		$access_token  = Api::get_access_token();
		$business_name = Setting::get( 'business_name' );

		return array(
			'connected'     => ! empty( $access_token ),
			'mode'          => Mode::get(),
			'merchant_name' => $business_name,
		);
	}

	/**
	 * Handle proxy callback
	 *
	 * @since 3.4.0
	 * @param array $data Callback data.
	 * @return bool True if successful, false otherwise.
	 */
	public static function handle_callback( $data ) {
		if ( empty( $data['access_token'] ) || empty( $data['refresh_token'] ) || empty( $data['expires_at'] ) ) {
			return false;
		}

		Setting::set( 'access_token', $data['access_token'] );
		Setting::set( 'refresh_token', $data['refresh_token'] );
		Setting::set( 'expires_at', $data['expires_at'] );

		// Store client ID if provided in callback data.
		if ( ! empty( $data['client_id'] ) ) {
			Setting::set( 'client_id', $data['client_id'] );
		}

		return true;
	}

	/**
	 * Disconnect from Square.
	 *
	 * @since 3.4.0
	 * @return bool True if disconnected, false otherwise.
	 */
	public static function disconnect() {
		Setting::delete( 'access_token' );
		Setting::delete( 'refresh_token' );
		Setting::delete( 'expires_at' );
		Setting::delete( 'merchant_id' );
		Setting::delete( 'business_name' );
		Setting::delete( 'country' );
		Setting::delete( 'currency' );
		Setting::delete( 'application_id' );
		Setting::delete( 'location_id' );
		Setting::delete( 'locations' );
		Setting::delete( 'client_id' );
		return true;
	}
}
