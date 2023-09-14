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

class API {

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
}
