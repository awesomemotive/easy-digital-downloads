<?php
/**
 * Settings Helpers Trait
 *
 * Provides reusable helper methods for settings classes.
 *
 * @package     EDD\Admin\Settings\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.1
 */

namespace EDD\Admin\Settings\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trait Helpers
 *
 * @since 3.6.1
 */
trait Helpers {

	/**
	 * Get the required CSS class for conditional settings display.
	 *
	 * Supports both normal and inverse logic for conditional field display.
	 * Normal logic: Show fields when requirement is TRUE, hide when FALSE.
	 * Inverse logic: Show fields when requirement is FALSE, hide when TRUE.
	 *
	 * To use inverse logic, pass 'inverse' as one of the classes.
	 *
	 * @since 3.6.1
	 * @param string $requirement The requirement setting ID to check.
	 * @param array  $classes     Additional CSS classes to include. Pass 'inverse' to use inverse logic.
	 * @param mixed  $value       The value to check against (for select fields, etc.).
	 * @return string Space-separated CSS class string.
	 */
	protected function get_requires_css_class( string $requirement, array $classes = array(), $value = false ) {
		// Check if inverse logic is requested and remove it from classes array.
		$inverse = in_array( 'inverse', $classes, true );
		if ( $inverse ) {
			$classes = array_filter(
				$classes,
				function ( $class ) {
					return 'inverse' !== $class;
				}
			);
		}

		$classes = wp_parse_args(
			$classes,
			array(
				'edd-requires',
				"edd-requires__{$requirement}",
			)
		);

		if ( $value ) {
			if ( is_array( $value ) ) {
				$should_hide = ! in_array( edd_get_option( $requirement, false ), $value, true );
			} else {
				$should_hide = edd_get_option( $requirement, false ) !== $value;
				$classes[]   = "edd-requires__{$requirement}-{$value}";
			}
		} else {
			// Determine if field should be hidden based on requirement state.
			// Inverse logic is used for settings like "disabled" where true means hide.
			$should_hide = $inverse
				? ! empty( edd_get_option( $requirement, false ) )  // Inverse: hide if TRUE.
				: empty( edd_get_option( $requirement, false ) );   // Normal: hide if FALSE.
		}

		if ( $should_hide ) {
			$classes[] = 'edd-hidden';
		}

		return implode( ' ', $classes );
	}

	/**
	 * Parses a description array into a string.
	 *
	 * Joins array elements with line breaks and filters out empty values.
	 *
	 * @since 3.6.1
	 * @param array $description The description array to parse.
	 * @return string Formatted description string with line breaks.
	 */
	protected function parse_description( array $description ): string {
		return implode( '<br />', array_filter( $description ) );
	}
}
