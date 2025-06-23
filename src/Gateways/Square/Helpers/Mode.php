<?php
/**
 * Mode helper for the Square gateway.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Mode helper for the Square gateway.
 *
 * @since 3.4.0
 */
class Mode {

	/**
	 * The mode for the Square gateway.
	 *
	 * @var string
	 */
	private static $mode;

	/**
	 * Get the mode for the Square gateway.
	 *
	 * @since 3.4.0
	 *
	 * @return string The mode for the Square gateway.
	 */
	public static function get() {
		if ( ! self::$mode ) {
			self::$mode = edd_is_test_mode() ? 'sandbox' : 'live';
		}

		return self::$mode;
	}

	/**
	 * Check if the mode is sandbox.
	 *
	 * @since 3.4.0
	 * @return bool True if the mode is sandbox, false otherwise.
	 */
	public static function is_sandbox() {
		return self::get() === 'sandbox';
	}

	/**
	 * Check if the mode is live.
	 *
	 * @since 3.4.0
	 * @return bool True if the mode is live, false otherwise.
	 */
	public static function is_live() {
		return self::get() === 'live';
	}
}
