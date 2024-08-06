<?php
/**
 * Base Tab class for sanitization of a settings tab.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs
 */

namespace EDD\Admin\Settings\Sanitize\Tabs;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base Tab class for sanitization.
 *
 * @since 3.3.3
 */
abstract class Tab {
	/**
	 * Sanitize the settings in a tab.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings for the settings tab.
	 *
	 * @return array
	 */
	public static function sanitize( $input ) {
		$processed_input = get_called_class()::process( $input );

		// If after running the process method, something has drastically changed, return the original input.
		return is_array( $processed_input )
			? $processed_input
			: $input;
	}

	/**
	 * Process the settings tab.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	abstract protected static function process( $input );
}
