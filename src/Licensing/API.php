<?php
/**
 * Licensing API
 *
 * Tool for making requests to the Software Licensing API.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Licensing;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Represents the API class for handling licensing in Easy Digital Downloads Pro.
 */
class API {

	/**
	 * Whether or not to log failed requests.
	 *
	 * @since 3.3.0
	 * @var bool
	 */
	public $should_check_failed_request = false;

	/**
	 * The Software Licensing API URL.
	 *
	 * @since 3.1.1
	 * @var string
	 */
	private $api_url = 'https://easydigitaldownloads.com/edd-sl-api';

	/**
	 * The class constructor.
	 *
	 * @since 3.1.1.4
	 * @param null|string $url Optional; used only for requests to non-EDD sites.
	 */
	public function __construct( $url = null ) {
		if ( ! empty( $url ) ) {
			$this->api_url = $url;
		}
	}

	/**
	 * Gets the API URL.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	public function get_url() {
		return $this->api_url;
	}

	/**
	 * Makes a request to the Software Licensing API.
	 *
	 * @since 3.1.1
	 * @param array $api_params The parameters for the API request.
	 * @return false|stdClass
	 */
	public function make_request( $api_params = array() ) {
		if ( empty( $api_params ) || ! is_array( $api_params ) ) {
			return false;
		}

		// If a request has recently failed, don't try again.
		if ( $this->request_recently_failed() ) {
			return false;
		}

		$request = wp_remote_get(
			$this->api_url,
			array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $this->get_body( $api_params ),
			)
		);

		// If there was an API error, return false.
		if ( is_wp_error( $request ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
			$this->log_failed_request();

			return false;
		}

		return json_decode( wp_remote_retrieve_body( $request ) );
	}

	/**
	 * Updates the API parameters with the defaults.
	 *
	 * @param array $api_params The parameters for the specific request.
	 * @return array
	 */
	private function get_body( array $api_params ) {
		return wp_parse_args(
			$api_params,
			array(
				'url' => home_url(),
			)
		);
	}

	/**
	 * Determines if a request has recently failed.
	 *
	 * @since 1.9.1
	 *
	 * @return bool
	 */
	private function request_recently_failed() {
		if ( ! $this->should_check_failed_request ) {
			return false;
		}

		$failed_request_details = get_option( $this->get_failed_request_cache_key() );

		// Request has never failed.
		if ( empty( $failed_request_details ) || ! is_numeric( $failed_request_details ) ) {
			return false;
		}

		/*
		 * Request previously failed, but the timeout has expired.
		 * This means we're allowed to try again.
		 */
		if ( time() > $failed_request_details ) {
			delete_option( $this->get_failed_request_cache_key() );

			return false;
		}

		return true;
	}

	/**
	 * Logs a failed HTTP request for this API URL.
	 * We set a timestamp for 1 hour from now. This prevents future API requests from being
	 * made to this domain for 1 hour. Once the timestamp is in the past, API requests
	 * will be allowed again. This way if the site is down for some reason we don't bombard
	 * it with failed API requests.
	 *
	 * @since 3.3.0
	 */
	private function log_failed_request() {
		update_option( $this->get_failed_request_cache_key(), strtotime( '+1 hour' ) );
	}

	/**
	 * Retrieves the cache key for the failed requests option.
	 *
	 * @since 3.3.0
	 * @return string The cache key for failed requests.
	 */
	private function get_failed_request_cache_key() {
		return 'edd_failed_request_' . md5( $this->api_url );
	}
}
