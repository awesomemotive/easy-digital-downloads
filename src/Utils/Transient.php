<?php
/**
 * Option handler for transient type data. This does not use the
 * WordPress transients API, but rather a custom option that is
 * set to expire after a certain amount of time.
 *
 * @since 3.3.5
 * @package EDD\Utils
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The Transient class.
 *
 * @since 3.3.5
 */
class Transient {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * Timeout.
	 *
	 * @var string
	 */
	private $timeout;

	/**
	 * Constructor.
	 *
	 * @param string $option_name The option name.
	 * @param string $timeout     The timeout.
	 */
	public function __construct( string $option_name, string $timeout = '+1 day' ) {
		$this->option_name = $option_name;
		$this->timeout     = strtotime( $timeout, time() );
	}

	/**
	 * Gets the option value.
	 *
	 * @since 3.3.5
	 * @return mixed
	 */
	public function get() {
		$option = get_option( $this->option_name, false );
		if ( ! $option ) {
			return false;
		}
		if ( is_string( $option ) ) {
			$option = json_decode( $option, true );
		}

		return ! $this->is_expired( $option ) ? $option['value'] : false;
	}

	/**
	 * Sets the option value.
	 *
	 * @since 3.3.5
	 * @param mixed $data The data to set.
	 * @return bool
	 */
	public function set( $data ) {
		$option = wp_json_encode(
			array(
				'value'   => $data,
				'timeout' => $this->timeout,
			)
		);

		return update_option( $this->option_name, $option, false );
	}

	/**
	 * Deletes the option.
	 *
	 * @since 3.3.5
	 * @return bool
	 */
	public function delete() {
		return delete_option( $this->option_name );
	}

	/**
	 * Checks whether a given option has "expired".
	 *
	 * @since 3.3.5
	 * @param array|false $option The option value.
	 * @return bool
	 */
	private function is_expired( $option ) {
		return empty( $option['timeout'] ) || time() > $option['timeout'];
	}
}
