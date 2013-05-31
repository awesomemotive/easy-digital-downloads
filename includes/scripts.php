<?php
/**
 * Scripts
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $edd_options
 * @global $post
 * @return void
 */
function edd_load_scripts() {
	global $edd_options, $post;

	$js_dir = EDD_PLUGIN_URL . 'assets/js/';

	wp_enqueue_script( 'jquery' );

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Get position in cart of current download
	if ( isset( $post->ID ) ) {
		$position = edd_get_item_position_in_cart( $post->ID );
	}

	if ( edd_is_checkout() ) {
		if ( edd_is_cc_verify_enabled() ) {
			wp_enqueue_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		}
		wp_enqueue_script( 'edd-checkout-global', $js_dir . 'edd-checkout-global' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		wp_localize_script( 'edd-checkout-global', 'edd_global_vars', array(
			'ajaxurl'            => edd_get_ajax_url(),
			'checkout_nonce'     => wp_create_nonce( 'edd_checkout_nonce' ),
			'currency_sign'      => edd_currency_filter(''),
			'currency_pos'       => isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before',
			'no_gateway'         => __( 'Please select a payment method', 'edd' ),
			'no_discount'        => __( 'Please enter a discount code', 'edd' ), // Blank discount code message
			'enter_discount'     => __( 'Enter discount', 'edd' ),
			'discount_applied'   => __( 'Discount Applied', 'edd' ), // Discount verified message
			'no_email'           => __( 'Please enter an email address before applying a discount code', 'edd' ),
			'no_username'        => __( 'Please enter a username before applying a discount code', 'edd' ),
			'purchase_loading'   => __( 'Please Wait...', 'edd' ),
			'complete_purchasse' => __( 'Purchase', 'edd' ),
			'taxes_enabled'      => edd_use_taxes() ? '1' : '0',
			'edd_version'        => EDD_VERSION
		));
	}

	// Load AJAX scripts, if enabled
	if ( edd_is_ajax_enabled() ) {
		wp_enqueue_script( 'edd-ajax', $js_dir . 'edd-ajax' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		wp_localize_script( 'edd-ajax', 'edd_scripts', array(
				'ajaxurl'                 => edd_get_ajax_url(),
				'ajax_nonce'              => wp_create_nonce( 'edd_ajax_nonce' ),
				'position_in_cart'        => isset( $position ) ? $position : -1,
				'already_in_cart_message' => __('You have already added this item to your cart', 'edd'), // Item already in the cart message
				'empty_cart_message'      => __('Your cart is empty', 'edd'), // Item already in the cart message
				'loading'                 => __('Loading', 'edd') , // General loading message
				'select_option'           => __('Please select an option', 'edd') , // Variable pricing error with multi-purchase option enabled
				'ajax_loader'             => EDD_PLUGIN_URL . 'assets/images/loading.gif', // Ajax loading image
				'is_checkout'             => edd_is_checkout() ? '1' : '0',
				'default_gateway'         => edd_get_default_gateway(),
				'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
				'checkout_page'           => edd_get_checkout_uri(),
				'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'edd_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @global $edd_options
 * @return void
 */
function edd_register_styles() {
	global $edd_options;

	if ( isset( $edd_options['disable_styles'] ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = 'edd_templates/';

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'edd.css';
	$edd_plugin_style_sheet     = trailingslashit( edd_get_templates_dir()    ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the EDD core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just edd.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) )
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'edd.css';
		else
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) )
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'edd.css';
		else
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
	} elseif ( file_exists( $edd_plugin_style_sheet ) || file_exists( $edd_plugin_style_sheet ) ) {
		$url = trailingslashit( edd_get_templates_url() ) . $file;
	}

	wp_enqueue_style( 'edd-styles', $url, array(), EDD_VERSION );
}
add_action( 'wp_enqueue_scripts', 'edd_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @global $pagenow
 * @global $edd_discounts_page
 * @global $edd_payments_page
 * @global $edd_settings_page
 * @global $edd_reports_page
 * @global $edd_system_info_page
 * @global $edd_add_ons_page
 * @global $edd_options
 * @global $edd_upgrades_screen
 * @param string $hook Page hook
 * @return void
 */
function edd_load_admin_scripts( $hook ) {
	global $post,
	$pagenow,
	$edd_discounts_page,
	$edd_payments_page,
	$edd_settings_page,
	$edd_reports_page,
	$edd_system_info_page,
	$edd_add_ons_page,
	$edd_options,
	$edd_upgrades_screen,
	$wp_version;

	$js_dir = EDD_PLUGIN_URL . 'assets/js/';
	$css_dir = EDD_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$edd_pages = array( $edd_discounts_page, $edd_payments_page, $edd_settings_page, $edd_reports_page, $edd_system_info_page, $edd_add_ons_page, $edd_upgrades_screen, 'index.php', );
	$edd_cpt   = apply_filters( 'edd_load_scripts_for_these_types', array( 'download', 'edd_payment', ) );

	if ( ! in_array( $hook, $edd_pages ) && ! is_object( $post ) )
		return;

	if ( is_object( $post ) && ! in_array( $post->post_type, $edd_cpt ) )
		return;

	if ( 'download_page_edd-reports' == $hook ) {
		wp_enqueue_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
	}
	if ( 'download_page_edd-discounts' == $hook ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
		wp_enqueue_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	}
	if ( $hook == $edd_settings_page ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', array(), '1.3.20' );
		wp_enqueue_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
		if( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
        	 //call for new media manager
         	wp_enqueue_media();
      }
	}
	wp_enqueue_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), EDD_VERSION );
	wp_enqueue_script( 'jquery-chosen', $js_dir . 'chosen.jquery.min.js', array( 'jquery' ), EDD_VERSION );
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_script( 'edd-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery' ), EDD_VERSION, false );
	wp_localize_script( 'edd-admin-scripts', 'edd_vars', array(
		'post_id'            => isset( $post->ID ) ? $post->ID : null,
		'edd_version'        => EDD_VERSION,
		'add_new_download'   => __( 'Add New Download', 'edd' ), 									// Thickbox title
		'use_this_file'      => __( 'Use This File','edd' ), 										// "use this file" button
		'quick_edit_warning' => __( 'Sorry, not available for variable priced products.', 'edd' ),
		'delete_payment'     => __( 'Are you sure you wish to delete this payment?', 'edd' ),
		'delete_payment_note'=> __( 'Are you sure you wish to delete this note?', 'edd' ),
		'delete_tax_rate'    => __( 'Are you sure you wish to delete this tax rate?', 'edd' ),
		'one_price_min'      => __( 'You must have at least one price', 'edd' ),
		'one_file_min'       => __( 'You must have at least one file', 'edd' ),
		'one_field_min'      => __( 'You must have at least one field', 'edd' ),
		'currency_sign'      => edd_currency_filter(''),
		'currency_pos'       => isset( $edd_options['currency_position'] ) ? $edd_options['currency_position'] : 'before',
		'new_media_ui'       => apply_filters( 'edd_use_35_media_ui', 1 ),
		'remove_text'        => __( 'Remove', 'edd' ),
	));
	wp_enqueue_style( 'thickbox' );

	wp_enqueue_style( 'edd-admin', $css_dir . 'edd-admin' . $suffix . '.css', EDD_VERSION );
}
add_action( 'admin_enqueue_scripts', 'edd_load_admin_scripts', 100 );

/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @since 1.0
 * @global $post_type
 * @return void
*/
function edd_admin_downloads_icon() {
	global $post_type;

	$images_url  = EDD_PLUGIN_URL . 'assets/images/';
	$icon_url    = $images_url . 'edd-icon.png';
	$icon_cpt_url = $images_url . 'edd-cpt.png';
	$icon_2x_url = $images_url . 'edd-icon-2x.png';
	?>
	<style type="text/css" media="screen">
		body #adminmenu #menu-posts-download div.wp-menu-image { background: transparent url(<?php echo $icon_url; ?>) no-repeat 7px -32px; }
		body #adminmenu #menu-posts-download:hover div.wp-menu-image,
		body #adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image { background: transparent url(<?php echo $icon_url; ?>) no-repeat 7px 0; }
		<?php if ( ( isset( $_GET['post_type'] ) ) && ( 'download' == $_GET['post_type'] ) || ( 'download' == $post_type ) ) : ?>
		#icon-edit { background: transparent url(<?php echo $icon_cpt_url; ?>) no-repeat; }
		<?php endif; ?>
		@media
		only screen and (-webkit-min-device-pixel-ratio: 1.5),
		only screen and (   min--moz-device-pixel-ratio: 1.5),
		only screen and (     -o-min-device-pixel-ratio: 3/2),
		only screen and (        min-device-pixel-ratio: 1.5),
		only screen and (        		 min-resolution: 1.5dppx) {
			/* Admin Menu - 16px @2x */
			body #adminmenu #menu-posts-download div.wp-menu-image {
				background: transparent url(<?php echo $icon_2x_url; ?>) no-repeat 7px -20px !important;
				background-size: 16px 48px !important;
			}

			body #adminmenu #menu-posts-download:hover div.wp-menu-image,
			body #adminmenu #menu-posts-download.wp-menu-open div.wp-menu-image {
				background-position: 7px 4px !important;
			}

			/* Post Screen - 32px @2x */
			.icon32-posts-download {
				background: url(<?php echo $icon_2x_url; ?>) no-repeat 0 0 !important;
				background-size: 32px 32px !important;
			}
		}
		#edd-media-button { -webkit-background-size: 16px; -moz-background-size: 16px; background-size: 16px; background-image: url(<?php echo $icon_cpt_url; ?>); margin-top: -1px; }
	</style>
	<?php
}
add_action( 'admin_head','edd_admin_downloads_icon' );

/**
 * Adds EDD Version to the <head> tag
 *
 * @since 1.4.2
 * @return void
*/
function edd_version_in_header(){
	// Newline on both sides to avoid being in a blob
	echo '<meta name="generator" content="Easy Digital Downloads v' . EDD_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'edd_version_in_header' );
