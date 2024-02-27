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
 * Register all front-end scripts
 *
 * @since 3.0
 */
function edd_register_scripts() {
	$js_dir = EDD_PLUGIN_URL . 'assets/js/';

	// Use minified libraries not debugging scripts
	$version   = edd_admin_get_script_version();
	$in_footer = edd_scripts_in_footer();
	$deps      = array( 'jquery' );

	wp_register_script( 'creditCardValidator', $js_dir . 'vendor/jquery.creditcardvalidator.min.js', $deps, $version, $in_footer );

	// Registered so gateways can enqueue it when they support the space formatting. wp_enqueue_script( 'jQuery.payment' );
	wp_register_script( 'jQuery.payment',      $js_dir . 'vendor/jquery.payment.min.js', $deps, $version, $in_footer );
	wp_register_script( 'edd-checkout-global', $js_dir . 'edd-checkout-global.js',       $deps, $version, $in_footer );
	wp_register_script( 'edd-ajax',            $js_dir . 'edd-ajax.js',                  $deps, $version, $in_footer );
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

	// Bail if styles are disabled
	if ( edd_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries not debugging scripts
	$suffix  = edd_doing_script_debug() ? '' : '.min';
	$version = edd_admin_get_script_version();

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
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . 'edd.css';
		} else {
			$url = trailingslashit( get_stylesheet_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $parent_theme_style_sheet ) || ( ! empty( $suffix ) && ( $nonmin = file_exists( $parent_theme_style_sheet_2 ) ) ) ) {
		if( ! empty( $nonmin ) ) {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . 'edd.css';
		} else {
			$url = trailingslashit( get_template_directory_uri() ) . $templates_dir . $file;
		}
	} elseif ( file_exists( $edd_plugin_style_sheet ) ) {
		$url = trailingslashit( EDD_PLUGIN_URL ) . 'assets/css/edd' . $css_suffix;
	}

	wp_register_style( 'edd-styles', $url, array(), $version, 'all' );
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

	// Checkout scripts
	if ( edd_is_checkout() ) {

		// Enqueue credit-card validator
		if ( edd_is_cc_verify_enabled() ) {
			wp_enqueue_script( 'creditCardValidator' );
		}

		// Enqueue global checkout
		wp_enqueue_script( 'edd-checkout-global' );
	}

	// AJAX scripts, if enabled
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
	global $post;

	$version  = edd_admin_get_script_version();
	$currency = new \EDD\Currency\Currency( edd_get_currency() );

	if ( edd_is_checkout() ) {
		$checkout_variables = apply_filters(
			'edd_global_checkout_script_vars',
			array(
				'ajaxurl'               => esc_url_raw( edd_get_ajax_url() ),
				'checkout_nonce'        => wp_create_nonce( 'edd_checkout_nonce' ),
				'checkout_error_anchor' => '#edd_purchase_submit',
				'currency_sign'         => $currency->symbol,
				'currency_pos'          => $currency->position,
				'decimal_separator'     => $currency->decimal_separator,
				'thousands_separator'   => $currency->thousands_separator,
				'no_gateway'            => __( 'Please select a payment method', 'easy-digital-downloads' ),
				'no_discount'           => __( 'Please enter a discount code', 'easy-digital-downloads' ), // Blank discount code message.
				'enter_discount'        => __( 'Enter discount', 'easy-digital-downloads' ),
				'discount_applied'      => __( 'Discount Applied', 'easy-digital-downloads' ), // Discount verified message.
				'no_email'              => __( 'Please enter an email address before applying a discount code', 'easy-digital-downloads' ),
				'no_username'           => __( 'Please enter a username before applying a discount code', 'easy-digital-downloads' ),
				'purchase_loading'      => __( 'Please Wait...', 'easy-digital-downloads' ),
				'complete_purchase'     => edd_get_checkout_button_purchase_label(),
				'taxes_enabled'         => edd_use_taxes() ? '1' : '0',
				'edd_version'           => $version,
				'current_page'          => get_the_ID(),
				'showStoreErrors'       => current_user_can( 'manage_shop_settings' ) ? 'true' : 'false',
			)
		);
		wp_localize_script( 'edd-checkout-global', 'edd_global_vars', $checkout_variables );
	}

	// Load AJAX scripts, if enabled.
	if ( ! edd_is_ajax_disabled() ) {

		// Get position in cart of current download
		$position = isset( $post->ID )
			? edd_get_item_position_in_cart( $post->ID )
			: -1;

		if ( ( ! empty( $post->post_content ) && ( has_shortcode( $post->post_content, 'purchase_link' ) || has_shortcode( $post->post_content, 'downloads' ) ) ) || is_post_type_archive( 'download' ) ) {
			$has_purchase_links = true;
		} else {
			$has_purchase_links = false;
		}

		wp_localize_script( 'edd-ajax', 'edd_scripts', apply_filters( 'edd_ajax_script_vars', array(
			'ajaxurl'                 => esc_url_raw( edd_get_ajax_url() ),
			'position_in_cart'        => $position,
			'has_purchase_links'      => $has_purchase_links,
			'already_in_cart_message' => __('You have already added this item to your cart','easy-digital-downloads' ), // Item already in the cart message
			'empty_cart_message'      => __('Your cart is empty','easy-digital-downloads' ), // Item already in the cart message
			'loading'                 => __('Loading','easy-digital-downloads' ) , // General loading message
			'select_option'           => __('Please select an option','easy-digital-downloads' ) , // Variable pricing error with multi-purchase option enabled
			'is_checkout'             => edd_is_checkout() ? '1' : '0',
			'default_gateway'         => edd_get_default_gateway(),
			'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
			'checkout_page'           => esc_url_raw( edd_get_checkout_uri() ),
			'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
			'quantities_enabled'      => edd_item_quantities_enabled(),
			'taxes_enabled'           => edd_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
			'current_page'            => get_the_ID(),
		) ) );
	}
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
	global $post;

	// Bail if styles are disabled
	if ( edd_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
		return;
	}

	// Use minified libraries not debugging scripts
	$suffix  = is_rtl() ? '-rtl' : '';
	$suffix .= edd_doing_script_debug() ? '' : '.min';

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = edd_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'edd.css';

	if ( has_shortcode( $post->post_content, 'downloads' ) &&
		file_exists( $child_theme_style_sheet    ) ||
		file_exists( $child_theme_style_sheet_2  ) ||
		file_exists( $parent_theme_style_sheet   ) ||
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
	$js_dir     = EDD_PLUGIN_URL . 'assets/js/';
	$version    = edd_admin_get_script_version();
	$admin_deps = array( 'jquery', 'jquery-form', 'underscore' );

	// Register scripts.
	wp_register_script( 'jquery-chosen',                   $js_dir . 'vendor/chosen.jquery.min.js',          array( 'jquery' ), $version );
	wp_register_script( 'edd-jquery-flot',                 $js_dir . 'vendor/jquery.flot.min.js',            array( 'jquery' ), $version );
	wp_register_script( 'edd-moment-js',                   $js_dir . 'vendor/moment.min.js',                 array(), $version );
	wp_register_script( 'edd-moment-timezone-js',          $js_dir . 'vendor/moment-timezone.min.js',        array( 'edd-moment-js' ), $version );
	wp_register_script( 'edd-chart-js',                    $js_dir . 'vendor/chartjs.min.js',                array( 'edd-moment-js', 'edd-moment-timezone-js' ), $version );
	wp_register_script( 'edd-admin-scripts',               $js_dir . 'edd-admin.js',                         $admin_deps, $version );
	wp_register_script( 'edd-admin-tax-rates',             $js_dir . 'edd-admin-tax-rates.js',               array( 'wp-backbone', 'jquery-chosen' ), $version, true );
	wp_register_script( 'edd-admin-email-tags',            $js_dir . 'edd-admin-email-tags.js',              array( 'thickbox', 'wp-util' ), $version );
	wp_register_script( 'edd-admin-downloads-editor',      $js_dir . 'edd-admin-downloads-editor.js',        array( 'wp-dom-ready', 'wp-api-fetch', 'wp-data' ), $version );

	// Individual admin pages.
	$admin_pages = array(
		'customers'    => array(
			'edd-admin-tools-export'
		),
		'dashboard'    => array(),
		'discounts'    => array(),
		'downloads'    => array(),
		'tools-export' => array(),
		'tools-import' => array(),
		'notes'        => array(),
		'onboarding'   => array(),
		'orders'       => array(
			'edd-admin-notes',
			'wp-util',
			'wp-backbone',
		),
		// Backwards compatibility.
		'payments'     => array(),
		'reports'      => array(
			'edd-chart-js',
		),
		'settings'     => array(),
		'tools'        => array(
			'edd-admin-tools-export'
		),
		'upgrades'     => array(),
	);

	foreach ( $admin_pages as $page => $deps ) {
		wp_register_script(
			'edd-admin-' . $page,
			$js_dir . 'edd-admin-' . $page . '.js',
			array_merge( $admin_deps, $deps ),
			$version
		);
	}
}
add_action( 'admin_init', 'edd_register_admin_scripts' );

/**
 * Register all admin area styles
 *
 * @since 3.0
 */
function edd_register_admin_styles() {
	$css_dir     = EDD_PLUGIN_URL . 'assets/css/';
	$css_suffix  = is_rtl() ? '-rtl.min.css' : '.min.css';
	$version     = edd_admin_get_script_version();
	$deps        = array( 'edd-admin' );

	// Register styles
	wp_register_style( 'jquery-chosen',         $css_dir . 'chosen'               . $css_suffix, array(), $version );
	wp_register_style( 'jquery-ui-css',         $css_dir . 'jquery-ui-fresh'      . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin',             $css_dir . 'edd-admin'            . $css_suffix, array( 'forms' ), $version );
	wp_register_style( 'edd-admin-menu',        $css_dir . 'edd-admin-menu'       . $css_suffix, array(), $version );
	wp_register_style( 'edd-admin-chosen',      $css_dir . 'edd-admin-chosen'     . $css_suffix, $deps,   $version );
	wp_register_style( 'edd-admin-email-tags',  $css_dir . 'edd-admin-email-tags' . $css_suffix, $deps,   $version );
	wp_register_style( 'edd-admin-datepicker',  $css_dir . 'edd-admin-datepicker' . $css_suffix, $deps,   $version );
	wp_register_style( 'edd-admin-tax-rates',   $css_dir . 'edd-admin-tax-rates'  . $css_suffix, $deps,   $version );
	wp_register_style( 'edd-admin-onboarding',  $css_dir . 'edd-admin-onboarding' . $css_suffix, $deps,   $version );
}
add_action( 'admin_init', 'edd_register_admin_styles' );

/**
 * Print admin area scripts
 *
 * @since 3.0
 */
function edd_enqueue_admin_scripts( $hook = '' ) {

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	/**
	 * Prevent the CM Admin Tools JS from loading on our settings pages, as they
	 * are including options and actions that can permemtnly harm a store's data.
	 */
	wp_deregister_script( 'cmadm-utils' );
	wp_deregister_script( 'cmadm-backend' );

	// Enqueue media on EDD admin pages
	wp_enqueue_media();

	// Scripts to enqueue
	$scripts = array(
		'edd-admin-scripts',
		'jquery-chosen',
		'jquery-form',
		'jquery-ui-datepicker',
		'jquery-ui-dialog',
		'jquery-ui-tooltip',
		'media-upload',
		'thickbox',
		'wp-ajax-response',
		'wp-color-picker',
	);

	// Loop through and enqueue the scripts
	foreach ( $scripts as $script ) {
		wp_enqueue_script( $script );
	}

	// Downloads page.
	if ( edd_is_admin_page( 'download' ) ) {
		wp_enqueue_script( 'edd-admin-downloads' );
	}

	if ( ( edd_is_admin_page( 'download', 'edit' ) || edd_is_admin_page( 'download', 'new' ) ) && get_current_screen()->is_block_editor() ) {
		wp_enqueue_script( 'edd-admin-downloads-editor' );
	}

	// Upgrades Page
	if ( in_array( $hook, array( 'edd-admin-upgrades', 'download_page_edd-tools' ) ) ) {
		wp_enqueue_script( 'edd-admin-tools-export' );
		wp_enqueue_script( 'edd-admin-upgrades' );
	}

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

	// Always enqueue the admin menu CSS
	wp_enqueue_style( 'edd-admin-menu' );

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Styles to enqueue (in priority order)
	$styles = array(
		'jquery-chosen',
		'thickbox',
		'wp-jquery-ui-dialog',
		'wp-color-picker',
		'edd-admin',
		'edd-admin-chosen',
		'edd-admin-datepicker'
	);

	// Loop through and enqueue the scripts
	foreach ( $styles as $style ) {
		wp_enqueue_style( $style );
	}
}
add_action( 'admin_enqueue_scripts', 'edd_enqueue_admin_styles' );

/**
 * Localize all admin scripts
 *
 * @since 3.0
 */
function edd_localize_admin_scripts() {
	$currency = edd_get_currency();

	// Customize the currency on a few individual pages.
	if ( function_exists( 'edd_is_admin_page' ) ) {
		if ( edd_is_admin_page( 'reports' ) && function_exists( '\EDD\Reports\get_filter_value' ) ) {
			/*
			 * For reports, use the currency currently being filtered.
			 */
			$currency_filter = \EDD\Reports\get_filter_value( 'currencies' );
			if ( ! empty( $currency_filter ) && array_key_exists( strtoupper( $currency_filter ), edd_get_currencies() ) ) {
				$currency = strtoupper( $currency_filter );
			}
		} elseif ( edd_is_admin_page( 'payments' ) && ! empty( $_GET['id'] ) ) {
			/*
			 * For orders & refunds, use the currency of the current order.
			 */
			$order = edd_get_order( absint( $_GET['id'] ) );
			if ( $order instanceof \EDD\Orders\Order ) {
				$currency = $order->currency;
			}
		}
	}

	$edd_currency = new EDD\Currency\Currency( $currency );
	$edd_vars     = array(
		'post_id'                 => get_the_ID(),
		'edd_version'             => edd_admin_get_script_version(),
		'currency'                => $edd_currency->code,
		'currency_sign'           => $edd_currency->symbol,
		'currency_pos'            => $edd_currency->position,
		'currency_decimals'       => $edd_currency->number_decimals,
		'decimal_separator'       => $edd_currency->decimal_separator,
		'thousands_separator'     => $edd_currency->thousands_separator,
		'date_picker_format'      => edd_get_date_picker_format( 'js' ),
		'add_new_download'        => __( 'Add New Download', 'easy-digital-downloads' ),
		'use_this_file'           => __( 'Use This File', 'easy-digital-downloads' ),
		'quick_edit_warning'      => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
		'delete_order_item'       => __( 'Are you sure you want to delete this item?', 'easy-digital-downloads' ),
		'delete_order_adjustment' => __( 'Are you sure you want to delete this adjustment?', 'easy-digital-downloads' ),
		'delete_note'             => __( 'Are you sure you want to delete this note?', 'easy-digital-downloads' ),
		'delete_tax_rate'         => __( 'Are you sure you want to delete this tax rate?', 'easy-digital-downloads' ),
		'revoke_api_key'          => __( 'Are you sure you want to revoke this API key?', 'easy-digital-downloads' ),
		'regenerate_api_key'      => __( 'Are you sure you want to regenerate this API key?', 'easy-digital-downloads' ),
		'resend_receipt'          => __( 'Are you sure you want to resend the purchase receipt?', 'easy-digital-downloads' ),
		'disconnect_customer'     => __( 'Are you sure you want to disconnect the WordPress user from this customer record?', 'easy-digital-downloads' ),
		'copy_download_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
		/* translators: singular download label */
		'delete_payment_download' => sprintf( __( 'Are you sure you want to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
		/* translators: plural download label */
		'type_to_search'          => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		/* translators: singular download label */
		'one_option'              => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
		/* translators: plural download label */
		'one_or_more_option'      => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'one_price_min'           => __( 'You must have at least one price', 'easy-digital-downloads' ),
		'one_field_min'           => __( 'You must have at least one field', 'easy-digital-downloads' ),
		'one_download_min'        => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
		'no_results_text'         => __( 'No match for:', 'easy-digital-downloads'),
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
		'no_downloads_error'      => __( 'There are no downloads attached to this payment', 'easy-digital-downloads' ),
		'wait'                    => __( 'Please wait &hellip;', 'easy-digital-downloads' ),
		'test_email_save_changes' => __( 'You must save your changes to send the test email.', 'easy-digital-downloads' ),
		'no_letters_or_numbers'   => __( 'Either Letters or Numbers should be selected.', 'easy-digital-downloads' ),

		// Diaglog buttons.
		'confirm_dialog_text'     => __( 'Confirm', 'easy-digital-downloads' ),
		'cancel_dialog_text'      => __( 'Cancel', 'easy-digital-downloads' ),

		// Features.
		'quantities_enabled'      => edd_item_quantities_enabled(),
		'taxes_enabled'           => edd_use_taxes(),
		'taxes_included'          => edd_use_taxes() && edd_prices_include_tax(),
		'new_media_ui'            => edd_apply_filters_deprecated( 'edd_use_35_media_ui', array( 1 ), '3.1.1', false, __( 'The edd_use_35_media_ui filter is no longer supported.', 'easy-digital-downloads' ) ),

		// REST based items.
		'restBase'                => rest_url( \EDD\API\v3\Endpoint::$namespace ),
		'restNonce'               => wp_create_nonce( 'wp_rest' ),
		'download_has_files'      => false,
	);

	if ( function_exists( 'edd_is_admin_page' ) && edd_is_admin_page( 'download', 'edit' ) ) {
		$edd_vars['download_has_files'] = (bool) edd_get_download_files( get_the_ID() );
	}

	// Admin scripts
	wp_localize_script( 'edd-admin-scripts', 'edd_vars', $edd_vars );

	wp_localize_script( 'edd-admin-upgrades', 'edd_admin_upgrade_vars', array(
			'migration_complete' => esc_html__( 'Migration complete', 'easy-digital-downloads' )
	) );
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
add_action( 'admin_head','edd_admin_downloads_icon' );

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

	// Bail if not an EDD admin page
	if ( ! edd_should_load_admin_scripts( $hook ) ) {
		return;
	}

	// Register all scripts and styles
	edd_register_admin_scripts();
	edd_register_admin_styles();

	// Load scripts and styles for back-compat
	edd_enqueue_admin_scripts( $hook );
	edd_enqueue_admin_styles( $hook );
}
