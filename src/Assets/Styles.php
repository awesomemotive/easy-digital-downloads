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

use EDD\Utils\FileSystem;

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
	 * Gets the file system path to the EDD stylesheet, mirroring get_stylesheet()'s lookup.
	 *
	 * Used to inline the stylesheet into the block editor iframe (see block_editor_settings_all
	 * in includes/blocks/includes/admin/scripts.php) without enqueuing it in the editor's admin
	 * chrome.
	 *
	 * @since 3.7.0
	 * @return string|false
	 */
	public static function get_stylesheet_path() {
		return self::get_stylesheet( 'path' );
	}

	/**
	 * Adds a stylesheet to the block editor canvas through the editor settings.
	 *
	 * Bails when the file is missing, so a caller never inlines an empty stylesheet.
	 *
	 * @since 3.7.3
	 * @param array  $editor_settings Block editor settings.
	 * @param string $path            File system path to the stylesheet.
	 * @param string $url             URL the stylesheet is served from.
	 * @return array
	 */
	public static function add_to_editor_settings( array $editor_settings, string $path, string $url ): array {
		if ( ! $path || ! FileSystem::file_exists( $path ) ) {
			return $editor_settings;
		}

		$editor_settings['styles'][] = array(
			'css'            => FileSystem::get_contents( $path ),
			'baseURL'        => $url,
			'__unstableType' => 'theme',
			'isGlobalStyles' => false,
		);

		return $editor_settings;
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
	 * Gets the URL (or, with $type set to 'path', the file system path) to the EDD stylesheet.
	 *
	 * @since 3.3.0
	 * @since 3.7.0 Added the $type parameter.
	 * @param string $type Either 'url' or 'path'.
	 * @return string|false
	 */
	private static function get_stylesheet( string $type = 'url' ) {
		$suffix        = edd_doing_script_debug() ? '' : '.min';
		$file          = 'edd' . $suffix . '.css';
		$css_suffix    = is_rtl() ? '-rtl.min.css' : '.min.css';
		$templates_dir = edd_get_theme_template_dir_name();

		$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
		$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
		$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
		$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'edd.css';
		$edd_plugin_style_sheet     = edd_get_assets_dir( 'css/frontend' ) . 'edd' . $css_suffix;

		$is_path = 'path' === $type;

		// Look in the child theme directory first, followed by the parent theme, followed by the EDD core templates directory
		// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
		// This allows users to copy just edd.css to their theme.
		$nonmin_child_theme_style_sheet = FileSystem::file_exists( $child_theme_style_sheet_2 );
		if ( FileSystem::file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && $nonmin_child_theme_style_sheet ) ) {
			if ( ! empty( $nonmin_child_theme_style_sheet ) ) {
				return $is_path
					? $child_theme_style_sheet_2
					: trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'edd.css';
			}

			return $is_path
				? $child_theme_style_sheet
				: trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}

		$nonmin_parent_theme_style_sheet = FileSystem::file_exists( $parent_theme_style_sheet_2 );
		if ( FileSystem::file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && $nonmin_parent_theme_style_sheet ) ) {
			if ( ! empty( $nonmin_parent_theme_style_sheet ) ) {
				return $is_path
					? $parent_theme_style_sheet_2
					: trailingslashit( get_template_directory_uri() ) . $templates_dir . 'edd.css';
			}

			return $is_path
				? $parent_theme_style_sheet
				: trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}

		if ( FileSystem::file_exists( $edd_plugin_style_sheet ) ) {
			return $is_path
				? $edd_plugin_style_sheet
				: edd_get_assets_url( 'css/frontend' ) . 'edd' . $css_suffix;
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

		if (
			FileSystem::file_exists( $child_theme_style_sheet ) ||
			FileSystem::file_exists( $child_theme_style_sheet_2 ) ||
			FileSystem::file_exists( $parent_theme_style_sheet ) ||
			FileSystem::file_exists( $parent_theme_style_sheet_2 )
		) {
			return apply_filters( 'edd_load_head_styles', true );
		}

		return false;
	}
}
