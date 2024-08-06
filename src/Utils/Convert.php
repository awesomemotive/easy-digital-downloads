<?php
/**
 * Utility class to help convert and reformat data.
 *
 * @since 3.3.3
 * @package EDD\Utils
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Utility class to help convert and reformat data.
 *
 * @since 3.3.3
 */
class Convert {
	/**
	 * Convert a camel case string to snake case.
	 *
	 * Useful when trying to convert a string into a class name.
	 *
	 * @since 3.3.3
	 *
	 * @param string $input The string to convert.
	 *
	 * @return string
	 */
	public static function snake_to_camel( $input ) {
		return str_replace( '_', '', ucwords( $input, '_' ) );
	}
}
