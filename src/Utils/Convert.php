<?php
/**
 * Utility class to help convert and reformat data.
 *
 * @since 3.3.3
 * @package EDD\Utils
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

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
		$input = str_replace( '-', '_', $input );

		return str_replace( '_', '', ucwords( $input, '_' ) );
	}

	/**
	 * Decode HTML entities in stored text bound for a JavaScript view.
	 *
	 * The view escapes on render, so references left in place are displayed literally.
	 *
	 * @since 3.7.3
	 *
	 * @param string $text The stored text.
	 *
	 * @return string
	 */
	public static function decode_entities( $text ) {
		return html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}
