<?php
/**
 * Sanitizes the Rich Editor setting type.
 *
 * @since 3.3.3
 * @package EDD\Settings\Sanitize\Types
 */

namespace EDD\Settings\Sanitize\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Sanitizes the Rich Editor setting type.
 *
 * @since 3.3.3
 */
class RichEditor extends Type {
	/**
	 * Sanitize the text setting type.
	 *
	 * @since 3.3.3
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string
	 */
	protected static function _sanitize( $value ) {
		return trim( wp_kses( $value, self::get_allowed_tags() ) );
	}

	/**
	 * Get the allowed tags for the rich editor.
	 *
	 * @since 3.3.3
	 * return array
	 */
	private static function get_allowed_tags() {
		$base_allowed_tags = edd_get_allowed_tags();

		return apply_filters( 'edd_rich_editor_allowed_tags', $base_allowed_tags );
	}
}
