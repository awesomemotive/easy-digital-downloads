<?php
/**
 * class-paypal-api.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\PayPal;

class API {

	const MODE_SANDBOX = 'sandbox';
	const MODE_LIVE = 'live';

	/**
	 * Base API URL
	 *
	 * @var string
	 */
	private $api_url;

	/**
	 * API constructor.
	 *
	 * @param string $mode
	 */
	public function __construct( $mode = '' ) {
		// If mode is not provided, use the current store mode.
		if ( empty( $mode ) ) {
			$mode = edd_is_test_mode() ? self::MODE_SANDBOX : self::MODE_LIVE;
		}

		if ( self::MODE_SANDBOX === $mode ) {
			$this->api_url = 'https://api-m.sandbox.paypal.com';
		} else {
			$this->api_url = 'https://api-m.paypal.com';
		}
	}

	private function get_bearer_token() {
		$response = wp_remote_post( $this->api_url . 'v1/oauth2/token', array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Authorization' => sprintf( 'Basic %s', base64_encode( sprintf( '%s:%s', $this->client_id, $this->client_secret ) ) )
			)
		) );
	}

}
