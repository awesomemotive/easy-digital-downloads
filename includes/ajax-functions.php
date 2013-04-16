<?php
/**
 * AJAX Functions
 *
 * Process the front-end AJAX actions.
 *
 * @package     EDD
 * @subpackage  Functions/AJAX
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Checks whether AJAX is enabled.
 *
 * @since 1.0
 * @return bool
 */
function edd_is_ajax_enabled() {
	global $edd_options;
	if ( ! isset( $edd_options['disable_ajax_cart'] ) ) {
		return true;
	}
	return false;
}


/**
 * Get AJAX URL
 *
 * @since 1.3
 * @return string
*/
function edd_get_ajax_url() {
	$scheme = defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'https' : 'admin';

	$current_url = edd_get_current_page_url();
	$ajax_url    = admin_url( 'admin-ajax.php', $scheme );

	if ( preg_match( '/^https/', $current_url ) && ! preg_match( '/^https/', $ajax_url ) ) {
		$ajax_url = preg_replace( '/^http/', 'https', $ajax_url );
	}

	return apply_filters( 'edd_ajax_url', $ajax_url );
}

/**
 * Removes item from cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_remove_from_cart() {
	if ( isset( $_POST['cart_item'] ) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' ) ) {
		edd_remove_from_cart( $_POST['cart_item'] );
		echo 'removed';
	}
	die();
}
add_action( 'wp_ajax_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_edd_remove_from_cart', 'edd_ajax_remove_from_cart' );

/**
 * Adds item to the cart via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_add_to_cart() {
	if ( isset( $_POST['download_id'] ) && check_ajax_referer( 'edd_ajax_nonce', 'nonce' ) ) {
		global $post;

		$to_add = array();

		if ( isset( $_POST['price_ids'] ) && is_array( $_POST['price_ids'] ) ) {
			foreach ( $_POST['price_ids'] as $price ) {
				$to_add[] = array( 'price_id' => $price );
			}
		}

		foreach ( $to_add as $options ) {
			if ( ! edd_item_in_cart( $_POST['download_id'], $options ) ) {
				$key          = edd_add_to_cart( $_POST['download_id'], $options );

				$item         = array(
					'id'      => $_POST['download_id'],
					'options' => $options
				);

				$item = apply_filters( 'edd_ajax_pre_cart_item_template', $item );

				$cart_item    = edd_get_cart_item_template( $key, $item, true );

				echo $cart_item;
			} else {
				echo 'incart';
			}
		}
	}
	die();
}
add_action( 'wp_ajax_edd_add_to_cart', 'edd_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_edd_add_to_cart', 'edd_ajax_add_to_cart' );

/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_apply_discount() {
	if ( isset( $_POST['code'] ) && check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) ) {
		$user = isset( $_POST['user'] ) ? $_POST['user'] : $_POST['email'];

		$return = array(
			'msg'  => '',
			'code' => $_POST['code']
		);

		if ( edd_is_discount_used( $_POST['code'], $user ) ) {  // Called twice if discount is not used (again by edd_is_discount_valid) but allows for beter usr msg and less execution if discount is used.
			$return['msg']  = __('This discount code has been used already', 'edd');
		} else {
			if ( edd_is_discount_valid( $_POST['code'], $user ) ) {
				$discount  = edd_get_discount_by_code( $_POST['code'] );
				$amount    = edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) );
				$discounts = edd_set_cart_discount( $_POST['code'] );
				$total     = edd_get_cart_total( $discounts );

				$return = array(
					'msg'    => 'valid',
					'amount' => $amount,
					'total'  => html_entity_decode( edd_currency_filter( edd_format_amount( $total ) ), ENT_COMPAT, 'UTF-8' ),
					'code'   => $_POST['code'],
					'html'   => edd_get_cart_discounts_html( $discounts )
				);
			} else {
				$return['msg']  = __('The discount you entered is invalid', 'edd');
			}
		}
		echo json_encode($return);
	}
	die();
}
add_action( 'wp_ajax_edd_apply_discount', 'edd_ajax_apply_discount' );
add_action( 'wp_ajax_nopriv_edd_apply_discount', 'edd_ajax_apply_discount' );

/**
 * Loads Checkout Login Fields the via AJAX
 *
 * @since 1.0
 * @return void
 */
function edd_load_checkout_login_fields() {
	do_action( 'edd_purchase_form_login_fields' );
	die();
}
add_action('wp_ajax_nopriv_checkout_login', 'edd_load_checkout_login_fields');

/**
 * Load Checkout Register Fields via AJAX
 *
 * @since 1.0
 * @return void
*/
function edd_load_checkout_register_fields() {
	do_action( 'edd_purchase_form_register_fields' );
	die();
}
add_action('wp_ajax_nopriv_checkout_register', 'edd_load_checkout_register_fields');

/**
 * Get Download Title via AJAX (used only in WordPress Admin)
 *
 * @since 1.0
 * @return void
 */
function edd_ajax_get_download_title() {
	if ( isset( $_POST['download_id'] ) ) {
		$title = get_the_title( $_POST['download_id'] );
		if ( $title ) {
			echo $title;
		} else {
			echo 'fail';
		}
	}
	die();
}
add_action( 'wp_ajax_edd_get_download_title', 'edd_ajax_get_download_title' );
add_action( 'wp_ajax_nopriv_edd_get_download_title', 'edd_ajax_get_download_title' );

/**
 * Opt into local taxes via AJAX
 *
 * @since 1.4.1
 * @return void
 */
function edd_ajax_opt_into_local_taxes() {
	if ( ! check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) )
		return false;

	edd_opt_into_local_taxes();

	ob_start();
	edd_checkout_cart();
	$cart = ob_get_contents();
	ob_end_clean();

	$response = array(
		'html'  => $cart,
		'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
	);

	echo json_encode( $response );

	exit;
}
add_action( 'wp_ajax_edd_local_tax_opt_in', 'edd_ajax_opt_into_local_taxes' );
add_action( 'wp_ajax_nopriv_edd_local_tax_opt_in', 'edd_ajax_opt_into_local_taxes' );

/**
 * Opt out of local taxes via AJAX
 *
 * @since 1.4.1
 * @return void
 */
function edd_ajax_opt_out_local_taxes() {
	if ( ! check_ajax_referer( 'edd_checkout_nonce', 'nonce' ) )
		return false;

	edd_opt_out_local_taxes();

	ob_start();
	edd_checkout_cart();
	$cart = ob_get_contents();
	ob_end_clean();

	$response = array(
		'html'  => $cart,
		'total' => html_entity_decode( edd_cart_total( false ), ENT_COMPAT, 'UTF-8' ),
	);

	echo json_encode( $response );

	exit;
}
add_action( 'wp_ajax_edd_local_tax_opt_out', 'edd_ajax_opt_out_local_taxes' );
add_action( 'wp_ajax_nopriv_edd_local_tax_opt_out', 'edd_ajax_opt_out_local_taxes' );

/**
 * Check for Download Price Variations via AJAX (this function can only be used
 * in WordPress Admin). This function isused for the Edit Payment screen when downloads
 * are added to the purchase. When each download is chosen, an AJAX call is fired
 * to this function which will check if variable prices exist for that download.
 * If they do, it will output a dropdown of all the variable prices available for
 * that download.
 *
 * @author Sunny Ratilal
 * @since 1.5
 * @return void
 */
function edd_check_for_download_price_variations() {
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'edd_add_downloads_to_purchase_nonce' ) ) {

		$download_id = intval( $_POST['download_id'] );

		if ( edd_has_variable_prices( $download_id ) ) {
			$variable_prices = get_post_meta( $download_id, 'edd_variable_prices', true );

			if ( $variable_prices ) {
				$ajax_response = '<select name="downloads[' . intval( $_POST['array_key'] ) . '][options][price_id]" class="edd-variable-prices-select">';
					foreach ( $variable_prices as $key => $price ) {
						$ajax_response .= '<option value="' . $key . '">' . $price['name']  . '</option>';
					}
				$ajax_response .= '</select>';
			}

			echo $ajax_response;
		}

		die();
	}
}
add_action( 'wp_ajax_edd_check_for_download_price_variations', 'edd_check_for_download_price_variations' );