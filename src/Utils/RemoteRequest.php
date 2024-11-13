<?php
/**
 * RemoteRequest.php
 *
 * @package EDD
 * @subpackage Utils
 * @since 3.3.5
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class RemoteRequest
 *
 * @since 3.3.5
 */
class RemoteRequest {

	/**
	 * The response object.
	 *
	 * @since 3.3.5
	 * @var false|array|WP_Error
	 */
	private $response;

	/**
	 * The response code.
	 *
	 * @since 3.3.5
	 * @var int
	 */
	private $code;

	/**
	 * The response body.
	 *
	 * @since 3.3.5
	 * @var array|WP_Error
	 */
	private $body;

	/**
	 * The URL to request.
	 *
	 * @since 3.3.5
	 * @var string
	 */
	private $url;

	/**
	 * The request arguments.
	 *
	 * @since 3.3.5
	 * @var array
	 */
	private $args;

	/**
	 * RemoteRequest constructor.
	 *
	 * Note that the request defaults to a 15 second timeout, SSL verification enabled,
	 * and a user agent string that includes the current WordPress version, EDD version, and site URL.
	 * It also defaults to a safe request and the method is set to GET.
	 *
	 * @since 3.3.5
	 * @param string $url    The URL to request.
	 * @param array  $args   The request arguments.
	 */
	public function __construct( string $url, array $args = array() ) {
		$this->url  = $url;
		$this->args = $this->parse_args( $args );
		$this->send_request();
	}

	/**
	 * Magic getter.
	 *
	 * @since 3.3.5
	 * @param string $key The property name.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( ! property_exists( $this, $key ) ) {
			return null;
		}

		if ( is_callable( array( $this, "get_{$key}" ) ) ) {
			return $this->{"get_{$key}"}();
		}

		return $this->$key;
	}

	/**
	 * Parses the request arguments.
	 *
	 * @since 3.3.5
	 * @param array $args The request arguments.
	 * @return array
	 */
	private function parse_args( array $args ) {
		return wp_parse_args(
			$args,
			array(
				'timeout'            => 15,
				'sslverify'          => true,
				'user-agent'         => $this->get_user_agent(),
				'reject_unsafe_urls' => true,
			)
		);
	}

	/**
	 * Sends the request.
	 *
	 * @since 3.3.5
	 */
	private function send_request() {
		$this->response = wp_remote_request(
			esc_url_raw( $this->url ),
			$this->args
		);
	}

	/**
	 * Gets the response.
	 *
	 * @since 3.3.5
	 * @return false|array|WP_Error
	 */
	private function get_response() {
		return $this->response;
	}

	/**
	 * Parses the request response.
	 *
	 * @since 3.3.5
	 * @return false|\WP_Error|string
	 */
	private function get_body() {
		if ( is_null( $this->body ) ) {
			$this->body = wp_remote_retrieve_body( $this->get_response() );
		}

		return $this->body;
	}

	/**
	 * Gets the response code.
	 *
	 * @since 3.3.5
	 * @return int
	 */
	private function get_code() {
		if ( is_null( $this->code ) ) {
			$response = $this->get_response();
			if ( ! $response || is_wp_error( $response ) ) {
				$this->code = 404;
			} else {
				$this->code = wp_remote_retrieve_response_code( $response );
			}
		}

		return $this->code;
	}

	/**
	 * Gets the default user agent string.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	private function get_user_agent() {
		$edd        = edd_is_pro() ? 'EDDPro/' : 'EDD/';
		$user_agent = array(
			'WordPress/' . get_bloginfo( 'version' ),
			$edd . EDD_VERSION,
			get_bloginfo( 'url' ),
		);

		return implode( '; ', $user_agent );
	}
}
