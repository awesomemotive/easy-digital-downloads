<?php
/**
 * REST API checker.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.2.0
 */
namespace EDD\Utils;

defined( 'ABSPATH' ) || exit;

class RESTChecker {

	/**
	 * The REST API endpoint.
	 *
	 * @since 3.2.0
	 * @var string
	 */
	private $endpoint;

	public function __construct( $endpoint = '' ) {
		$this->endpoint = $endpoint;
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_enabled() {

		$response = $this->make_request();

		// When testing the REST API, an error was encountered, leave early.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// When testing the REST API, an unexpected result was returned, leave early.
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		// If the remote response is valid JSON, the REST API is enabled.
		return (bool) $this->is_json( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Make a request to the REST API.
	 *
	 * @since 3.2.0
	 * @return array|WP_Error
	 */
	private function make_request() {

		return wp_safe_remote_get(
			rest_url( $this->endpoint ),
			array(
				'timeout'   => 15,
				'cookies'   => array(),
				'sslverify' => $this->sslverify(),
				'headers'   => array(
					'Cache-Control' => 'no-cache',
				),
			)
		);
	}

	/**
	 * Whether to verify SSL when making a request to the REST API.
	 * This filter is documented in wp-includes/class-wp-http-streams.php
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function sslverify() {
		return apply_filters( 'https_local_ssl_verify', false );
	}

	/**
	 * Whether a string is valid JSON.
	 *
	 * @since 3.2.0
	 * @param string $string
	 * @return bool
	 */
	private function is_json( $string ) {
		return (
			is_string( $string ) &&
			is_array( json_decode( $string, true ) ) &&
			json_last_error() === JSON_ERROR_NONE
		);
	}
}
