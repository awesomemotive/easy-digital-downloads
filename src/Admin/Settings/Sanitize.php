<?php
/**
 * Sanitizes settings.
 *
 * @since 3.1.4
 * @package EDD
 */

namespace EDD\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sanitize class.
 *
 * @since 3.1.4
 */
class Sanitize {

	/**
	 * Sanitizes the sequential order number starting number.
	 *
	 * @since 3.1.4
	 * @param array $input The input.
	 * @return array
	 */
	public function sanitize_sequential_order_numbers( $input ) {
		return \EDD\Admin\Settings\Sanitize\Tabs\Gateways\Accounting::sanitize( $input );
	}
}
