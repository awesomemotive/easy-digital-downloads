<?php
/**
 * Scripts
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/** Front End *****************************************************************/

/**
 * Register all front-end scripts
 *
 * @since 3.0
 */
function edd_register_scripts() {
	EDD\Assets\Checkout::register();
}
add_action( 'init', 'edd_register_scripts' );

/**
 * Register styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 */
function edd_register_styles() {
	EDD\Assets\Styles::register();
}
add_action( 'init', 'edd_register_styles' );

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @since 3.0 calls edd_enqueue_scripts()
 */
function edd_load_scripts() {
	edd_enqueue_scripts();
	edd_localize_scripts();
}
add_action( 'wp_enqueue_scripts', 'edd_load_scripts' );

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 3.0
 */
function edd_enqueue_scripts() {

	// Checkout scripts.
	if ( edd_is_checkout() ) {
		EDD\Assets\Checkout::enqueue();
	}

	// AJAX scripts, if enabled.
	if ( ! edd_is_ajax_disabled() ) {
		wp_enqueue_script( 'edd-ajax' );
	}
}

/**
 * Enqueue styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 3.0
 */
function edd_enqueue_styles() {
	wp_enqueue_style( 'edd-styles' );
}
add_action( 'wp_enqueue_scripts', 'edd_enqueue_styles' );

/**
 * Localize scripts
 *
 * @since 3.0
 *
 * @global $post $post
 */
function edd_localize_scripts() {
	EDD\Assets\Localization::checkout();
	EDD\Assets\Localization::ajax();
}

/**
 * Load head styles
 *
 * Ensures download styling is still shown correctly if a theme is using the CSS template file
 *
 * @since 2.5
 * @global $post
 */
function edd_load_head_styles() {
	EDD\Assets\Styles::head();
}
add_action( 'wp_print_styles', 'edd_load_head_styles' );

/**
 * Determine if the frontend scripts should be loaded in the footer or header (default: footer)
 *
 * @since 2.8.6
 * @return mixed
 */
function edd_scripts_in_footer() {
	return apply_filters( 'edd_load_scripts_in_footer', true );
}

/** Admin Area ****************************************************************/

/**
 * Return the current script version
 *
 * @since 3.0
 *
 * @return string
 */
function edd_admin_get_script_version() {
	return edd_doing_script_debug()
		? time()
		: EDD_VERSION;
}

/**
 * Register all admin area scripts
 *
 * @since 3.0
 */
function edd_register_admin_scripts() {
	EDD\Admin\Assets\Scripts::register();
}
add_action( 'admin_init', 'edd_register_admin_scripts' );

/**
 * Register all admin area styles
 *
 * @since 3.0
 */
function edd_register_admin_styles() {
	EDD\Admin\Assets\Styles::register();
}
add_action( 'admin_init', 'edd_register_admin_styles' );

/**
 * Print admin area scripts
 *
 * @since 3.0
 */
function edd_enqueue_admin_scripts( $hook = '' ) {
	EDD\Admin\Assets\Scripts::enqueue( $hook );
}
add_action( 'admin_enqueue_scripts', 'edd_enqueue_admin_scripts' );

/**
 * Enqueue admin area styling.
 *
 * Always enqueue the menu styling. Only enqueue others on EDD pages.
 *
 * @since 3.0
 */
function edd_enqueue_admin_styles( $hook = '' ) {
	EDD\Admin\Assets\Styles::enqueue( $hook );
}
add_action( 'admin_enqueue_scripts', 'edd_enqueue_admin_styles' );

/**
 * Localize all admin scripts
 *
 * @since 3.0
 */
function edd_localize_admin_scripts() {
	EDD\Admin\Assets\Localization::admin();
	EDD\Admin\Assets\Localization::upgrades();
}
add_action( 'admin_enqueue_scripts', 'edd_localize_admin_scripts' );

/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @since 1.0
 * @since 2.6.11 Removed globals and CSS for custom icon
 */
function edd_admin_downloads_icon() {

	$images_url      = EDD_PLUGIN_URL . 'assets/images/';
	$menu_icon       = '\f316';
	$icon_cpt_url    = $images_url . 'edd-cpt.png';
	$icon_cpt_2x_url = $images_url . 'edd-cpt-2x.png';
	?>
	<style type="text/css" media="screen">
		#dashboard_right_now .download-count:before {
			content: '<?php echo $menu_icon; ?>';
		}
		#icon-edit.icon32-posts-download {
			background: url(<?php echo $icon_cpt_url; ?>) -7px -5px no-repeat;
		}
		@media
		only screen and (-webkit-min-device-pixel-ratio: 1.5),
		only screen and (   min--moz-device-pixel-ratio: 1.5),
		only screen and (     -o-min-device-pixel-ratio: 3/2),
		only screen and (        min-device-pixel-ratio: 1.5),
		only screen and (        		 min-resolution: 1.5dppx) {
			#icon-edit.icon32-posts-download {
				background: url(<?php echo $icon_cpt_2x_url; ?>) no-repeat -7px -5px !important;
				background-size: 55px 45px !important;
			}
		}
	</style>
	<?php
}
add_action( 'admin_head', 'edd_admin_downloads_icon' );

/**
 * Should we be loading admin scripts
 *
 * @since 3.0
 *
 * @param string $hook
 * @return bool
 */
function edd_should_load_admin_scripts( $hook = '' ) {

	// Back compat for hook suffix.
	$hook_suffix = empty( $hook )
		? $GLOBALS['hook_suffix']
		: $hook;

	// Filter & return.
	return (bool) apply_filters( 'edd_load_admin_scripts', edd_is_admin_page(), $hook_suffix );
}

add_action( 'wp_body_open', 'edd_add_js_class', 100 );
/**
 * Use javascript to remove the no-js class from the body element.
 * The `wp_body_open` was added in WordPress 5.2.0 but it's dependent on themes to include it.
 *
 * @since 3.1
 * @return void
 */
function edd_add_js_class() {
	?>
	<style>.edd-js-none .edd-has-js, .edd-js .edd-no-js, body.edd-js input.edd-no-js { display: none; }</style>
	<script>/* <![CDATA[ */(function(){var c = document.body.classList;c.remove('edd-js-none');c.add('edd-js');})();/* ]]> */</script>
	<?php
}

add_action( 'wp_footer', 'edd_back_compat_add_js_class' );
/**
 * Backwards compatible no-js replacement--runs if the wp_body_open hook
 * is not present.
 *
 * @since 3.1
 * @return void
 */
function edd_back_compat_add_js_class() {
	if ( did_action( 'wp_body_open' ) ) {
		return;
	}
	edd_add_js_class();
}

/** Deprecated ****************************************************************/

/**
 * Enqueue admin area scripts.
 *
 * Only enqueue on EDD pages.
 *
 * @since 1.0
 * @deprecated 3.0
 */
function edd_load_admin_scripts( $hook ) {

	// Bail if not an EDD admin page.
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Register all scripts and styles.
	edd_register_admin_scripts();
	edd_register_admin_styles();

	// Load scripts and styles for back-compat.
	edd_enqueue_admin_scripts( $hook );
	edd_enqueue_admin_styles( $hook );
}
