<?php
/**
 * Colors Utility
 *
 * @package   EDD\Utils
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.5.3
 */

namespace EDD\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Colors utility class.
 *
 * @since 3.5.3
 */
class Colors {

	/**
	 * The functions a color or length may be written with.
	 *
	 * @since 3.7.1
	 * @var string[]
	 */
	public const CSS_VALUE_FUNCTIONS = array(
		'rgb',
		'rgba',
		'hsl',
		'hsla',
		'hwb',
		'lab',
		'lch',
		'oklab',
		'oklch',
		'color',
		'color-mix',
		'light-dark',
		'env',
		'var',
		'calc',
		'clamp',
		'min',
		'max',
	);

	/**
	 * Gets the stored button colors, each one a hex color or an empty string.
	 *
	 * A stored value that is not a hex color reads as unset: every consumer prints
	 * it into CSS, a localized script, or a settings field.
	 *
	 * @since 3.7.1
	 *
	 * @return array {
	 *     @type string $background The stored background color, or an empty string.
	 *     @type string $text       The stored text color, or an empty string.
	 * }
	 */
	public static function get_stored_button_colors() {
		$stored = array(
			'background' => '',
			'text'       => '',
		);

		$colors = edd_get_option( 'button_colors' );
		if ( ! is_array( $colors ) ) {
			return $stored;
		}

		foreach ( array_keys( $stored ) as $key ) {
			$stored[ $key ] = self::get_stored_hex_color( $colors, $key );
		}

		return $stored;
	}

	/**
	 * Gets the button colors a block renders with, defaulting each color that is not stored.
	 *
	 * @since 3.7.1
	 *
	 * @return array {
	 *     @type string $background The background color to render.
	 *     @type string $text       The text color to render.
	 * }
	 */
	public static function get_block_button_colors() {
		// An empty string is a color that is not stored, so it must fall back to the default.
		return wp_parse_args(
			array_filter( self::get_stored_button_colors() ),
			array(
				'background' => '#428bca',
				'text'       => '#ffffff',
			)
		);
	}

	/**
	 * Get button colors.
	 *
	 * @since 3.5.3
	 * @since 3.7.1 Reads the stored background color only when it is a hex color.
	 * @return array $css_colors Button colors.
	 */
	public static function get_button_colors() {
		$button_color = edd_get_button_color_class();
		$css_colors   = array(
			'buttonColor' => self::css_name_to_hex( $button_color ),
		);

		$colors = self::get_stored_button_colors();
		if ( '' !== $colors['background'] ) {
			$css_colors['buttonColor'] = $colors['background'];
		}

		// The text color is the readable one against the background, and the hover color darkens the background by 20 steps.
		$css_colors['buttonTextColor']  = self::get_readable_text_color( $css_colors['buttonColor'] );
		$css_colors['buttonHoverColor'] = self::adjust_color_brightness( $css_colors['buttonColor'], -20 );

		return $css_colors;
	}

	/**
	 * Adjust the brightness of a hex color.
	 *
	 * @since 3.5.3
	 * @param string $hex   The hex color code (with or without #).
	 * @param int    $steps Number of steps to adjust (-255 to 255). Negative = darker, Positive = lighter.
	 * @return string The adjusted hex color code.
	 */
	public static function adjust_color_brightness( $hex, $steps ) {
		$rgb = self::get_rgb_from_hex( $hex );

		// Adjust each color channel.
		$r = max( 0, min( 255, $rgb['r'] + $steps ) );
		$g = max( 0, min( 255, $rgb['g'] + $steps ) );
		$b = max( 0, min( 255, $rgb['b'] + $steps ) );

		// Convert back to hex.
		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) .
					str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) .
					str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Get readable text color (black or white) based on a background color.
	 *
	 * Uses the W3C contrast ratio formula to determine if white or black text
	 * would be more readable on the given background color.
	 *
	 * @since 3.5.3
	 * @param string $hex The background hex color code.
	 * @return string Either '#ffffff' or '#000000'.
	 */
	public static function get_readable_text_color( $hex ) {
		$rgb = self::get_rgb_from_hex( $hex );

		/**
		 * Calculate relative luminance using W3C formula.
		 *
		 * @link https://www.w3.org/WAI/GL/wiki/Relative_luminance
		 */
		$r = $rgb['r'] / 255;
		$g = $rgb['g'] / 255;
		$b = $rgb['b'] / 255;

		$r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( ( $r + 0.055 ) / 1.055 ), 2.4 );
		$g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( ( $g + 0.055 ) / 1.055 ), 2.4 );
		$b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( ( $b + 0.055 ) / 1.055 ), 2.4 );

		$luminance = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;

		// Return white for dark backgrounds, black for light backgrounds.
		return ( $luminance > 0.5 ) ? '#000000' : '#ffffff';
	}

	/**
	 * A value safe to interpolate into a declaration.
	 *
	 * Returns an empty string for anything that could end the declaration or the rule, for a quote
	 * or comment opener, for a bracket left open, and for any function other than those a color or
	 * length is written with.
	 * Everything else is left intact, so hex and named colors, lengths, keywords, rgb(), hsl(),
	 * var() and calc() all survive.
	 *
	 * @since 3.7.1
	 *
	 * @param string $value The submitted value.
	 * @return string
	 */
	public static function css_value( $value ) {
		$value = trim( (string) $value );

		if ( preg_match( '/[;{}\\\\<>"\']/', $value ) || false !== strpos( $value, '/*' ) || self::has_unclosed_bracket( $value ) ) {
			return '';
		}

		preg_match_all( '/([\\w-]+)\\s*\\(/', $value, $functions );

		foreach ( $functions[1] as $function ) {
			if ( ! in_array( strtolower( $function ), self::CSS_VALUE_FUNCTIONS, true ) ) {
				return '';
			}
		}

		return $value;
	}

	/**
	 * A value safe to interpolate inside a double-quoted CSS string.
	 *
	 * Escapes what would end the string or the rule and drops newlines, which a CSS string cannot carry.
	 * Everything else survives, so ordinary punctuation in a label is preserved.
	 *
	 * @since 3.7.1
	 *
	 * @param string $text The submitted text.
	 * @return string
	 */
	public static function css_string( $text ) {
		$text = preg_replace( '/[\\r\\n\\f]+/', ' ', (string) $text );

		// Hex escapes rather than drops: the HTML parser ends a style element on `</style>` without
		// regard for CSS quoting, and braces and a single quote must not reach the stylesheet bare.
		return str_replace(
			array( '\\', '"', "'", '<', '>', '{', '}' ),
			array( '\\\\', '\\"', '\\27 ', '\\3c ', '\\3e ', '\\7b ', '\\7d ' ),
			$text
		);
	}

	/**
	 * Get RGB from hex.
	 *
	 * @since 3.5.3
	 * @param string $hex The hex color code or CSS color name.
	 * @return array $rgb The RGB color code.
	 */
	protected static function get_rgb_from_hex( $hex ) {
		// Convert CSS color names to hex.
		$hex = self::css_name_to_hex( $hex );

		// Remove # if present.
		$hex = str_replace( '#', '', $hex );

		// Handle shorthand hex colors (e.g., #FFF).
		if ( strlen( $hex ) === 3 ) {
			$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) .
					str_repeat( substr( $hex, 1, 1 ), 2 ) .
					str_repeat( substr( $hex, 2, 1 ), 2 );
		}

		// Convert hex to RGB.
		return array(
			'r' => hexdec( substr( $hex, 0, 2 ) ),
			'g' => hexdec( substr( $hex, 2, 2 ) ),
			'b' => hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Convert CSS color name to hex code.
	 *
	 * @since 3.5.3
	 * @param string $color The color (hex code or CSS color name).
	 * @return string The hex color code.
	 */
	protected static function css_name_to_hex( $color ) {
		// If it's already a hex code, return as-is.
		if ( preg_match( '/^#?[0-9a-fA-F]{3,6}$/', $color ) ) {
			return $color;
		}

		$color_map = edd_get_button_colors();
		$color     = strtolower( trim( $color ) );

		return isset( $color_map[ $color ] ) ? $color_map[ $color ]['hex'] : '#333';
	}

	/**
	 * Gets one of the stored button colors, when it is a hex color.
	 *
	 * @since 3.7.1
	 *
	 * @param array  $colors The stored button colors.
	 * @param string $key    The color to read.
	 * @return string The hex color, or an empty string.
	 */
	private static function get_stored_hex_color( $colors, $key ) {
		if ( empty( $colors[ $key ] ) || ! is_string( $colors[ $key ] ) ) {
			return '';
		}

		return (string) sanitize_hex_color( $colors[ $key ] );
	}

	/**
	 * Whether a `(` or `[` in the value is never closed.
	 *
	 * An open bracket in a custom property value takes in every token after it, so the declarations
	 * that follow in the same rule are lost. A stray closer only spoils its own declaration.
	 *
	 * @since 3.7.3
	 * @param string $value The value.
	 * @return bool
	 */
	private static function has_unclosed_bracket( string $value ): bool {
		$closers = array(
			'(' => ')',
			'[' => ']',
		);
		$open    = array();

		foreach ( str_split( $value ) as $char ) {
			if ( isset( $closers[ $char ] ) ) {
				$open[] = $closers[ $char ];
			} elseif ( end( $open ) === $char ) {
				array_pop( $open );
			}
		}

		return ! empty( $open );
	}
}
