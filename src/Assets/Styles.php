<?php
/**
 * Handles EDD styles.
 *
 * @package     EDD
 * @subpackage  Assets
 * @since       3.3.0
 */

namespace EDD\Assets;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Styles class.
 */
class Styles {

	/**
	 * Register the EDD stylesheet.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function register() {
		if ( edd_get_option( 'disable_styles', false ) ) {
			return;
		}

		$url = self::get_stylesheet();
		if ( ! $url ) {
			return;
		}

		wp_register_style( 'edd-styles', $url, array(), edd_admin_get_script_version(), 'all' );
	}

	/**
	 * Enqueues an inline style if a custom stylesheet is being used.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public static function head() {
		global $post;

		// Bail if styles are disabled.
		if ( edd_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
			return;
		}

		if ( empty( self::needs_head_styles() ) ) {
			return;
		}

		?>
		<style id="edd-head-styles">.edd_download{float:left;}.edd_download_columns_1 .edd_download{width: 100%;}.edd_download_columns_2 .edd_download{width:50%;}.edd_download_columns_0 .edd_download,.edd_download_columns_3 .edd_download{width:33%;}.edd_download_columns_4 .edd_download{width:25%;}.edd_download_columns_5 .edd_download{width:20%;}.edd_download_columns_6 .edd_download{width:16.6%;}</style>
		<?php
	}

	/**
	 * Gets the URL to the EDD stylesheet.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_stylesheet() {
		$suffix        = edd_doing_script_debug() ? '' : '.min';
		$file          = 'edd' . $suffix . '.css';
		$css_suffix    = is_rtl() ? '-rtl.min.css' : '.min.css';
		$templates_dir = edd_get_theme_template_dir_name();

		$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
		$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
		$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
		$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'edd.css';
		$edd_plugin_style_sheet     = trailingslashit( EDD_PLUGIN_DIR ) . 'assets/css/edd' . $css_suffix;

		// Look in the child theme directory first, followed by the parent theme, followed by the EDD core templates directory
		// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
		// This allows users to copy just edd.css to their theme
		if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
			if ( ! empty( $nonmin ) ) {
				return trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'edd.css';
			}

			return trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}

		if ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
			if ( ! empty( $nonmin ) ) {
				return trailingslashit( get_template_directory_uri() ) . $templates_dir . 'edd.css';
			}

			return trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}

		if ( file_exists( $edd_plugin_style_sheet ) ) {
			return trailingslashit( EDD_PLUGIN_URL ) . 'assets/css/edd' . $css_suffix;
		}

		return false;
	}

	/**
	 * Determine if a custom CSS stylesheet is being used.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function needs_head_styles() {
		global $post;
		if ( ! is_object( $post ) || ! has_shortcode( $post->post_content, 'downloads' ) ) {
			return false;
		}

		// Use minified libraries, not debugging scripts.
		$suffix  = is_rtl() ? '-rtl' : '';
		$suffix .= edd_doing_script_debug() ? '' : '.min';

		$file          = 'edd' . $suffix . '.css';
		$templates_dir = edd_get_theme_template_dir_name();

		$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
		$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
		$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
		$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'edd.css';

		if ( file_exists( $child_theme_style_sheet ) ||
			file_exists( $child_theme_style_sheet_2 ) ||
			file_exists( $parent_theme_style_sheet ) ||
			file_exists( $parent_theme_style_sheet_2 )
		) {
			return apply_filters( 'edd_load_head_styles', true );
		}

		return false;
	}
}
