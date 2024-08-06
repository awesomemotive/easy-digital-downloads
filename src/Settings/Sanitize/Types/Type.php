<?php
/**
 * Base Type class for sanitizing a EDD setting type.
 *
 * @since 3.3.3
 * @package EDD\Settings\Sanitize\Types
 */

namespace EDD\Settings\Sanitize\Types;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base Type class for sanitizing a EDD setting type.
 *
 * @since 3.3.3
 */
abstract class Type {
	/**
	 * Sanitize the value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed
	 */
	public static function sanitize( $value ) {
		return get_called_class()::_sanitize( $value );
	}
}
