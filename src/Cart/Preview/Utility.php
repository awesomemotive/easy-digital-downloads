<?php
/**
 * EDD Cart Preview Utility
 *
 * @package EDD\Cart\Preview
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\Cart\Preview;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Utility class.
 *
 * @since 3.6.2
 */
class Utility {

	/**
	 * Check if the cart preview is enabled.
	 *
	 * @since 3.6.2
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return edd_get_option( 'enable_cart_preview', false );
	}

	/**
	 * Load a template.
	 *
	 * @since 3.6.2
	 * @param string $template Template filename.
	 * @return void
	 */
	public static function load_template( string $template ) {
		$template_path = self::get_template_path( $template );
		if ( file_exists( $template_path ) ) {
			require $template_path;
		}
	}

	/**
	 * Get template path.
	 *
	 * @since 3.6.2
	 * @param string $template Template filename.
	 * @return string Full path to template file.
	 */
	private static function get_template_path( $template ) {
		return EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/' . $template;
	}
}
