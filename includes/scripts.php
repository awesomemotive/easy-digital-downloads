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
			'no_gateway'         => __( 'Please select a payment method', 'easy-digital-downloads' ),
			'no_discount'        => __( 'Please enter a discount code', 'easy-digital-downloads' ), // Blank discount code message
			'enter_discount'     => __( 'Enter discount', 'easy-digital-downloads' ),
			'discount_applied'   => __( 'Discount Applied', 'easy-digital-downloads' ), // Discount verified message
			'no_email'           => __( 'Please enter an email address before applying a discount code', 'easy-digital-downloads' ),
			'no_username'        => __( 'Please enter a username before applying a discount code', 'easy-digital-downloads' ),
			'purchase_loading'   => __( 'Please Wait...', 'easy-digital-downloads' ),
			'complete_purchase'  => __( 'Purchase', 'easy-digital-downloads' ),
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
			'already_in_cart_message' => __('You have already added this item to your cart','easy-digital-downloads' ), // Item already in the cart message
			'empty_cart_message'      => __('Your cart is empty','easy-digital-downloads' ), // Item already in the cart message
			'loading'                 => __('Loading','easy-digital-downloads' ) , // General loading message
			'select_option'           => __('Please select an option','easy-digital-downloads' ) , // Variable pricing error with multi-purchase option enabled
			'ajax_loader'             => set_url_scheme( EDD_PLUGIN_URL . 'assets/images/loading.gif', 'relative' ), // Ajax loading image
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

	if( edd_is_checkout() && is_ssl() ) {
		// Dashicons are used to show the padlock icon on the credit card form
		wp_enqueue_style( 'dashicons' );
	}
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

	global $wp_version, $post;

	$js_dir  = EDD_PLUGIN_URL . 'assets/js/';
	$css_dir = EDD_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// These have to be global
	wp_register_style( 'jquery-chosen', $css_dir . 'chosen' . $suffix . '.css', array(), EDD_VERSION );
	wp_enqueue_style( 'jquery-chosen' );

	wp_register_script( 'jquery-chosen', $js_dir . 'chosen.jquery' . $suffix . '.js', array( 'jquery' ), EDD_VERSION );
	wp_enqueue_script( 'jquery-chosen' );

	wp_register_script( 'edd-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', array( 'jquery', 'inline-edit-post' ), EDD_VERSION, false );
	wp_enqueue_script( 'edd-admin-scripts' );

	wp_localize_script( 'edd-admin-scripts', 'edd_vars', array(
		'post_id'                 => isset( $post->ID ) ? $post->ID : null,
		'edd_version'             => EDD_VERSION,
		'add_new_download'        => __( 'Add New Download', 'easy-digital-downloads' ),
		'use_this_file'           => __( 'Use This File','easy-digital-downloads' ),
		'quick_edit_warning'      => __( 'Sorry, not available for variable priced products.', 'easy-digital-downloads' ),
		'delete_payment'          => __( 'Are you sure you wish to delete this payment?', 'easy-digital-downloads' ),
		'delete_payment_note'     => __( 'Are you sure you wish to delete this note?', 'easy-digital-downloads' ),
		'delete_tax_rate'         => __( 'Are you sure you wish to delete this tax rate?', 'easy-digital-downloads' ),
		'revoke_api_key'          => __( 'Are you sure you wish to revoke this API key?', 'easy-digital-downloads' ),
		'regenerate_api_key'      => __( 'Are you sure you wish to regenerate this API key?', 'easy-digital-downloads' ),
		'resend_receipt'          => __( 'Are you sure you wish to resend the purchase receipt?', 'easy-digital-downloads' ),
		'copy_download_link_text' => __( 'Copy these links to your clipboard and give them to your customer', 'easy-digital-downloads' ),
		'delete_payment_download' => sprintf( __( 'Are you sure you wish to delete this %s?', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'one_price_min'           => __( 'You must have at least one price', 'easy-digital-downloads' ),
		'one_field_min'           => __( 'You must have at least one field', 'easy-digital-downloads' ),
		'one_download_min'        => __( 'Payments must contain at least one item', 'easy-digital-downloads' ),
		'one_option'              => sprintf( __( 'Choose a %s', 'easy-digital-downloads' ), edd_get_label_singular() ),
		'one_or_more_option'      => sprintf( __( 'Choose one or more %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'numeric_item_price'      => __( 'Item price must be numeric', 'easy-digital-downloads' ),
		'numeric_quantity'        => __( 'Quantity must be numeric', 'easy-digital-downloads' ),
		'currency'                => edd_get_currency(),
		'currency_sign'           => edd_currency_filter(''),
		'currency_pos'            => edd_get_option( 'currency_position', 'before' ),
		'currency_decimals'       => edd_currency_decimal_filter(),
		'new_media_ui'            => apply_filters( 'edd_use_35_media_ui', 1 ),
		'remove_text'             => __( 'Remove', 'easy-digital-downloads' ),
		'type_to_search'          => sprintf( __( 'Type to search %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
		'quantities_enabled'      => edd_item_quantities_enabled()
	));

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	wp_register_style( 'colorbox', $css_dir . 'colorbox' . $suffix . '.css', array(), '1.3.20' );
	wp_enqueue_style( 'colorbox' );

	wp_register_script( 'colorbox', $js_dir . 'jquery.colorbox-min.js', array( 'jquery' ), '1.3.20' );
	wp_enqueue_script( 'colorbox' );

	if( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
		//call for new media manager
		wp_enqueue_media();
	}

	wp_register_script( 'jquery-flot', $js_dir . 'jquery.flot' . $suffix . '.js' );
	wp_enqueue_script( 'jquery-flot' );

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-dialog' );

	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_register_style( 'jquery-ui-css', $css_dir . 'jquery-ui-' . $ui_style . $suffix . '.css' );
	wp_enqueue_style( 'jquery-ui-css' );

	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );

	wp_register_style( 'edd-admin', $css_dir . 'edd-admin' . $suffix . '.css', EDD_VERSION );
	wp_enqueue_style( 'edd-admin' );
}
add_action( 'admin_enqueue_scripts', 'edd_load_admin_scripts', 100 );

/**
 * Admin Downloads Icon
 *
 * Echoes the CSS for the downloads post type icon.
 *
 * @since 1.0
 * @global $post_type
 * @global $wp_version
 * @return void
*/
function edd_admin_downloads_icon() {
	global $post_type, $wp_version;

	$images_url      = EDD_PLUGIN_URL . 'assets/images/';
	$menu_icon       = '\f316';
	$icon_url        = $images_url . 'edd-icon.png';
	$icon_cpt_url    = $images_url . 'edd-cpt.png';
	$icon_2x_url     = $images_url . 'edd-icon-2x.png';
	$icon_cpt_2x_url = $images_url . 'edd-cpt-2x.png';
	?>
	<style type="text/css" media="screen">
		<?php if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) { ?>
			#adminmenu #menu-posts-download .wp-menu-image:before,
			#dashboard_right_now .download-count:before {
				content: '<?php echo $menu_icon; ?>';
			}
		<?php } else { ?>
			/** Fallback for outdated WP installations */
			#adminmenu #menu-posts-download div.wp-menu-image {
				background: url(<?php echo $icon_url; ?>) no-repeat 7px -17px;
			}
			#adminmenu #menu-posts-download:hover div.wp-menu-image,
			#adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image {
				background-position: 7px 6px;
			}
		<?php } ?>
		#icon-edit.icon32-posts-download {
			background: url(<?php echo $icon_cpt_url; ?>) -7px -5px no-repeat;
		}
		#edd-media-button {
			background: url(<?php echo $icon_url; ?>) 0 -16px no-repeat;
			background-size: 12px 30px;
		}
		@media
		only screen and (-webkit-min-device-pixel-ratio: 1.5),
		only screen and (   min--moz-device-pixel-ratio: 1.5),
		only screen and (     -o-min-device-pixel-ratio: 3/2),
		only screen and (        min-device-pixel-ratio: 1.5),
		only screen and (        		 min-resolution: 1.5dppx) {
			<?php if( version_compare( $wp_version, '3.7', '<=' ) ) { ?>
				#adminmenu #menu-posts-download div.wp-menu-image {
					background-image: url(<?php echo $icon_2x_url; ?>);
					background-position: 7px -18px;
					background-size: 16px 40px;
				}
				#adminmenu #menu-posts-download:hover div.wp-menu-image,
				#adminmenu #menu-posts-download.wp-has-current-submenu div.wp-menu-image {
					background-position: 7px 6px;
				}
			<?php } ?>
			#icon-edit.icon32-posts-download {
				background: url(<?php echo $icon_cpt_2x_url; ?>) no-repeat -7px -5px !important;
				background-size: 55px 45px !important;
			}
			#edd-media-button {
				background-image: url(<?php echo $icon_2x_url; ?>);
				background-position: 0 -17px;
			}
		}
	</style>
	<?php
}
add_action( 'admin_head','edd_admin_downloads_icon' );
