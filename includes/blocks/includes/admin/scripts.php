<?php
/**
 * Scripts for the EDD Blocks.
 *
 * @package EDD\Blocks\Admin\Scripts
 * @copyright Copyright Easy Digital Downloads
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.0
 */

namespace EDD\Blocks\Admin\Scripts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Adds a custom variable to the JS to allow a user in the block editor
 * to preview sensitive data.
 *
 * @since 2.0
 * @return void
 */
function localize() {

	$user = wp_get_current_user();

	$download_query_args = array(
		'post_type'      => 'download',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	);

	$published_downloads = new \WP_Query(
		array_merge(
			$download_query_args,
			array(
				'post_status' => array( 'publish' ),
			)
		)
	);

	$draft_downloads = new \WP_Query(
		array_merge(
			$download_query_args,
			array(
				'post_status' => array( 'draft' ),
			)
		)
	);

	$button_colors = \EDD\Utils\Colors::get_block_button_colors();

	wp_localize_script(
		'wp-block-editor',
		'EDDBlocks',
		array(
			'current_user'            => md5( $user->user_email ),
			'current_user_email'      => $user->user_email,
			'all_access'              => function_exists( 'edd_all_access' ),
			'recurring'               => function_exists( 'EDD_Recurring' ),
			'is_pro'                  => edd_is_pro(),
			'no_redownload'           => edd_no_redownload(),
			'supports_buy_now'        => edd_shop_supports_buy_now(),
			'has_published_downloads' => $published_downloads->have_posts(),
			'has_draft_downloads'     => $draft_downloads->have_posts(),
			'new_download_link'       => add_query_arg( 'post_type', 'download', admin_url( 'post-new.php' ) ),
			'view_downloads_link'     => add_query_arg( 'post_type', 'download', admin_url( 'edit.php' ) ),
			'download_label_singular' => edd_get_label_singular(),
			'download_label_plural'   => edd_get_label_plural(),
			'checkout_registration'   => in_array( edd_get_option( 'show_register_form', 'none' ), array( 'both', 'registration' ), true ),
			'button_colors'           => $button_colors,
			'css_value_functions'     => \EDD\Utils\Colors::CSS_VALUE_FUNCTIONS,
			'featured_promo'          => ! edd_is_pro() && class_exists( '\\EDD\\Lite\\Admin\\Promos\\Notices\\FeaturedDownloads' ),
			'manage_shop_discounts'   => current_user_can( 'manage_shop_discounts' ),
			'quantities_enabled'      => edd_item_quantities_enabled(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\localize' );

/**
 * Makes sure the payment icons show on the checkout block in the editor.
 *
 * @since 2.0
 */
add_action( 'admin_print_footer_scripts', '\edd_print_payment_icons_on_checkout' );

/**
 * If the EDD styles are registered, load them into the block editor iframe.
 *
 * @since 2.0
 * @since 3.7.0 Switched from enqueue_block_assets (which also loads in the
 *                        editor's admin chrome, overriding metabox styles) to inlining
 *                        the stylesheet into the iframe via block_editor_settings_all.
 * @param array $editor_settings Block editor settings.
 * @return array
 */
function add_edd_styles_block_editor( $editor_settings ) {
	if ( ! wp_style_is( 'edd-styles', 'registered' ) ) {
		return $editor_settings;
	}

	return \EDD\Assets\Styles::add_to_editor_settings(
		$editor_settings,
		(string) \EDD\Assets\Styles::get_stylesheet_path(),
		wp_styles()->registered['edd-styles']->src
	);
}
add_filter( 'block_editor_settings_all', __NAMESPACE__ . '\add_edd_styles_block_editor' );
