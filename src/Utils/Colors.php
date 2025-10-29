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
defined( 'ABSPATH' ) || exit;

/**
 * Colors utility class.
 *
 * @since 3.5.3
 */
class Colors {

	/**
	 * Get button colors.
	 *
	 * @since 3.5.3
	 * @return array $css_colors Button colors.
	 */
	public static function get_button_colors() {
		$button_color = edd_get_button_color_class();
		$css_colors   = array(
			'buttonColor'     => self::css_name_to_hex( $button_color ),
			'buttonTextColor' => '#fff',
		);

		$colors = edd_get_option( 'button_colors' );
		if ( ! empty( $colors ) ) {
			if ( ! empty( $colors['background'] ) ) {
				$css_colors['buttonColor'] = $colors['background'];
			}
			if ( ! empty( $colors['text'] ) ) {
				$css_colors['buttonTextColor'] = $colors['text'];
			}
		}

		// Calculate hover color (darken by 20 steps) and auto-detect text color if not explicitly set.
		$css_colors['buttonHoverColor'] = self::adjust_color_brightness( $css_colors['buttonColor'], -20 );
		$css_colors['buttonTextColor']  = self::get_readable_text_color( $css_colors['buttonColor'] );

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
}
