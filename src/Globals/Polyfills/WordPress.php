<?php
/**
 * Polyfills for WordPress functions which may not be available in older versions.
 *
 * These are loaded with the EDD\Globals\Polyfills\Loader class, so that we can ensure
 * they are loaded before pluggable functions are loaded.
 *
 * @since 3.2.0
 *
 * @package     EDD
 * @subpackage  Globals\Polyfills\WordPress
 */

if ( ! function_exists( 'wp_readonly' ) ) {
	/**
	 * Polyfill for wp_readonly() function added in WP 5.9.0.
	 *
	 * Outputs the HTML readonly attribute.
	 *
	 * Compares the first two arguments and if identical marks as readonly.
	 *
	 * @since 5.9.0
	 *
	 * @param mixed $readonly_value One of the values to compare.
	 * @param mixed $current        Optional. The other value to compare if not just true.
	 *                              Default true.
	 * @param bool  $display        Optional. Whether to echo or just return the string.
	 *                              Default true.
	 * @return string HTML attribute or empty string.
	 */
	function wp_readonly( $readonly_value, $current = true, $display = true ) {
		return __checked_selected_helper( $readonly_value, $current, $display, 'readonly' );
	}
}
