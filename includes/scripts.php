<?php
/**
 * Scripts
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
 * @global $post
 * @return void
 */
function edd_load_scripts() {
	global $post;

	$js_dir = EDD_PLUGIN_URL . 'assets/js/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Get position in cart of current download
	if ( isset( $post->ID ) ) {
		$position = edd_get_item_position_in_cart( $post->ID );
	}

	$has_purchase_links = false;
	if ( ( ! empty( $post->post_content ) && ( has_shortcode( $post->post_content, 'purchase_link' ) || has_shortcode( $post->post_content, 'downloads' ) ) ) || is_post_type_archive( 'download' ) ) {
		$has_purchase_links = true;
	}

	if ( edd_is_checkout() ) {
		if ( edd_is_cc_verify_enabled() ) {
			wp_register_script( 'creditCardValidator', $js_dir . 'jquery.creditCardValidator' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
			wp_enqueue_script( 'creditCardValidator' );
		}

		wp_register_script( 'edd-checkout-global', $js_dir . 'edd-checkout-global' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		wp_enqueue_script( 'edd-checkout-global' );

		wp_localize_script( 'edd-checkout-global', 'edd_global_vars', apply_filters( 'edd_global_checkout_script_vars', array(
			'ajaxurl'            => edd_get_ajax_url(),
			'checkout_nonce'     => wp_create_nonce( 'edd_checkout_nonce' ),
			'currency_sign'      => edd_currency_filter(''),
			'currency_pos'       => edd_get_option( 'currency_position', 'before' ),
			'decimal_separator'  => edd_get_option( 'decimal_separator', '.' ),
			'thousands_separator'=> edd_get_option( 'thousands_separator', ',' ),
			'no_gateway'         => __( 'Please select a payment method', 'easy-digital-downloads' ),
			'no_discount'        => __( 'Please enter a discount code', 'easy-digital-downloads' ), // Blank discount code message
			'enter_discount'     => __( 'Enter discount', 'easy-digital-downloads' ),
			'discount_applied'   => __( 'Discount Applied', 'easy-digital-downloads' ), // Discount verified message
			'no_email'           => __( 'Please enter an email address before applying a discount code', 'easy-digital-downloads' ),
			'no_username'        => __( 'Please enter a username before applying a discount code', 'easy-digital-downloads' ),
			'purchase_loading'   => __( 'Please Wait...', 'easy-digital-downloads' ),
			'complete_purchase'  => edd_get_checkout_button_purchase_label(),
			'taxes_enabled'      => edd_use_taxes() ? '1' : '0',
			'edd_version'        => EDD_VERSION
		) ) );
	}

	// Load AJAX scripts, if enabled
	if ( ! edd_is_ajax_disabled() ) {
		wp_register_script( 'edd-ajax', $js_dir . 'edd-ajax' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
		wp_enqueue_script( 'edd-ajax' );

		wp_localize_script( 'edd-ajax', 'edd_scripts', apply_filters( 'edd_ajax_script_vars', array(
			'ajaxurl'                 => edd_get_ajax_url(),
			'position_in_cart'        => isset( $position ) ? $position : -1,
			'has_purchase_links'      => $has_purchase_links,
			'already_in_cart_message' => __('You have already added this item to your cart','easy-digital-downloads' ), // Item already in the cart message
			'empty_cart_message'      => __('Your cart is empty','easy-digital-downloads' ), // Item already in the cart message
			'loading'                 => __('Loading','easy-digital-downloads' ) , // General loading message
			'select_option'           => __('Please select an option','easy-digital-downloads' ) , // Variable pricing error with multi-purchase option enabled
			'is_checkout'             => edd_is_checkout() ? '1' : '0',
			'default_gateway'         => edd_get_default_gateway(),
			'redirect_to_checkout'    => ( edd_straight_to_checkout() || edd_is_checkout() ) ? '1' : '0',
			'checkout_page'           => edd_get_checkout_uri(),
			'permalinks'              => get_option( 'permalink_structure' ) ? '1' : '0',
			'quantities_enabled'      => edd_item_quantities_enabled(),
			'taxes_enabled'           => edd_use_taxes() ? '1' : '0', // Adding here for widget, but leaving in checkout vars for backcompat
		) ) );
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
	if ( edd_get_option( 'disable_styles', false ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = edd_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'edd.css';
	$edd_plugin_style_sheet     = trailingslashit( edd_get_templates_dir()    ) . $file;

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
	} elseif ( file_exists( $edd_plugin_style_sheet ) || file_exists( $edd_plugin_style_sheet ) ) {
		$url = trailingslashit( edd_get_templates_url() ) . $file;
	}

	wp_register_style( 'edd-styles', $url, array(), EDD_VERSION, 'all' );
	wp_enqueue_style( 'edd-styles' );
}
add_action( 'wp_enqueue_scripts', 'edd_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */
function edd_load_admin_scripts( $hook ) {

	if ( ! apply_filters( 'edd_load_admin_scripts', edd_is_admin_page(), $hook ) ) {
		return;
	}

	global $post;

	$js_dir  = EDD_PLUGIN_URL . 'assets/js/';
	$css_dir = EDD_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// These have to be global
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), EDD_VERSION );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_enqueue_script( 'jquery-form' );

	$admin_deps = array();

	if ( ! edd_is_admin_page( $hook, 'edit' ) && ! edd_is_admin_page( $hook, 'new' ) ) {
		$admin_deps = array( 'jquery', 'jquery-form', 'inline-edit-post' );
	} else {
		$admin_deps = array( 'jquery', 'jquery-form' );
	}

	wp_register_script( 'edd-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, EDD_VERSION, false );

	wp_enqueue_script( 'edd-admin-scripts' );

	wp_localize_script( 'edd-admin-scripts', 'edd_vars', array(
		'post_id'                     => isset( $post->ID ) ? $post->ID : null,
		'edd_version'                 => EDD_VERSION,
		'add_new_download'            => __( 'Add New Download', 'easy-digital-downloads' ),
		'use_this_file'               => __( 'Use This File', 'easy-digital-downloads' ),
		'quick_edit_warning'          => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
		'delete_payment'              => __( 'Are you sure you wish to delete this payment?', 'easy-digital-downloads' ),
		'delete_payment_note'         => __( 'Are you sure you wish to delete this note?', 'easy-digital-downloads' ),
		'delete_tax_rate'             => __( 'Are you sure you wish to delete this tax rate?', 'easy-digital-downloads' ),
		'revoke_api_key'              => __( 'Are you sure you wish to revoke this API key?', 'easy-digital-downloads' ),
		'regenerate_api_key'          => __( 'Are you sure you wish to regenerate this API key?', 'easy-digital-downloads' ),
		'resend_receipt'              => __( 'Are you sure you wish to resend the purchase receipt?', 'easy-digital-downloads' ),
		'copy_download_link_text'     => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
		'delete_payment_download'     => sprintf( __( 'Are you sure you wish to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'one_price_min'               => __( 'You must have at least one price', 'easy-digital-downloads' ),
		'one_field_min'               => __( 'You must have at least one field', 'easy-digital-downloads' ),
		'one_download_min'            => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
		'one_option'                  => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'one_or_more_option'          => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'numeric_item_price'          => __( 'Item price must be numeric', 'easy-digital-downloads' ),
		'numeric_item_tax'            => __( 'Item tax must be numeric', 'easy-digital-downloads' ),
		'numeric_quantity'            => __( 'Quantity must be numeric', 'easy-digital-downloads' ),
		'currency'                    => edd_get_currency(),
		'currency_sign'               => edd_currency_filter( '' ),
		'currency_pos'                => edd_get_option( 'currency_position', 'before' ),
		'currency_decimals'           => edd_currency_decimal_filter(),
		'new_media_ui'                => apply_filters( 'edd_use_35_media_ui', 1 ),
		'remove_text'                 => __( 'Remove', 'easy-digital-downloads' ),
		'type_to_search'              => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'quantities_enabled'          => edd_item_quantities_enabled(),
		'batch_export_no_class'       => __( 'You must choose a method.', 'easy-digital-downloads' ),
		'batch_export_no_reqs'        => __( 'Required fields not completed.', 'easy-digital-downloads' ),
		'reset_stats_warn'            => __( 'Are you sure you want to reset your store? This process is <strong><em>not reversible</em></strong>. Please be sure you have a recent backup.', 'easy-digital-downloads' ),
		'search_placeholder'          => sprintf( __( 'Type to search all %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'search_placeholder_customer' => __( 'Type to search all Customers', 'easy-digital-downloads' ),
		'search_placeholder_country'  => __( 'Type to search all Countries', 'easy-digital-downloads' ),
		'search_placeholder_state'    => __( 'Type to search all States/Provinces', 'easy-digital-downloads' ),
		'unsupported_browser'         => __( 'We are sorry but your browser is not compatible with this kind of file upload. Please upgrade your browser.', 'easy-digital-downloads' ),
	));

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_register_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', array(), '1.3.20' );
	wp_enqueue_style( 'colorbox' );

	wp_register_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
	wp_enqueue_script( 'colorbox' );

	//call for media manager
	wp_enqueue_media();

	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-tooltip' );

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_register_style( 'edd-admin', $css_dir . 'edd-admin' . $suffix . '.css', array(), EDD_VERSION );
	wp_enqueue_style( 'edd-admin' );
}
add_action( 'admin_enqueue_scripts', 'edd_load_admin_scripts', 100 );

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
add_action( 'admin_head','edd_admin_downloads_icon' );


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

	if ( edd_get_option( 'disable_styles', false ) || ! is_object( $post ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$file          = 'edd' . $suffix . '.css';
	$templates_dir = edd_get_theme_template_dir_name();

	$child_theme_style_sheet    = trailingslashit( get_stylesheet_directory() ) . $templates_dir . $file;
	$child_theme_style_sheet_2  = trailingslashit( get_stylesheet_directory() ) . $templates_dir . 'edd.css';
	$parent_theme_style_sheet   = trailingslashit( get_template_directory()   ) . $templates_dir . $file;
	$parent_theme_style_sheet_2 = trailingslashit( get_template_directory()   ) . $templates_dir . 'edd.css';

	$has_css_template = false;

	if ( has_shortcode( $post->post_content, 'downloads' ) &&
		file_exists( $child_theme_style_sheet ) ||
		file_exists( $child_theme_style_sheet_2 ) ||
		file_exists( $parent_theme_style_sheet ) ||
		file_exists( $parent_theme_style_sheet_2 )
	) {
		$has_css_template = apply_filters( 'edd_load_head_styles', true );
	}

	if ( ! $has_css_template ) {
		return;
	}

	?>
	<style>.edd_download{float:left;}.edd_download_columns_1 .edd_download{width: 100%;}.edd_download_columns_2 .edd_download{width:50%;}.edd_download_columns_0 .edd_download,.edd_download_columns_3 .edd_download{width:33%;}.edd_download_columns_4 .edd_download{width:25%;}.edd_download_columns_5 .edd_download{width:20%;}.edd_download_columns_6 .edd_download{width:16.6%;}</style>
	<?php
}
add_action( 'wp_head', 'edd_load_head_styles' );
