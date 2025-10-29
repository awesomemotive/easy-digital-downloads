<?php
/**
 * Settings trait.
 *
 * @package EDD\Profiler\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Profiler\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Settings trait.
 *
 * @since 3.6.0
 */
trait Settings {
	/**
	 * Get the settings for the profiler.
	 *
	 * @return array The settings for the profiler
	 */
	abstract public static function get_settings(): array;

	/**
	 * Get the cookie setting for the profiler.
	 *
	 * @since 3.6.0
	 * @return array The cookie setting for the profiler
	 */
	public static function get_cookie_setting(): array {
		return array(
			'id'      => 'cookie',
			'name'    => __( 'Allow Cookie-Based Profiling', 'easy-digital-downloads' ),
			'check'   => __( 'Set an authentication cookie which allows you to track performance when you\'re not logged in, or when you\'re logged in as a different user.', 'easy-digital-downloads' ),
			'type'    => 'checkbox_toggle',
			'current' => \EDD\Utils\Cookies::get( 'edd_profiler_enabled' ),
			'desc'    => __( 'This applies to all enabled profilers.', 'easy-digital-downloads' ),
			'class'   => self::get_requires_css_class( self::get_id() ),
		);
	}

	/**
	 * Get the classes for a setting that requires another setting to be enabled.
	 *
	 * @since 3.6.0
	 * @param string $requirement The requirement to check.
	 * @param array  $classes     The additional classes to add.
	 * @return string
	 */
	protected static function get_requires_css_class( string $requirement, $classes = array() ) {
		$classes = wp_parse_args(
			$classes,
			array(
				'edd-requires',
				"edd-requires__{$requirement}",
			)
		);

		if ( empty( edd_get_option( $requirement, false ) ) ) {
			$classes[] = 'edd-hidden';
		}

		return implode( ' ', $classes );
	}
}
