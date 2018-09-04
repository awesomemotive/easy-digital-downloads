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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Front End *****************************************************************/

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $post
 * @return void
 */
function edd_load_scripts() {
	global $post;

	$js_dir = EDD_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$version = edd_admin_get_script_version();

	// Get position in cart of current download
	if ( isset( $post->ID ) ) {
		$position = edd_get_item_position_in_cart( $post->ID );
	}

	$has_purchase_links = false;
	if ( ( ! empty( $post->post_content ) && ( has_shortcode( $post->post_content, 'purchase_link' ) || has_shortcode( $post->post_content, 'downloads' ) ) ) || is_post_type_archive( 'download' ) ) {
		$has_purchase_links = true;
	}

	$in_footer = edd_scripts_in_footer();

	if ( edd_is_checkout() ) {
		if ( edd_is_cc_verify_enabled() ) {
			wp_register_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), $version, $in_footer );

			// Registered so gateways can enqueue it when they support the space formatting. wp_enqueue_script( 'jQuery.payment' );
			wp_register_script( 'jQuery.payment', $js_dir . 'jquery.payment.min.js', array( 'jquery' ), $version, $in_footer );

			wp_enqueue_script( 'creditCardValidator' );
		}

		wp_register_script( 'edd-checkout-global', $js_dir . 'edd-checkout-global' . $suffix . '.js', array( 'jquery' ), $version, $in_footer );
		wp_enqueue_script( 'edd-checkout-global' );

		wp_localize_script(
			'edd-checkout-global',
			'edd_global_vars',
			apply_filters(
				'edd_global_checkout_script_vars',
				array(
					'ajaxurl'               => edd_get_ajax_url(),
					'checkout_nonce'        => wp_create_nonce( 'edd_checkout_nonce' ),
					'checkout_error_anchor' => '#edd_purchase_submit',
					'currency_sign'         => edd_currency_filter( '' ),
					'currency_pos'          => edd_get_option( 'currency_position', 'before' ),
					'decimal_separator'     => edd_get_option( 'decimal_separator', '.' ),
					'thousands_separator'   => edd_get_option( 'thousands_separator', ',' ),
					'no_gateway'            => __( 'Please select a payment method', 'easy-digital-downloads' ),
					'no_discount'           => __( 'Please enter a discount code', 'easy-digital-downloads' ), // Blank discount code message
					'enter_discount'        => __( 'Enter discount', 'easy-digital-downloads' ),
					'discount_applied'      => __( 'Discount Applied', 'easy-digital-downloads' ), // Discount verified message
					'no_email'              => __( 'Please enter an email address before applying a discount code', 'easy-digital-downloads' ),
					'no_username'           => __( 'Please enter a username before applying a discount code', 'easy-digital-downloads' ),
					'purchase_loading'      => __( 'Please Wait...', 'easy-digital-downloads' ),
					'complete_purchase'     => edd_get_checkout_button_purchase_label(),
					'taxes_enabled'         => edd_use_taxes() ? '1' : '0',
					'edd_version'           => $version,
				)
			)
		);
	}

	// Load AJAX scripts, if enabled
	if ( ! edd_is_ajax_disabled() ) {
		wp_register_script( 'edd-ajax', $js_dir . 'edd-ajax' . $suffix . '.js', array( 'jquery' ), $version, $in_footer );
		wp_enqueue_script( 'edd-ajax' );

		wp_localize_script(
			'edd-ajax',
			'edd_scripts',
			apply_filters(
				'edd_ajax_script_vars',
				array(
					'ajaxurl'                 => edd_get_ajax_url(),
					'position_in_cart'        => isset( $position ) ? $position : -1,
					'has_purchase_links'      => $has_purchase_links,
					'already_in_cart_message' => __( 'You have already added this item to your cart', 'easy-digital-downloads' ), // Item already in the cart message
					'empty_cart_message'      => __( 'Your cart is empty', 'easy-digital-downloads' ), // Item already in the cart message
					'loading'                 => __( 'Loading', 'easy-digital-downloads' ), // General loading message
					'select_option'           => __( 'Please select an option', 'easy-digital-downloads' ), // Variable pricing error with multi-purchase option enabled
					'is_checkout'             => edd_is_checkout() ? '1' : '0',
					'default_gateway'         => edd_get_default_gateway(),
					'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
					'checkout_page'           => edd_get_checkout_uri(),
					'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
					'quantities_enabled'      => edd_item_quantities_enabled(),
					'taxes_enabled'           => edd_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
				)
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
 * @return void
 */
function edd_register_styles() {

	// Bail if styles are disabled
	if ( edd_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$version = edd_admin_get_script_version();

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = edd_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'edd.css';
	$edd_plugin_style_sheet     = trailingslashit( edd_get_templates_dir() ) . $file;

	// Look in the child theme directory first, followed by the parent theme, followed by the EDD core templates directory
	// Also look for the min version first, followed by non minified version, even if SCRIPT_DEBUG is not enabled.
	// This allows users to copy just edd.css to their theme
	if ( file_exists( $child_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $child_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'edd.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if ( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'edd.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $edd_plugin_style_sheet ) || file_exists( $edd_plugin_style_sheet ) ) {
		$url = trailingslashit( edd_get_templates_url() ) . $file;
	}

	wp_register_style( 'edd-styles', $url, array(), $version, 'all' );
	wp_enqueue_style( 'edd-styles' );
}
add_action( 'wp_enqueue_scripts', 'edd_register_styles' );

/**
 * Load head styles
 *
 * Ensures download styling is still shown correctly if a theme is using the CSS template file
 *
 * @since 2.5
 * @global $post
 * @return void
 */
function edd_load_head_styles() {
	global $post;

	// Bail if styles are disabled
	if ( edd_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = is_rtl() ? '-rtl' : '';
	$suffix .= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = edd_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory() ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory() ) . $templates_dir . 'edd.css';

	if ( has_shortcode( $post->post_content, 'downloads' ) &&
		file_exists( $child_theme_style_sheet ) ||
		file_exists( $child_theme_style_sheet_2 ) ||
		file_exists( $parent_theme_style_sheet ) ||
		file_exists( $parent_theme_style_sheet_2 )
	) {
		$has_css_template = apply_filters( 'edd_load_head_styles', true );
	} else {
		$has_css_template = false;
	}

	// Bail if no template
	if ( empty( $has_css_template ) ) {
		return;
	}

	?>
	<style id="edd-head-styles">.edd_download{float:left;}.edd_download_columns_1 .edd_download{width: 100%;}.edd_download_columns_2 .edd_download{width:50%;}.edd_download_columns_0 .edd_download,.edd_download_columns_3 .edd_download{width:33%;}.edd_download_columns_4 .edd_download{width:25%;}.edd_download_columns_5 .edd_download{width:20%;}.edd_download_columns_6 .edd_download{width:16.6%;}</style>
	<?php
}
add_action( 'wp_head', 'edd_load_head_styles' );

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
	return ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
		? current_time( 'timestamp' )
		: EDD_VERSION;
}

/**
 * Register all admin area scripts
 *
 * @since 3.0
 */
function edd_register_admin_scripts() {
	global $hook_suffix;

	$js_dir     = EDD_PLUGIN_URL . 'assets/js/';
	$js_suffix  = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.js' : '.min.js';
	$version    = edd_admin_get_script_version();
	$moment_js  = 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js';
	$admin_deps = ! edd_is_admin_page( $hook_suffix, 'edit' ) && ! edd_is_admin_page( $hook_suffix, 'new' )
		? array( 'jquery', 'jquery-form', 'inline-edit-post' )
		: array( 'jquery', 'jquery-form' );

	// Add chart-js to dependencies if viewing reports page
	if ( edd_is_admin_page( $hook_suffix, 'reports' ) ) {
		$admin_deps[] = 'edd-chart-js';
	}

	// Register scripts
	wp_register_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), $version );
	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $js_suffix, array( 'jquery' ), $version );
	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $js_suffix, array(), $version );
	wp_register_script( 'edd-moment-js', $moment_js, array(), $version );
	wp_register_script( 'edd-chart-js', $js_dir . 'Chart' . $js_suffix, array( 'edd-moment-js' ), $version );
	wp_register_script( 'edd-admin-scripts', $js_dir . 'admin-scripts' . $js_suffix, $admin_deps, $version );
	wp_register_script( 'edd-admin-scripts-compatibility', $js_dir . 'admin-backwards-compatibility' . $js_suffix, array( 'jquery', 'edd-admin-scripts' ), $version );
}
add_action( 'admin_enqueue_scripts', 'edd_register_admin_scripts' );

/**
 * Register all admin area styles
 *
 * @since 3.0
 */
function edd_register_admin_styles() {
	$css_dir     = EDD_PLUGIN_URL . 'assets/css/';
	$css_suffix  = is_rtl() ? '-rtl' : '';
	$css_suffix .= defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.css' : '.min.css';
	$version     = edd_admin_get_script_version();

	// Register styles
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $css_suffix, array(), $version );
	wp_register_style( 'colorbox', $css_dir . 'colorbox' . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin', $css_dir . 'edd-admin' . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin-menu', $css_dir . 'edd-admin-menu' . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin-email-tags', $css_dir . 'edd-admin-email-tags' . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin-datepicker', $css_dir . 'edd-admin-datepicker' . $css_suffix, array( 'edd-admin' ), $version );
}
add_action( 'admin_enqueue_scripts', 'edd_register_admin_styles' );

/**
 * Localize all admin scripts
 *
 * @since 3.0
 */
function edd_localize_admin_scripts() {

	// Admin scripts
	wp_localize_script(
		'edd-admin-scripts',
		'edd_vars',
		array(
			'post_id'                 => get_the_ID(),
			'edd_version'             => edd_admin_get_script_version(),
			'currency'                => edd_get_currency(),
			'currency_sign'           => edd_currency_filter( '' ),
			'currency_pos'            => edd_get_option( 'currency_position', 'before' ),
			'currency_decimals'       => edd_currency_decimal_filter(),
			'decimal_separator'       => edd_get_option( 'decimal_separator', '.' ),
			'thousands_separator'     => edd_get_option( 'thousands_separator', ',' ),
			'date_picker_format'      => edd_get_date_picker_format( 'js' ),
			'add_new_download'        => __( 'Add New Download', 'easy-digital-downloads' ),
			'use_this_file'           => __( 'Use This File', 'easy-digital-downloads' ),
			'quick_edit_warning'      => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
			'delete_payment'          => __( 'Are you sure you want to delete this payment?', 'easy-digital-downloads' ),
			'delete_order_item'       => __( 'Are you sure you want to delete this item?', 'easy-digital-downloads' ),
			'delete_order_adjustment' => __( 'Are you sure you want to delete this adjustment?', 'easy-digital-downloads' ),
			'delete_note'             => __( 'Are you sure you want to delete this note?', 'easy-digital-downloads' ),
			'delete_tax_rate'         => __( 'Are you sure you want to delete this tax rate?', 'easy-digital-downloads' ),
			'revoke_api_key'          => __( 'Are you sure you want to revoke this API key?', 'easy-digital-downloads' ),
			'regenerate_api_key'      => __( 'Are you sure you want to regenerate this API key?', 'easy-digital-downloads' ),
			'resend_receipt'          => __( 'Are you sure you want to resend the purchase receipt?', 'easy-digital-downloads' ),
			'disconnect_customer'     => __( 'Are you sure you want to disconnect the WordPress user from this customer record?', 'easy-digital-downloads' ),
			'copy_download_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
			'delete_payment_download' => sprintf( __( 'Are you sure you want to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'type_to_search'          => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			'one_option'              => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
			'one_or_more_option'      => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
			'one_price_min'           => __( 'You must have at least one price', 'easy-digital-downloads' ),
			'one_field_min'           => __( 'You must have at least one field', 'easy-digital-downloads' ),
			'one_download_min'        => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
			'no_results_text'         => __( 'No match for:', 'easy-digital-downloads' ),
			'numeric_item_price'      => __( 'Item price must be numeric', 'easy-digital-downloads' ),
			'numeric_item_tax'        => __( 'Item tax must be numeric', 'easy-digital-downloads' ),
			'numeric_quantity'        => __( 'Quantity must be numeric', 'easy-digital-downloads' ),
			'remove_text'             => __( 'Remove', 'easy-digital-downloads' ),
			'batch_export_no_class'   => __( 'You must choose a method.', 'easy-digital-downloads' ),
			'batch_export_no_reqs'    => __( 'Required fields not completed.', 'easy-digital-downloads' ),
			'reset_stats_warn'        => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'easy-digital-downloads' ),
			'unsupported_browser'     => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'easy-digital-downloads' ),
			'show_advanced_settings'  => __( 'Show advanced settings', 'easy-digital-downloads' ),
			'hide_advanced_settings'  => __( 'Hide advanced settings', 'easy-digital-downloads' ),

			// Features
			'quantities_enabled'      => edd_item_quantities_enabled(),
			'taxes_enabled'           => edd_use_taxes(),
			'taxes_included'          => edd_use_taxes() && edd_prices_include_tax(),
			'new_media_ui'            => apply_filters( 'edd_use_35_media_ui', 1 ),
		)
	);

	/*
	 * This bit of JavaScript is to facilitate #2704, in order to not break backwards compatibility with the old Variable Price Rows
	 * while we transition to an entire new markup. They should not be relied on for long-term usage.
	 *
	 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2704
	 */
	wp_localize_script(
		'edd-admin-scripts-compatibility',
		'edd_backcompat_vars',
		array(
			'purchase_limit_settings'     => __( 'Purchase Limit Settings', 'easy-digital-downloads' ),
			'simple_shipping_settings'    => __( 'Simple Shipping Settings', 'easy-digital-downloads' ),
			'software_licensing_settings' => __( 'Software Licensing Settings', 'easy-digital-downloads' ),
			'recurring_payments_settings' => __( 'Recurring Payments Settings', 'easy-digital-downloads' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'edd_localize_admin_scripts' );

/**
 * Print admin area scripts
 *
 * @since 3.0
 */
function edd_print_admin_scripts( $hook = '' ) {

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Enqueue media on EDD admin pages
	wp_enqueue_media();

	// Scripts to enqueue
	$scripts = array(
		'jquery-chosen',
		'jquery-form',
		'jquery-ui-datepicker',
		'jquery-ui-dialog',
		'jquery-ui-tooltip',
		'wp-ajax-response',
		'wp-color-picker',
		'edd-admin-scripts',
		'media-upload',
		'colorbox',
		'thickbox',
	);

	// Loop through and enqueue the scripts
	foreach ( $scripts as $script ) {
		wp_enqueue_script( $script );
	}
}
add_action( 'admin_print_scripts', 'edd_print_admin_scripts' );

/**
 * Enqueue admin area styling.
 *
 * Always enqueue the menu styling. Only enqueue others on EDD pages.
 *
 * @since 3.0
 */
function edd_print_admin_styles( $hook = '' ) {

	// Always enqueue the admin menu CSS
	wp_enqueue_style( 'edd-admin-menu' );

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Styles to enqueue
	$styles = array(
		'jquery-chosen',
		'wp-color-picker',
		'colorbox',
		'thickbox',
		'edd-admin',
		'edd-admin-datepicker',
	);

	// Loop through and enqueue the scripts
	foreach ( $styles as $style ) {
		wp_enqueue_style( $style );
	}
}
add_action( 'admin_print_styles', 'edd_print_admin_styles' );

/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @since 1.0
 * @since 2.6.11 Removed globals and CSS for custom icon
 * @return void
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

	// Back compat for hook suffix
	$hook_suffix = empty( $hook )
		? $GLOBALS['hook_suffix']
		: $hook;

	// Filter & return
	return (bool) apply_filters( 'edd_load_admin_scripts', edd_is_admin_page(), $hook_suffix );
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

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Register all scripts and styles
	edd_register_admin_scripts();
	edd_register_admin_styles();

	// Load scripts and styles for back-compat
	edd_print_admin_scripts( $hook );
	edd_print_admin_styles( $hook );
}
